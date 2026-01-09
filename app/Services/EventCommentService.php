<?php

namespace App\Services;

use App\Interfaces\EventCommentRepositoryInterface;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class EventCommentService
{
    protected $eventCommentRepository;

    public function __construct(EventCommentRepositoryInterface $eventCommentRepository)
    {
        $this->eventCommentRepository = $eventCommentRepository;
    }

    public function addComment(int $eventId, int $userId, string $comment, ?int $parentId = null)
    {
        // Add business validation if needed (e.g. event exists, is active)
        // Note: Request validation handles basic input + role auth

        // Ensure event exists
        Event::findOrFail($eventId);

        $replyToUserId = null;

        // If reply, ensure parent exists in same event
        if ($parentId) {
            $parent = $this->eventCommentRepository->findById($parentId);

            if (!$parent) {
                throw new \Exception("Parent comment not found.");
            }

            if ($parent->event_id != $eventId) {
                throw new \Exception("Parent comment does not belong to this event.");
            }

            // Strict 2-level hierarchy: Always resolve root parent
            // If parent has a parent_id, it is a reply. 
            // - The NEW parent_id should be the root (parent->parent_id).
            // - The reply_to_user_id should be the author of the comment we are replying to ($parent->user_id).

            if ($parent->parent_id) {
                $parentId = $parent->parent_id;
                $replyToUserId = $parent->user_id;
            } else {
                // We are replying to a root comment directly.
                // Usually we don't need reply_to_user_id if replying to root, 
                // OR we could set it to the root author. 
                // Let's set it to root author only if explicit requirement, otherwise null mimics standard behavior (Facebook etc).
                // Actually, user said: "track which user replied to which comment".
                // Let's set it to the target user regardless to be safe/explicit.
                $replyToUserId = $parent->user_id;
            }
        }

        $data = [
            'event_id' => $eventId,
            'user_id' => $userId,
            'comment' => $comment,
            'parent_id' => $parentId,
            'reply_to_user_id' => $replyToUserId,
            'is_active' => true,
        ];

        return $this->eventCommentRepository->create($data);
    }

    public function toggleLike(int $commentId, int $userId)
    {
        $liked = $this->eventCommentRepository->toggleLike($commentId, $userId);
        $comment = $this->eventCommentRepository->findById($commentId);

        return [
            'liked' => $liked,
            'total_likes' => $comment->total_likes
        ];
    }

    public function getEventComments(int $eventId, ?int $userId)
    {
        $comments = $this->eventCommentRepository->getEventComments($eventId);

        // We might want to post-process here if needed, but Resource is better for transformation.
        return $comments;
    }
}
