<?php

namespace App\Http\Requests\AdminAPI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSuperAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'firstname' => 'sometimes|required|string|max:50',
            'lastname' => 'sometimes|required|string|max:50',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId, 'user_id'),
                'max:100',
            ],
            'password' => 'sometimes|nullable|string|min:6',
            'mobile' => 'nullable|string|max:15',
        ];
    }
}








