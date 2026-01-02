<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student');

        return [
            'firstname' => 'sometimes|required|string|max:255',
            'lastname' => 'sometimes|required|string|max:255|alpha',
            'gender' => 'sometimes|required|integer|in:1,2',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('students', 'email')->ignore($studentId, 'student_id'),
                Rule::unique('users', 'email'),
            ],
            'belt_id' => 'sometimes|required|exists:belt,belt_id',
            'branch_id' => 'sometimes|required|exists:branch,branch_id',
            'active' => 'sometimes|boolean',
        ];
    }
}

