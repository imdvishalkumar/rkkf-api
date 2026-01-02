<?php

namespace App\Http\Requests\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coupon_txt' => 'required|string|max:255|unique:coupon,coupon_txt',
            'amount' => 'required|numeric|min:0',
            'active' => 'nullable|boolean',
        ];
    }
}



