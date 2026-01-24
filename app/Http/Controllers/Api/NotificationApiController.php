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
            $search = $request->query('search', 'all');

            $query = DB::table('notification')
                ->where('student_id', $studentId)
                ->orderBy('timestamp', 'desc');

            // Fetch unique types for filtering
            $typesFromDb = DB::table('notification')
                ->where('student_id', $studentId)
                ->whereNotNull('type')
                ->distinct()
                ->pluck('type')
                ->toArray();

            $notificationTypesFromDb = array_merge(['all'], $typesFromDb);
            $notificationTypes = collect($notificationTypesFromDb)->map(function ($type) {
                return [
                    'type' => $type,
                    'icon' => $this->getNotificationIcon($type)
                ];
            });

            // Apply search
            if ($search === 'unread') {
                $query->where('viewed', 0);
            } elseif ($search !== 'all') {
                // Dynamic search: search by the provided type
                $query->where('type', $search);
            }

            $notifications = $query->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'details' => $item->details,
                    'type' => $item->type,
                    'icon' => $this->getNotificationIcon($item->type),
                    'is_read' => $item->viewed == 1,
                    // Format relative time if needed, or just send date
                    'date' => date('Y-m-d', strtotime($item->timestamp)),
                    'time_ago' => \Carbon\Carbon::parse($item->timestamp)->diffForHumans(),
                    'full_timestamp' => $item->timestamp,
                ];
            });

            return ApiResponseHelper::success([
                'data' => $notifications,
                'count' => $notifications->count(),
                'notification_types' => $notificationTypes
            ], 'Notifications retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get icon URL for a notification type
     */
    private function getNotificationIcon($type)
    {
        $type = strtolower($type);
        $iconMap = [
            'event' => 'ic_event.svg',
            'exam' => 'ic_examnot.svg',
            'fees' => 'ic_fees_reminder.svg',
        ];

        $icon = $iconMap[$type] ?? 'ic_general.svg';
        $baseUrl = url('images/notifications') . '/';
        return $baseUrl . $icon;
    }
}
