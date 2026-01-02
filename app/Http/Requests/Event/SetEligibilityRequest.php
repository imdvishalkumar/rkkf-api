<?php

namespace App\Http\Requests\Event;

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
            'event_id' => 'required|exists:event,event_id',
            'student_id' => 'required|exists:students,student_id',
            'eligible' => 'required|boolean',
        ];
    }
}



