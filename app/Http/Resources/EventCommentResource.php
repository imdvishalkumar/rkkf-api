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
                'id' => (string) $this->user->user_id, // Unifying ID as string
                'name' => $this->user->name,
                'avatar' => $this->user->profile_image ? url('storage/' . $this->user->profile_image) : null,
            ],
            'reply_to_user' => $this->replyToUser
                ? [
                    'id' => (string) $this->replyToUser->user_id,
                    'name' => $this->replyToUser->name,
                    'avatar' => $this->replyToUser->profile_image ? url('storage/' . $this->replyToUser->profile_image) : null,
                ]
                : ($this->parent ? [ // Fallback for legacy replies (assume reply to root author)
                    'id' => (string) $this->parent->user->user_id,
                    'name' => $this->parent->user->name,
                    'avatar' => $this->parent->user->profile_image ? url('storage/' . $this->parent->user->profile_image) : null,
                ] : null),
            'replies_count' => $this->replies_count,
            'replies' => EventCommentResource::collection($this->whenLoaded('replies')),
        ];
    }
}
