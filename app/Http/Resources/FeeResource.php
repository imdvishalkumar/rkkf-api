<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'fee_id' => $this->fee_id,
            'student_id' => $this->student_id,
            'months' => $this->months,
            'year' => $this->year,
            'date' => $this->date?->format('Y-m-d'),
            'amount' => (float) $this->amount,
            'mode' => $this->mode,
            'remarks' => $this->remarks,
            'student' => [
                'student_id' => $this->student->student_id ?? null,
                'name' => $this->student ? "{$this->student->firstname} {$this->student->lastname}" : null,
            ],
        ];
    }
}



