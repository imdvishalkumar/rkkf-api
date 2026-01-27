<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeaveService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class LeaveApiController extends Controller
{
    protected $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    /**
     * Apply for leave.
     * POST /api/leaves/apply
     * 
     * Student ID is derived from auth()->user()->student->student_id
     */
    public function apply(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponseHelper::error('User not authenticated', 401);
            }

            $student = $user->student;

            if (!$student) {
                return ApiResponseHelper::error('No student profile linked to this user', 404);
            }

            $request->validate([
                'from_date' => 'required|date',
                'to_date' => 'required|date|after_or_equal:from_date',
                'subject' => 'required|string|max:255',
                'reason' => 'required|string|max:500',
            ]);

            $result = $this->leaveService->applyLeave($student->student_id, [
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'subject' => $request->input('subject'),
                'reason' => $request->input('reason'),
            ]);

            if (!$result['success']) {
                return ApiResponseHelper::error($result['message'], 422);
            }

            return ApiResponseHelper::success([
                'leave_id' => $result['leave']->leave_id,
                'status' => $result['leave']->status_label,
                'leave_days' => $result['leave_days'],
            ], 'Leave request submitted successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get leave history for authenticated student.
     * GET /api/leaves/history
     * 
     * Query params:
     * - per_page: int (optional, default: 15)
     */
    public function history(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponseHelper::error('User not authenticated', 401);
            }

            $student = $user->student;

            if (!$student) {
                return ApiResponseHelper::error('No student profile linked to this user', 404);
            }

            $request->validate([
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $perPage = $request->input('per_page', 15);

            // Get stats
            $stats = $this->leaveService->getLeaveStats($student->student_id);

            // Get history
            $history = $this->leaveService->getLeaveHistory($student->student_id, $perPage);

            return ApiResponseHelper::success([
                'stats' => $stats,
                'leaves' => $history['leaves'],
                'pagination' => $history['pagination'],
            ], 'Leave history retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
}
