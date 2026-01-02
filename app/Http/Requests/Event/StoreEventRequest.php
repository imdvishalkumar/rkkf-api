<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'venue' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'fees' => 'required|numeric|min:0',
            'fees_due_date' => 'required|date',
            'penalty' => 'nullable|numeric|min:0',
            'penalty_due_date' => 'nullable|date',
            'active' => 'nullable|boolean',
        ];
    }
}



