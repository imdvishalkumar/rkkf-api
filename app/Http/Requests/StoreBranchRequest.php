<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:50',
            'days' => 'required|string|max:100',
            'fees' => 'required|integer|min:0',
            'late' => 'required|integer|min:0',
            'discount' => 'required|integer|min:0',
            // New optional fields
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
