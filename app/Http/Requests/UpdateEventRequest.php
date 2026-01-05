<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'event_start_datetime' => 'sometimes|required|date',
            'event_end_datetime' => 'sometimes|required|date|after:event_start_datetime',
            'description' => 'nullable|string',
            'venue' => 'nullable|string',
            'fees' => 'nullable|numeric',
            'category' => 'nullable|string|max:255',
            'image' => 'nullable|string',
            'subtitle' => 'nullable|string|max:255',
            'likes' => 'nullable|integer',
            'comments' => 'nullable|integer',
            'shares' => 'nullable|integer',
        ];
    }
}
