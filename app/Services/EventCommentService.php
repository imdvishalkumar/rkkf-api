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

        // If reply, ensure parent exists in same event
        if ($parentId) {
            $parent = $this->eventCommentRepository->findById($parentId);

            if (!$parent) {
                throw new \Exception("Parent comment not found.");
            }

            if ($parent->event_id != $eventId) {
                throw new \Exception("Parent comment does not belong to this event.");
            }

            // Flattening Logic: If parent has a parent, use THAT as the parent_id
            // This ensures only 1 level of nesting (Comment -> Replies)
            // Example: A (root) -> B (reply). If user replies to B, new comment C should have parent_id = A.
            // In the DB, B has parent_id = A. So we take B's parent_id.

            if ($parent->parent_id) {
                $parentId = $parent->parent_id;
            }
        }

        $data = [
            'event_id' => $eventId,
            'user_id' => $userId,
            'comment' => $comment,
            'parent_id' => $parentId,
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
