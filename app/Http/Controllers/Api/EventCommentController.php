<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventCommentRequest;
use App\Http\Resources\EventCommentResource;
use App\Services\EventCommentService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventCommentController extends Controller
{
    protected $eventCommentService;

    public function __construct(EventCommentService $eventCommentService)
    {
        $this->eventCommentService = $eventCommentService;
    }

    /**
     * Get comments for an event.
     */
    public function index(Request $request, int $eventId)
    {
        $userId = $request->user()?->user_id; // Pass authenticated user ID if available

        $comments = $this->eventCommentService->getEventComments($eventId, $userId);

        return ApiResponseHelper::success(
            EventCommentResource::collection($comments),
            'Comments retrieved successfully'
        );
    }

    /**
     * Store a newly created comment or reply.
     */
    public function store(StoreEventCommentRequest $request, int $eventId)
    {
        // Validation and Authorization handled by FormRequest
        $user = $request->user();

        $comment = $this->eventCommentService->addComment(
            $eventId,
            $user->user_id,
            $request->input('comment'),
            $request->input('parent_id')
        );

        return ApiResponseHelper::success(
            new EventCommentResource($comment),
            'Comment added successfully',
            201
        );
    }

    /**
     * Toggle like for a comment.
     */
    public function toggleLike(Request $request, int $commentId)
    {
        $user = $request->user();

        // Admin check (also implemented in Request, but this route uses generic Request, so add explicit check)
        if ($user->isAdmin()) {
            return ApiResponseHelper::forbidden('Admins cannot perform this action.');
        }

        $result = $this->eventCommentService->toggleLike($commentId, $user->user_id);

        return ApiResponseHelper::success($result, 'Like status updated successfully');
    }
}
