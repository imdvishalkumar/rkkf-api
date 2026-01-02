<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SearchStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grno' => 'required|string|min:1',
            'branch_id' => 'nullable|integer|exists:branch,branch_id',
            'active' => 'nullable|boolean',
        ];
    }
}



