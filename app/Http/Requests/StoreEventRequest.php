<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'event_start_datetime' => 'required|date',
            'event_end_datetime' => 'required|date|after:event_start_datetime',
            'description' => 'nullable|string',
            'venue' => 'nullable|string',
            'fees' => 'nullable|numeric',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string',
            'subtitle' => 'nullable|string|max:255',
            'likes' => 'nullable|integer',
            'comments' => 'nullable|integer',
            'shares' => 'nullable|integer',
            // Add other legacy fields if we decide to expose them in API
        ];
    }
}
