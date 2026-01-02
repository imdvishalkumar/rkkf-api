<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'details' => 'nullable|string',
            'image1' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image2' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'image3' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'belt_ids' => 'nullable|string',
            'active' => 'nullable|boolean',
            'variations' => 'nullable|array',
            'variations.*.variation' => 'required|string',
            'variations.*.price' => 'required|numeric|min:0',
            'variations.*.qty' => 'required|integer|min:0',
        ];
    }
}



