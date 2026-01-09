<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'comment' => $this->comment,
            'created_at' => $this->created_at->toIso8601String(),
            'created_human' => $this->created_at->diffForHumans(),
            'total_likes' => $this->total_likes,
            'is_liked' => $this->is_liked, // Uses attribute/appends
            'user' => [
                'id' => $this->user->user_id, // Ensure correct ID key
                'name' => $this->user->name,
                // Add avatar if available
            ],
            'replies_count' => $this->replies_count,
            'replies' => EventCommentResource::collection($this->whenLoaded('replies')),
        ];
    }
}
