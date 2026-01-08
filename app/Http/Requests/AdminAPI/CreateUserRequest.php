<?php

namespace App\Http\Requests\AdminAPI;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            'role' => 'required|string|in:user,admin,instructor', // user, admin, instructor
            'mobile' => 'nullable|string|max:15',
        ];
    }
}








