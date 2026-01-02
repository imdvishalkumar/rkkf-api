<?php

namespace App\Http\Requests\Fee;

use Illuminate\Foundation\Http\FormRequest;

class EnterFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disable_student_id' => 'required|exists:students,student_id',
            'amount' => 'required|numeric|min:0',
            'increaseMonth' => 'required|integer|min:1|max:12',
            'remarks' => 'nullable|string|max:500',
            'disable_month' => 'nullable|integer|between:1,12',
            'disable_year' => 'nullable|integer|min:2020|max:2100',
            'doj' => 'nullable|string',
        ];
    }
}



