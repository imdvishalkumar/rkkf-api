<?php

namespace App\Http\Requests\Fee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => 'sometimes|required|exists:students,student_id',
            'months' => 'sometimes|required|integer|between:1,12',
            'year' => 'sometimes|required|integer|min:2020|max:2100',
            'amount' => 'sometimes|required|numeric|min:0',
            'coupon_id' => 'nullable|exists:coupon,coupon_id',
            'mode' => 'sometimes|required|string|in:cash,online,app,razorpay',
            'remarks' => 'nullable|string|max:500',
        ];
    }
}



