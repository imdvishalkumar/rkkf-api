<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->event_id,
            'image' => $this->image,
            'title' => $this->title, // Accessor
            'subtitle' => $this->subtitle,
            'date' => $this->from_date ? $this->from_date->format('Y-m-d') : null,
            'likes' => $this->likes,
            'comments' => $this->comments,
            'shares' => $this->shares,
            'time_ago' => $this->time_ago, // Accessor
            'is_liked' => $this->is_liked, // Accessor
            'category' => $this->category,
        ];
    }
}
