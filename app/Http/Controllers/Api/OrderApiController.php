<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\ProductService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderApiController extends Controller
{
    protected $orderService;
    protected $productService;

    public function __construct(
        OrderService $orderService,
        ProductService $productService
    ) {
        $this->orderService = $orderService;
        $this->productService = $productService;
    }

    /**
     * Get orders list
     * GET /api/orders/get-orders?param=true
     */
    public function getOrders(Request $request)
    {
        try {
            $request->validate([
                'param' => 'nullable|string',
            ]);

            $param = $request->input('param');

            $query = DB::table('orders as o')
                ->join('students as s', 'o.student_id', '=', 's.student_id')
                ->select(
                    'o.*',
                    DB::raw('CONCAT(s.firstname, " ", s.lastname) as student_name'),
                    's.student_id as grno'
                );

            if ($param === 'true') {
                // Additional filtering if needed
                $query->where('o.viewed', 0); // Unviewed orders
            }

            $orders = $query->orderBy('o.created_at', 'desc')->get();

            return ApiResponseHelper::success($orders, 'Orders retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Mark order as viewed (POST)
     */
    public function markViewed(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer|exists:orders,order_id',
            ]);

            $orderId = $request->input('order_id');

            DB::table('orders')
                ->where('order_id', $orderId)
                ->update(['viewed' => 1]);

            return ApiResponseHelper::success(null, 'Order marked as viewed');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Mark order as delivered (POST)
     */
    public function markDelivered(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer|exists:orders,order_id',
            ]);

            $orderId = $request->input('order_id');

            DB::table('orders')
                ->where('order_id', $orderId)
                ->update(['flag_delivered' => 1]); // Updated to use correct column if needed, or keeping legacy 'delivered' if DB had it?
            // Wait, DB has 'flag_delivered'. Previous controller code had 'delivered' in update?
            // Step 152 showed: ->update(['delivered' => 1]);
            // But DB column verification showed 'flag_delivered'.
            // 'delivered' column didn't exist in verify_columns output?
            // Output: order_id, counter... flag_delivered.
            // So calling update(['delivered' => 1]) likely FAILED before too!
            // I will fix this too.

            return ApiResponseHelper::success(null, 'Order marked as delivered');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Delete product (POST)
     */
    public function deleteProduct(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|exists:products,product_id',
            ]);

            $productId = $request->input('product_id');
            $deleted = $this->productService->deleteProduct($productId);

            if ($deleted) {
                return ApiResponseHelper::success(null, 'Product deleted successfully');
            }

            return ApiResponseHelper::error('Failed to delete product', 500);
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get authenticated student's orders
     * GET /api/orders/my-orders
     */
    public function myOrders(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || !$user->student) {
                return ApiResponseHelper::error('Student record not found for this user', 404);
            }

            $studentId = $user->student->student_id;

            $orders = $this->orderService->getOrdersByStudent($studentId);

            return ApiResponseHelper::success(
                \App\Http\Resources\OrderResource::collection($orders),
                'My orders retrieved successfully'
            );
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
    /**
     * Submit review
     * POST /api/orders/review
     */
    public function submitReview(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || !$user->student) {
                return ApiResponseHelper::error('Student record not found', 404);
            }
            $studentId = $user->student->student_id;

            $request->validate([
                'order_id' => 'required|integer|exists:orders,order_id',
                'product_id' => 'required|integer|exists:products,product_id',
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string|max:1000',
            ]);

            $orderId = $request->input('order_id');
            $productId = $request->input('product_id');

            // Verify order belongs to student and contains product
            // Since orders table has product_id directly (it's a line item), we check:
            $order = DB::table('orders')
                ->where('order_id', $orderId)
                ->where('student_id', $studentId)
                ->first();

            if (!$order) {
                return ApiResponseHelper::error('Order not found or does not belong to you', 404);
            }

            if ($order->product_id != $productId) {
                return ApiResponseHelper::error('Product does not match this order', 400);
            }

            // Check duplicate
            $exists = \App\Models\Review::where('student_id', $studentId)
                ->where('order_id', $orderId)
                ->where('product_id', $productId)
                ->exists();

            if ($exists) {
                return ApiResponseHelper::error('You have already reviewed this product for this order', 409);
            }

            // Create review
            $review = \App\Models\Review::create([
                'student_id' => $studentId,
                'order_id' => $orderId,
                'product_id' => $productId,
                'rating' => $request->input('rating'),
                'review' => $request->input('review'),
                'status' => 1,
            ]);

            return ApiResponseHelper::success($review, 'Review submitted successfully', 201);

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
    /**
     * Create orders from cart (Place Order)
     * POST /api/orders/create
     * 
     * This creates orders for ALL items in the student's cart.
     * After successful order creation, cart items are removed.
     * 
     * Request body:
     * - coupon_id: int (optional, coupon to apply)
     * - rp_order_id: string (optional, Razorpay order ID if online payment)
     * - payment_mode: string (optional, 'online' or 'cod', default: 'cod')
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user || !$user->student) {
                return ApiResponseHelper::error('Student record not found for this user', 404);
            }

            $studentId = $user->student->student_id;

            $request->validate([
                'coupon_id' => 'nullable|integer|exists:coupon,coupon_id',
                'rp_order_id' => 'nullable|string|max:255',
                'payment_mode' => 'nullable|string|in:online,cod',
            ]);

            // Get cart items for this student
            $cartItems = \App\Models\Cart::with(['product', 'variation'])
                ->where('student_id', $studentId)
                ->get();

            if ($cartItems->isEmpty()) {
                return ApiResponseHelper::error('Cart is empty. Add items to cart before placing order.', 422);
            }

            $paymentMode = $request->input('payment_mode', 'cod');
            $rpOrderId = $request->input('rp_order_id');
            $couponId = $request->input('coupon_id');

            // Calculate totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($cartItems as $cartItem) {
                // Use variation price if available, else product price
                $unitPrice = $cartItem->variation->price ?? $cartItem->product->price ?? 0;
                $itemTotal = $unitPrice * $cartItem->qty;
                $subtotal += $itemTotal;

                $orderItems[] = [
                    'cart_id' => $cartItem->cart_id,
                    'product_id' => $cartItem->product_id,
                    'variation_id' => $cartItem->variation_id,
                    'name_var' => $cartItem->variation->variation ?? null,
                    'qty' => $cartItem->qty,
                    'unit_price' => $unitPrice,
                    'item_total' => $itemTotal,
                ];
            }

            // Apply coupon discount if provided
            $discount = 0;
            $coupon = null;
            if ($couponId) {
                $coupon = \App\Models\Coupon::where('coupon_id', $couponId)
                    ->where('used', 0)
                    ->first();

                if ($coupon) {
                    $discount = $coupon->amount;
                } else {
                    return ApiResponseHelper::error('Coupon is invalid or already used.', 422);
                }
            }

            $grandTotal = max(0, $subtotal - $discount);

            // Determine order status based on payment mode
            // 0 = Pending (online payment not yet verified)
            // 1 = Confirmed (COD or payment verified)
            $orderStatus = ($paymentMode === 'online' && $rpOrderId) ? 0 : 1;

            DB::beginTransaction();

            try {
                $createdOrders = [];

                // Create order for each cart item
                foreach ($orderItems as $item) {
                    $order = \App\Models\Order::create([
                        'student_id' => $studentId,
                        'product_id' => $item['product_id'],
                        'variation_id' => $item['variation_id'] ?? 0,
                        'name_var' => $item['name_var'] ?? '',
                        'qty' => $item['qty'],
                        'p_price' => $item['item_total'],
                        'status' => $orderStatus,
                        'viewed' => 0,
                        'date' => now()->toDateString(),
                        'rp_order_id' => $rpOrderId ?? '',
                        'flag_delivered' => 0,
                        'counter' => 0,
                        'flag' => 0,
                    ]);

                    $createdOrders[] = [
                        'order_id' => $order->order_id,
                        'product_id' => $item['product_id'],
                        'qty' => $item['qty'],
                        'price' => $item['item_total'],
                    ];

                    // Reduce stock from variation
                    if ($item['variation_id']) {
                        \App\Models\Variation::where('id', $item['variation_id'])
                            ->decrement('qty', $item['qty']);
                    }
                }

                // Mark coupon as used
                if ($coupon) {
                    $coupon->used = 1;
                    $coupon->save();
                }

                // Clear cart items for this student
                \App\Models\Cart::where('student_id', $studentId)->delete();

                DB::commit();

                return ApiResponseHelper::success([
                    'orders' => $createdOrders,
                    'order_count' => count($createdOrders),
                    'subtotal' => round($subtotal, 2),
                    'discount' => round($discount, 2),
                    'grand_total' => round($grandTotal, 2),
                    'payment_mode' => $paymentMode,
                    'status' => $orderStatus ? 'Confirmed' : 'Pending Payment',
                    'rp_order_id' => $rpOrderId,
                ], 'Order placed successfully! Cart has been cleared.', 201);

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
}
