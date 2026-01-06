<?php

namespace App\Http\Requests\FrontendAPI;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstname' => 'required|string|max:50',
            'lastname' => 'required|string|max:50',
            'email' => 'required|email|unique:users,email|max:100',
            'password' => 'required|string|min:6',
            'mobile' => 'nullable|string|max:15',
            'role' => 'nullable|integer|in:0,1,2',
        ];
    }
}






