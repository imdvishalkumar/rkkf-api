<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;

class CouponApiController extends Controller
{
    /**
     * Validate and apply coupon
     * GET /api/coupons/validate?coupon={code}
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validate(Request $request)
    {
        try {
            $couponCode = $request->query('coupon');

            // Validate input
            if (empty($couponCode) || strlen(trim($couponCode)) < 3) {
                return ApiResponseHelper::error('Invalid Coupon!', 200);
            }

            $couponCode = trim($couponCode);

            // Find unused coupon
            $coupon = Coupon::where('coupon_txt', $couponCode)
                ->where('used', 0)
                ->first();

            if (!$coupon) {
                return ApiResponseHelper::success([
                    'success' => 1,
                    'message' => 'No Coupon Found!'
                ], 'No Coupon Found!', 422);
            }

            // Mark coupon as used
            $coupon->used = 1;
            $coupon->save();

            return ApiResponseHelper::success([
                'couponData' => [
                    'coupon_id' => $coupon->coupon_id,
                    'coupon_txt' => $coupon->coupon_txt,
                    'amount' => $coupon->amount,
                ]
            ], 'Coupon applied successfully');

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to validate coupon',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
