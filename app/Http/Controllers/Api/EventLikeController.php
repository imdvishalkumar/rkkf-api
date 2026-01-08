<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventLike;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Exception;

class EventLikeController extends Controller
{
    /**
     * Toggle like/unlike for an event
     * POST /api/events/{event_id}/like
     */
    public function toggleLike(Request $request, $eventId)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ApiResponseHelper::error('Unauthenticated', 401);
            }

            // Validate event exists
            $event = Event::find($eventId);
            if (!$event) {
                return ApiResponseHelper::error('Event not found', 404);
            }

            // Check if like record exists
            $like = EventLike::where('user_id', $user->user_id)
                ->where('event_id', $eventId)
                ->first();

            if ($like) {
                // Toggle the like status
                $like->is_liked = !$like->is_liked;
                $like->save();

                $message = $like->is_liked ? 'Event liked successfully' : 'Event unliked successfully';
            } else {
                // Create new like record
                $like = EventLike::create([
                    'user_id' => $user->user_id,
                    'event_id' => $eventId,
                    'is_liked' => true,
                ]);

                $message = 'Event liked successfully';
            }

            // Get updated like count
            $likeCount = EventLike::where('event_id', $eventId)
                ->where('is_liked', true)
                ->count();

            return ApiResponseHelper::success([
                'is_liked' => $like->is_liked,
                'like_count' => $likeCount,
            ], $message);

        } catch (Exception $e) {
            return ApiResponseHelper::error(
                'Failed to toggle like: ' . $e->getMessage(),
                ApiResponseHelper::getStatusCode($e, 500)
            );
        }
    }

    /**
     * Get like status for an event
     * GET /api/events/{event_id}/like
     */
    public function getLikeStatus(Request $request, $eventId)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return ApiResponseHelper::error('Unauthenticated', 401);
            }

            // Validate event exists
            $event = Event::find($eventId);
            if (!$event) {
                return ApiResponseHelper::error('Event not found', 404);
            }

            // Get like status for current user
            $like = EventLike::where('user_id', $user->user_id)
                ->where('event_id', $eventId)
                ->first();

            $isLiked = $like ? $like->is_liked : false;

            // Get total like count
            $likeCount = EventLike::where('event_id', $eventId)
                ->where('is_liked', true)
                ->count();

            return ApiResponseHelper::success([
                'is_liked' => $isLiked,
                'like_count' => $likeCount,
            ], 'Like status retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error(
                'Failed to get like status: ' . $e->getMessage(),
                ApiResponseHelper::getStatusCode($e, 500)
            );
        }
    }
}
