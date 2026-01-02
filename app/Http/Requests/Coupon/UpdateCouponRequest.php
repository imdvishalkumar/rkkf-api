<?php

namespace App\Http\Requests\Coupon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon');

        return [
            'coupon_txt' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('coupon', 'coupon_txt')->ignore($couponId, 'coupon_id'),
            ],
            'amount' => 'sometimes|required|numeric|min:0',
            'active' => 'nullable|boolean',
        ];
    }
}



