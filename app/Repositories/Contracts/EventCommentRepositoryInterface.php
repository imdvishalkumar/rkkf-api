<?php

namespace App\Repositories\Contracts;

use App\Models\EventComment;
use Illuminate\Database\Eloquent\Collection;

interface EventCommentRepositoryInterface
{
    /**
     * Create a new comment or reply.
     *
     * @param array $data
     * @return EventComment
     */
    public function create(array $data): EventComment;

    /**
     * Find a comment by ID.
     *
     * @param int $id
     * @return EventComment|null
     */
    public function findById(int $id): ?EventComment;

    /**
     * Get all comments for an event with nested replies.
     *
     * @param int $eventId
     * @return Collection
     */
    public function getEventComments(int $eventId): Collection;

    /**
     * Toggle like/unlike for a comment.
     *
     * @param int $commentId
     * @param int $userId
     * @return array ['liked' => bool, 'total_likes' => int]
     */
    public function toggleLike(int $commentId, int $userId): array;

    /**
     * Check if a user has liked a comment.
     *
     * @param int $commentId
     * @param int $userId
     * @return bool
     */
    public function getLikeStatus(int $commentId, int $userId): bool;

    /**
     * Get total like count for a comment.
     *
     * @param int $commentId
     * @return int
     */
    public function getLikeCount(int $commentId): int;
}
