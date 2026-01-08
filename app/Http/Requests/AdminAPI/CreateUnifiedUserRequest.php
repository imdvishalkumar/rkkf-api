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
        $user = $this->user();
        $userViaAuth = auth('sanctum')->user();
        
        \Illuminate\Support\Facades\Log::info('CreateUnifiedUserRequest::authorize called', [
            'user_via_request' => $user?->user_id,
            'user_via_auth' => $userViaAuth?->user_id,
            'user_exists' => $user !== null,
            'auth_user_exists' => $userViaAuth !== null,
            'path' => $this->path(),
            'method' => $this->method(),
        ]);
        
        // Since middleware already authenticated, always return true
        // The controller will check admin role
        return true;
    }
    
    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        \Illuminate\Support\Facades\Log::warning('CreateUnifiedUserRequest: Authorization failed', [
            'user' => $this->user()?->user_id,
            'path' => $this->path(),
        ]);
        throw new \Illuminate\Auth\Access\AuthorizationException('This action is unauthorized.');
    }
    
    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Illuminate\Support\Facades\Log::warning('CreateUnifiedUserRequest: Validation failed', [
            'errors' => $validator->errors()->toArray(),
            'user' => $this->user()?->user_id,
        ]);
        throw new \Illuminate\Validation\ValidationException($validator);
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
            'profile_img.image' => 'The profile image must be an image file.',
            'profile_img.mimes' => 'The profile image must be a file of type: jpg, jpeg, png, webp.',
            'profile_img.max' => 'The profile image must not be larger than 2MB (2048 KB).',
        ];
    }
}
