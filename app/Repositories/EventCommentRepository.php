<?php

namespace App\Repositories;

use App\Interfaces\EventCommentRepositoryInterface;
use App\Models\EventComment;
use App\Models\EventCommentLike;

class EventCommentRepository implements EventCommentRepositoryInterface
{
    public function create(array $data)
    {
        return EventComment::create($data);
    }

    public function findById(int $id)
    {
        return EventComment::findOrFail($id);
    }

    public function getEventComments(int $eventId)
    {
        return EventComment::with(['user', 'replies.user', 'replies.likes', 'likes']) // Eager load for performance
            ->where('event_id', $eventId)
            ->parentComments() // Only parents
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function toggleLike(int $commentId, int $userId)
    {
        $existingLike = EventCommentLike::where('event_comment_id', $commentId)
            ->where('user_id', $userId)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            return false; // unliked
        } else {
            EventCommentLike::create([
                'event_comment_id' => $commentId,
                'user_id' => $userId,
            ]);
            return true; // liked
        }
    }

    public function getLikeStatus(int $commentId, int $userId)
    {
        return EventCommentLike::where('event_comment_id', $commentId)
            ->where('user_id', $userId)
            ->exists();
    }
}
