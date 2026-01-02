<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'student_id' => $this->student_id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'full_name' => $this->full_name ?? "{$this->firstname} {$this->lastname}",
            'email' => $this->email,
            'gender' => $this->gender,
            'dob' => $this->dob?->format('Y-m-d'),
            'doj' => $this->doj?->format('Y-m-d'),
            'active' => (bool) $this->active,
            'branch' => [
                'branch_id' => $this->branch->branch_id ?? null,
                'name' => $this->branch->name ?? null,
            ],
            'belt' => [
                'belt_id' => $this->belt->belt_id ?? null,
                'name' => $this->belt->name ?? null,
            ],
        ];
    }
}



