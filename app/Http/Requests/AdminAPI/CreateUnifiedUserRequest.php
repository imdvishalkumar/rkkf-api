<?php

namespace App\Http\Requests\AdminAPI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\UserRole;

class CreateUnifiedUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Auth handled by middleware/controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:students,email',
            'mobile' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in([UserRole::USER->value, UserRole::INSTRUCTOR->value, 'user', 'instructor'])],
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'branch_id' => 'nullable|integer|exists:branch,branch_id',
            'belt_id' => 'nullable|integer|exists:belt,belt_id',
            'address' => 'nullable|string',
            'pincode' => 'nullable|string',
        ];
    }
}
