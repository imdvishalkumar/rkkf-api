<?php

namespace App\Http\Requests\Exam;

use Illuminate\Foundation\Http\FormRequest;

class SetEligibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exam_id' => 'required|exists:exam,exam_id',
            'student_id' => 'required|exists:students,student_id',
            'eligible' => 'required|boolean',
        ];
    }
}



