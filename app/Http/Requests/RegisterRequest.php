<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\UserRole;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public registration endpoint
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
            'email' => 'required|email|unique:users,email|max:100',
            'mobile' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in([UserRole::USER->value, UserRole::INSTRUCTOR->value, 'user', 'instructor'])],
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'branch_id' => 'nullable|integer',
            'belt_id' => 'nullable|integer',
            'address' => 'nullable|string',
            'pincode' => 'nullable|string',
            'profile_img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // Required fields
            'firstname.required' => 'The first name field is required.',
            'lastname.required' => 'The last name field is required.',
            'email.required' => 'The email field is required.',
            'mobile.required' => 'The mobile field is required.',
            'password.required' => 'The password field is required.',
            'role.required' => 'The role field is required.',
            
            // Email validation
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'email.max' => 'The email may not be greater than 100 characters.',
            
            // String validation
            'firstname.string' => 'The first name must be a string.',
            'firstname.max' => 'The first name may not be greater than 255 characters.',
            'lastname.string' => 'The last name must be a string.',
            'lastname.max' => 'The last name may not be greater than 255 characters.',
            'mobile.string' => 'The mobile must be a string.',
            'mobile.max' => 'The mobile may not be greater than 20 characters.',
            'password.string' => 'The password must be a string.',
            'password.min' => 'The password must be at least 6 characters.',
            
            // Role validation
            'role.in' => 'The selected role is invalid. Allowed values: user, instructor.',
            
            // Date validation
            'dob.date' => 'The date of birth must be a valid date.',
            
            // Gender validation
            'gender.in' => 'The selected gender is invalid. Allowed values: Male, Female, Other.',
            
            // Integer validation
            'branch_id.integer' => 'The branch ID must be an integer.',
            'belt_id.integer' => 'The belt ID must be an integer.',
            
            // Profile image validation
            'profile_img.image' => 'The profile image must be an image file.',
            'profile_img.mimes' => 'The profile image must be a file of type: jpg, jpeg, png, webp.',
            'profile_img.max' => 'The profile image must not be larger than 2MB (2048 KB).',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'firstname' => 'first name',
            'lastname' => 'last name',
            'profile_img' => 'profile image',
            'dob' => 'date of birth',
            'branch_id' => 'branch ID',
            'belt_id' => 'belt ID',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator);
    }
}

