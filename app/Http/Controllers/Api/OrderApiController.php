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
}
