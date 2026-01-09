<?php

namespace App\Interfaces;

interface EventCommentRepositoryInterface
{
    public function create(array $data);
    public function findById(int $id);
    public function getEventComments(int $eventId);
    public function toggleLike(int $commentId, int $userId);
    public function getLikeStatus(int $commentId, int $userId);
}
