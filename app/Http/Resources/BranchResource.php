<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->branch_id,
            'name' => $this->name,
            'days' => $this->days,
            'fees' => $this->fees,
            'late' => $this->late,
            'discount' => $this->discount,

            // New Fields
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'phone' => $this->phone,
            'email' => $this->email,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'map_link' => $this->map_link,
            'is_active' => (bool) $this->is_active,

            // Helpful formatted address
            'full_address' => sprintf(
                "%s%s%s",
                $this->address,
                ($this->city ? ", " . $this->city : ""),
                ($this->zip_code ? " - " . $this->zip_code : "")
            ),
        ];
    }
}
