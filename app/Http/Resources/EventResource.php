<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\EventLike;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get like count (total active likes)
        $likeCount = EventLike::where('event_id', $this->event_id)
            ->where('is_liked', true)
            ->count();

        // Get user's like status if authenticated
        $isLiked = false;
        if ($request->user()) {
            $userLike = EventLike::where('event_id', $this->event_id)
                ->where('user_id', $request->user()->user_id)
                ->first();
            $isLiked = $userLike ? $userLike->is_liked : false;
        }

        return [
            'id' => (string) $this->event_id,
            'image' => $this->image,
            'title' => $this->title, // Accessor
            'subtitle' => $this->subtitle,
            'date' => $this->from_date ? $this->from_date->format('Y-m-d') : null,
            'likes' => $likeCount, // Use actual like count from event_likes table
            'comments' => $this->event_comments_count ?? 0,
            'shares' => $this->event_shares_count ?? 0,
            'share_event_link' => 'https://api.rkkf.imobiledesigns.cloud', //. $this->event_id,
            'time_ago' => $this->time_ago, // Accessor
            'is_liked' => $isLiked, // Use actual like status from event_likes table
            'category' => $this->category ? $this->category->name : null,
        ];
    }
}
