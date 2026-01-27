<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;

class CouponApiController extends Controller
{
    /**
     * List all coupons (Admin)
     * GET /api/admin/coupons
     */
    public function index()
    {
        try {
            $coupons = Coupon::orderBy('coupon_id', 'desc')->get();
            return ApiResponseHelper::success($coupons, 'Coupons fetched successfully');
        } catch (\Exception $e) {
            return ApiResponseHelper::error('Failed to fetch coupons', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create a new coupon (Admin)
     * POST /api/admin/coupons
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'coupon_txt' => 'required|string|unique:coupon,coupon_txt|min:3',
                'amount' => 'required|numeric|min:0',
            ]);

            $coupon = Coupon::create([
                'coupon_txt' => trim($validated['coupon_txt']),
                'amount' => $validated['amount'],
                'used' => 0 // Default to unused
            ]);

            return ApiResponseHelper::success($coupon, 'Coupon created successfully', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::error($e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponseHelper::error('Failed to create coupon', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Show a specific coupon (Admin)
     * GET /api/admin/coupons/{id}
     */
    public function show($id)
    {
        try {
            $coupon = Coupon::find($id);
            if (!$coupon) {
                return ApiResponseHelper::error('Coupon not found', 404);
            }
            return ApiResponseHelper::success($coupon, 'Coupon details fetched successfully');
        } catch (\Exception $e) {
            return ApiResponseHelper::error('Failed to fetch coupon', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update a coupon (Admin)
     * PUT /api/admin/coupons/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $coupon = Coupon::find($id);
            if (!$coupon) {
                return ApiResponseHelper::error('Coupon not found', 404);
            }

            $validated = $request->validate([
                'coupon_txt' => 'required|string|min:3|unique:coupon,coupon_txt,' . $id . ',coupon_id',
                'amount' => 'required|numeric|min:0',
                'used' => 'nullable|boolean' // Allow admin to reset used status
            ]);

            $coupon->update([
                'coupon_txt' => trim($validated['coupon_txt']),
                'amount' => $validated['amount'],
                'used' => isset($validated['used']) ? $validated['used'] : $coupon->used
            ]);

            return ApiResponseHelper::success($coupon, 'Coupon updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::error($e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponseHelper::error('Failed to update coupon', 500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a coupon (Admin)
     * DELETE /api/admin/coupons/{id}
     */
    public function destroy($id)
    {
        try {
            $coupon = Coupon::find($id);
            if (!$coupon) {
                return ApiResponseHelper::error('Coupon not found', 404);
            }

            $coupon->delete();
            return ApiResponseHelper::success(null, 'Coupon deleted successfully');
        } catch (\Exception $e) {
            return ApiResponseHelper::error('Failed to delete coupon', 500, ['error' => $e->getMessage()]);
        }
    }
    /**
     * Apply coupon code (POST)
     * POST /api/coupons/apply
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apply(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'coupon_code' => 'required|string|min:3',
                'action' => 'nullable|string|in:apply'
            ]);

            $couponCode = trim($request->input('coupon_code'));
            // Default to 'validate' or 'apply' based on need. Safe default is 'validate'.
            $action = $request->input('action', 'apply');

            // Find unused coupon
            $coupon = Coupon::where('coupon_txt', $couponCode)
                ->where('used', 0) // Ensure it's not already used
                ->first();

            if (!$coupon) {
                return ApiResponseHelper::error('Invalid or used coupon code!', 422);
            }

            $message = 'Coupon is valid';

            // Only mark coupon as used if the action is 'apply'
            // Note: Actual 'used' marking happens during order placement
            // This endpoint just validates the coupon
            if ($action === 'apply') {
                // Don't mark as used here - marking happens in order creation
                $message = 'Coupon applied successfully';
            }

            return ApiResponseHelper::success([
                'couponData' => [
                    'coupon_id' => $coupon->coupon_id,
                    'coupon_txt' => $coupon->coupon_txt,
                    'amount' => $coupon->amount,
                ]
            ], $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::error($e->errors(), 422);
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to process coupon',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
