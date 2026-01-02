<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'attendance_id' => $this->attendance_id,
            'student_id' => $this->student_id,
            'date' => $this->date?->format('Y-m-d'),
            'attend' => $this->attend,
            'branch_id' => $this->branch_id,
            'is_additional' => (bool) $this->is_additional,
            'student' => [
                'student_id' => $this->student->student_id ?? null,
                'name' => $this->student ? "{$this->student->firstname} {$this->student->lastname}" : null,
            ],
        ];
    }
}



