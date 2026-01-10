<?php

namespace App\Services;

use App\Models\Leave;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class LeaveService
{
    /**
     * Annual leave allowance per student.
     * Can be made configurable per branch/student if needed.
     */
    const ANNUAL_LEAVE_LIMIT = 25;

    /**
     * Apply for leave.
     *
     * @param int $studentId
     * @param array $data
     * @return array
     */
    public function applyLeave(int $studentId, array $data): array
    {
        try {
            $student = Student::find($studentId);

            if (!$student) {
                return [
                    'success' => false,
                    'message' => 'Student not found',
                ];
            }

            $fromDate = Carbon::parse($data['from_date']);
            $toDate = Carbon::parse($data['to_date']);
            $leaveDays = $fromDate->diffInDays($toDate) + 1;

            // Check available leaves
            $stats = $this->getLeaveStats($studentId);
            if ($leaveDays > $stats['available']) {
                return [
                    'success' => false,
                    'message' => "Insufficient leave balance. Available: {$stats['available']} days, Requested: {$leaveDays} days",
                ];
            }

            // Create leave record
            $leave = new Leave();
            $leave->student_id = $studentId;
            $leave->from_date = $fromDate->format('Y-m-d');
            $leave->to_date = $toDate->format('Y-m-d');
            $leave->reason = $data['reason'];
            $leave->status = Leave::STATUS_PENDING;
            $leave->applied_at = now();
            $leave->save();

            return [
                'success' => true,
                'leave' => $leave,
                'leave_days' => $leaveDays,
            ];

        } catch (Exception $e) {
            Log::error('Error applying for leave: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to submit leave request: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get leave history for a student.
     *
     * @param int $studentId
     * @param int $perPage
     * @return array
     */
    public function getLeaveHistory(int $studentId, int $perPage = 15): array
    {
        $leaves = Leave::where('student_id', $studentId)
            ->orderBy('applied_at', 'desc')
            ->orderBy('leave_id', 'desc')
            ->paginate($perPage);

        $formattedLeaves = [];
        foreach ($leaves as $leave) {
            $reviewerName = null;
            if ($leave->reviewed_by) {
                $reviewer = $leave->reviewer;
                $reviewerName = $reviewer ? ($reviewer->firstname . ' ' . $reviewer->lastname) : null;
            }

            $formattedLeaves[] = [
                'id' => $leave->leave_id,
                'from_date' => $leave->from_date ? $leave->from_date->format('Y-m-d') : null,
                'to_date' => $leave->to_date ? $leave->to_date->format('Y-m-d') : null,
                'from_date_display' => $leave->from_date ? $leave->from_date->format('d-m-Y') : null,
                'to_date_display' => $leave->to_date ? $leave->to_date->format('d-m-Y') : null,
                'leave_days' => $leave->leave_days,
                'reason' => $leave->reason,
                'status' => $leave->status_label,
                'status_code' => $leave->status,
                'applied_at' => $leave->applied_at ? $leave->applied_at->format('Y-m-d') : null,
                'applied_at_display' => $leave->applied_at ? 'Applied on ' . $leave->applied_at->format('M d') : null,
                'reviewed_by' => $reviewerName,
            ];
        }

        return [
            'leaves' => $formattedLeaves,
            'pagination' => [
                'current_page' => $leaves->currentPage(),
                'per_page' => $leaves->perPage(),
                'total' => $leaves->total(),
                'last_page' => $leaves->lastPage(),
            ],
        ];
    }

    /**
     * Get leave statistics for a student.
     *
     * @param int $studentId
     * @return array
     */
    public function getLeaveStats(int $studentId): array
    {
        $currentYear = (int) date('Y');

        // Count approved leave days for current year
        $usedLeaves = Leave::where('student_id', $studentId)
            ->where('status', Leave::STATUS_APPROVED)
            ->whereYear('from_date', $currentYear)
            ->get()
            ->sum(function ($leave) {
                return $leave->leave_days;
            });

        // Also count pending as tentatively used
        $pendingLeaves = Leave::where('student_id', $studentId)
            ->where('status', Leave::STATUS_PENDING)
            ->whereYear('from_date', $currentYear)
            ->get()
            ->sum(function ($leave) {
                return $leave->leave_days;
            });

        $totalLeaves = self::ANNUAL_LEAVE_LIMIT;
        $available = max(0, $totalLeaves - $usedLeaves - $pendingLeaves);

        return [
            'total_leaves' => $totalLeaves,
            'used' => $usedLeaves,
            'pending' => $pendingLeaves,
            'available' => $available,
        ];
    }
}
