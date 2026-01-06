<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Variation;
use App\Models\Student;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class CartApiController extends Controller
{
    /**
     * Get Cart Items (Auth based)
     * GET /api/cart
     */
    public function index(Request $request)
    {
        try {
            // Get logged in user
            $user = $request->user();

            if (!$user) {
                return ApiResponseHelper::error('Unauthorized', 401);
            }

            // Find student by email
            $student = Student::where('email', $user->email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Student profile not found for this user', 404);
            }

            $studentId = $student->student_id;

            $cartItems = Cart::with(['product', 'variation'])
                ->where('student_id', $studentId)
                ->get();

            $formattedItems = $cartItems->map(function ($item) {
                // Handle image URL
                $baseUrl = config('app.url') . '/images/products/';
                $image = $item->product && $item->product->image1
                    ? $baseUrl . $item->product->image1
                    : $baseUrl . 'placeholder.png';

                return [
                    'cart_id' => $item->cart_id,
                    'qty' => $item->qty,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? null,
                    'product_details' => $item->product->details ?? null,
                    'variation_id' => $item->variation_id,
                    'variation_name' => $item->variation->variation ?? null,
                    'price' => $item->variation->price ?? 0,
                    'image1' => $image,
                ];
            });

            return ApiResponseHelper::success(
                $formattedItems,
                $formattedItems->isEmpty() ? 'Cart is empty!' : 'Cart items retrieved successfully'
            );

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Add to Cart
     * POST /api/cart
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|integer|exists:products,product_id',
                'variation_id' => 'required|integer|exists:variation,id',
                'qty' => 'required|integer|min:1',
            ]);

            $user = $request->user();

            if (!$user) {
                return ApiResponseHelper::error('Unauthorized', 401);
            }

            // Find student by email
            $student = Student::where('email', $user->email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Student profile not found for this user', 404);
            }

            $studentId = $student->student_id;
            $productId = $validated['product_id'];
            $variationId = $validated['variation_id'];
            $qty = $validated['qty'];

            // Check if variation belongs to product
            $variation = Variation::where('id', $variationId)
                ->where('product_id', $productId)
                ->first();

            if (!$variation) {
                return ApiResponseHelper::error('Invalid variation for this product', 422);
            }

            // Check existing cart item
            $cartItem = Cart::where('student_id', $studentId)
                ->where('product_id', $productId)
                ->where('variation_id', $variationId)
                ->first();

            if (!$cartItem) {
                // New Item Logic
                // Check stock
                if ($variation->qty >= $qty) {
                    Cart::create([
                        'student_id' => $studentId,
                        'product_id' => $productId,
                        'variation_id' => $variationId,
                        'qty' => $qty
                    ]);

                    return ApiResponseHelper::success([
                        'added' => 1,
                        'qty' => $qty
                    ], 'Product added successfully.');
                } else {
                    return ApiResponseHelper::error('Quantity is not available at this moment!', 422);
                }
            } else {
                // Update Existing Logic
                // Check total stock needed (current in cart + new qty)
                $totalNeeded = $cartItem->qty + $qty;

                // Logic from core PHP: v.qty >= c.qty + new_qty
                if ($variation->qty >= $totalNeeded) {
                    $cartItem->qty = $totalNeeded;
                    $cartItem->save();

                    return ApiResponseHelper::success([
                        'added' => 1
                    ], 'Product updated successfully.');
                } else {
                    return ApiResponseHelper::error('Quantity is not available at this moment!', 422);
                }
            }

        } catch (Exception $e) {
            // Core PHP returns 500 on exception
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Remove from Cart (Single Item)
     * DELETE /api/cart/{cart_id}
     */
    public function destroy(Request $request, $id)
    {
        try {
            // Supports removing by cart_id (preferred)
            $cartItem = Cart::find($id);
            if ($cartItem) {
                $cartItem->delete();
                return ApiResponseHelper::success([
                    'removed' => 1
                ], 'Product removed successfully.');
            }

            return ApiResponseHelper::error('Cart item not found', 404);

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

}
