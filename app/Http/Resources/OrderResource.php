<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->order_id,
            'student_id' => $this->student_id,
            'name_var' => $this->name_var,
            'qty' => $this->qty,
            'p_price' => (float) $this->p_price,
            'date' => $this->date?->format('Y-m-d'),
            'status' => $this->status,
            'rp_order_id' => $this->rp_order_id,
            'counter' => $this->counter,
            'flag_delivered' => (bool) $this->flag_delivered,
            'viewed' => (bool) $this->viewed,
            'student' => [
                'student_id' => $this->student->student_id ?? null,
                'name' => $this->student ? "{$this->student->firstname} {$this->student->lastname}" : null,
            ],
        ];
    }
}



