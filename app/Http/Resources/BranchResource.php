<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'branch_id' => $this->branch_id,
            'name' => $this->name,
            'fees' => (float) $this->fees,
            'late' => (float) $this->late,
            'discount' => (float) $this->discount,
            'days' => $this->days ? explode(',', $this->days) : [],
        ];
    }
}



