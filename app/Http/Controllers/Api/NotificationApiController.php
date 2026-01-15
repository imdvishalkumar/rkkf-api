<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class NotificationApiController extends Controller
{
    /**
     * Get notifications with optional filtering
     * GET /api/notifications?filter={all|unread|exams|event|fees}
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // Get student ID from user's email if possible, or use user_id fallback logic
            // Assuming simplified logic where we can get student_id associated with user
            $student = DB::table('students')->where('email', $user->email)->first();

            if (!$student) {
                // Fallback: mostly for testing if user is not a student
                // For now return empty or handle as error
                return ApiResponseHelper::success(['data' => []], 'No student profile found for user');
            }

            $studentId = $student->student_id;
            $filter = $request->query('filter', 'all');

            $query = DB::table('notification')
                ->where('student_id', $studentId)
                ->orderBy('timestamp', 'desc');

            // Apply filters
            switch ($filter) {
                case 'unread':
                    $query->where('viewed', 0);
                    break;
                case 'exams':
                case 'exam': // handle singular too just in case
                    $query->where('type', 'Exam');
                    break;
                case 'event':
                case 'events':
                    $query->where('type', 'Event');
                    break;
                case 'fees':
                case 'fee':
                    $query->where('type', 'Fees'); // Assuming type is 'Fees' based on typical naming
                    break;
                case 'all':
                default:
                    // No additional filter
                    break;
            }

            $notifications = $query->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'details' => $item->details,
                    'type' => $item->type,
                    'is_read' => $item->viewed == 1,
                    // Format relative time if needed, or just send date
                    'date' => date('Y-m-d', strtotime($item->timestamp)),
                    'time_ago' => \Carbon\Carbon::parse($item->timestamp)->diffForHumans(),
                    'full_timestamp' => $item->timestamp,
                ];
            });

            return ApiResponseHelper::success([
                'data' => $notifications,
                'count' => $notifications->count()
            ], 'Notifications retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }
}
