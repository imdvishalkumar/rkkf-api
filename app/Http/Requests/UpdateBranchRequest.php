<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:50',
            'days' => 'sometimes|required|string|max:100',
            'fees' => 'sometimes|required|integer|min:0',
            'late' => 'sometimes|required|integer|min:0',
            'discount' => 'sometimes|required|integer|min:0',

            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'map_link' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }
}
