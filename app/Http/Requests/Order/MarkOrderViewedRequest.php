<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class MarkOrderViewedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,order_id',
        ];
    }
}



