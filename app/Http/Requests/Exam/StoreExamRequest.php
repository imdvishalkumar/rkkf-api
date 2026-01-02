<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'sessions_count' => 'required|integer|min:1',
            'fees' => 'required|numeric|min:0',
            'fess_due_date' => 'required|date',
            'from_criteria' => 'nullable|integer',
            'to_criteria' => 'nullable|integer',
            'active' => 'nullable|boolean',
        ];
    }
}



