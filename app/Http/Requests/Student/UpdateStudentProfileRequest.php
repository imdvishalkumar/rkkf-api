<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address' => 'nullable|string|max:500',
            'pincode' => 'nullable|string|max:10',
            'dadwp' => 'nullable|string|max:20',
            'selfno' => 'nullable|string|max:20',
            'momno' => 'nullable|string|max:20',
            'profile_img' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
