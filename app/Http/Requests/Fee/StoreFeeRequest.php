<?php

namespace App\Http\Requests\Fee;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'required|exists:students,student_id',
            'months' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2100',
            'amount' => 'required|numeric|min:0',
            'coupon_id' => 'nullable|exists:coupon,coupon_id',
            'mode' => 'required|string|in:cash,online,app,razorpay',
            'remarks' => 'nullable|string|max:500',
        ];
    }
}



