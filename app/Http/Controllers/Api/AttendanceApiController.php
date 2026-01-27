<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Services\StudentService;
use App\Services\ExamService;
use App\Services\EventService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class AttendanceApiController extends Controller
{
    protected $attendanceService;
    protected $studentService;
    protected $examService;
    protected $eventService;

    public function __construct(
        AttendanceService $attendanceService,
        StudentService $studentService,
        ExamService $examService,
        EventService $eventService
    ) {
        $this->attendanceService = $attendanceService;
        $this->studentService = $studentService;
        $this->examService = $examService;
        $this->eventService = $eventService;
    }

    /**
     * Get students for attendance by branch ID
     * GET /api/attendance/get-students?branchId=1
     */
    public function getStudents(Request $request)
    {
        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'branchId' => 'required|integer|exists:branch,branch_id',
            ]);

            if ($validator->fails()) {
                return ApiResponseHelper::error($validator->errors()->first(), 200);
            }

            $branchId = $request->input('branchId');

            // Get students for attendance (excluding fastrack students)
            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.branch_id', $branchId)
                ->whereNotIn('s.student_id', function ($query) {
                    $query->select('student_id')->from('fastrack');
                })
                ->select(
                    's.student_id',
                    's.firstname',
                    's.lastname',
                    's.active',
                    'br.name as branch_name',
                    DB::raw('EXISTS(SELECT student_id FROM leave_table WHERE CURDATE() BETWEEN from_date AND to_date AND student_id = s.student_id) as present')
                )
                ->get();

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 200);
        }
    }

    /**
     * Insert/Update attendance (POST - keeping for compatibility)
     */
    public function insertAttendance(Request $request)
    {
        try {
            $request->validate([
                'branchId' => 'required|integer|exists:branch,branch_id',
                'date' => 'required|date',
                'attendance' => 'required|array',
                'attendance.*.student_id' => 'required|integer|exists:students,student_id',
                'attendance.*.attend' => 'required|string|in:P,A,L',
            ]);

            $branchId = $request->input('branchId');
            $date = $request->input('date');
            $attendanceData = $request->input('attendance');
            $userId = $request->user()->user_id;

            DB::beginTransaction();

            foreach ($attendanceData as $item) {
                DB::table('attendance')
                    ->updateOrInsert(
                        [
                            'student_id' => $item['student_id'],
                            'branch_id' => $branchId,
                            'date' => $date,
                        ],
                        [
                            'attend' => $item['attend'],
                            'user_id' => $userId,
                            'is_additional' => 0,
                        ]
                    );
            }

            DB::commit();

            return ApiResponseHelper::success(null, 'Attendance saved successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get students for additional attendance
     * GET /api/attendance/additional/get-students?branchId=1&date=2024-01-15
     */
    public function getAdditionalStudents(Request $request)
    {
        try {
            $request->validate([
                'branchId' => 'required|integer|exists:branch,branch_id',
                'date' => 'required|date',
            ]);

            $branchId = $request->input('branchId');
            $date = $request->input('date');

            // Get students who have attendance on this date
            $students = DB::table('attendance as a')
                ->join('students as s', 'a.student_id', '=', 's.student_id')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('a.branch_id', $branchId)
                ->where('a.date', $date)
                ->where('s.active', 1)
                ->select(
                    'a.attendance_id',
                    's.student_id',
                    's.firstname',
                    's.lastname',
                    'br.name as branch_name'
                )
                ->get();

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Insert/Update additional attendance (POST)
     */
    public function takeAdditionalAttendance(Request $request)
    {
        try {
            $request->validate([
                'branchId' => 'required|integer|exists:branch,branch_id',
                'date' => 'required|date',
                'attendance' => 'required|array',
                'attendance.*.attendance_id' => 'required|integer|exists:attendance,attendance_id',
                'attendance.*.attend' => 'required|string|in:P,A,L',
            ]);

            $attendanceData = $request->input('attendance');

            DB::beginTransaction();

            foreach ($attendanceData as $item) {
                DB::table('attendance')
                    ->where('attendance_id', $item['attendance_id'])
                    ->update([
                        'attend' => $item['attend'],
                        'is_additional' => 1,
                    ]);
            }

            DB::commit();

            return ApiResponseHelper::success(null, 'Additional attendance saved successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get attendance log
     * GET /api/attendance/log?branch_id=1&start_date=2024-01-01&end_date=2024-01-31&student_id=101
     */
    public function getAttendanceLog(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'student_id' => 'nullable|integer|exists:students,student_id',
            ]);

            $branchId = $request->input('branch_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $studentId = $request->input('student_id');

            $query = DB::table('attendance as a')
                ->join('students as s', 'a.student_id', '=', 's.student_id')
                ->where('a.branch_id', $branchId)
                ->whereBetween('a.date', [$startDate, $endDate])
                ->where('s.active', 1);

            if ($studentId) {
                $query->where('a.student_id', $studentId);
            }

            $attendance = $query->select(
                'a.*',
                DB::raw('CONCAT(s.firstname, " ", s.lastname) as name'),
                DB::raw('(SELECT name FROM branch WHERE branch_id = a.branch_id) as branch_name')
            )
                ->orderBy('a.date', 'desc')
                ->get();

            return ApiResponseHelper::success($attendance, 'Attendance log retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * View attendance with filters
     * GET /api/attendance/view?branch_id=1&start_date=2024-01&end_date=2024-12&param=true
     */
    public function viewAttendance(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
                'start_date' => 'required|string', // Format: YYYY-MM
                'end_date' => 'required|string', // Format: YYYY-MM
                'param' => 'nullable|string',
            ]);

            $branchId = $request->input('branch_id');
            $startDate = $request->input('start_date') . '-01';
            $endDate = $request->input('end_date') . '-31';
            $param = $request->input('param');

            $query = DB::table('attendance as a')
                ->join('students as s', 'a.student_id', '=', 's.student_id')
                ->where('a.branch_id', $branchId)
                ->whereBetween('a.date', [$startDate, $endDate])
                ->where('s.active', 1);

            if ($param === 'true') {
                // Additional filtering if needed
            }

            $attendance = $query->select(
                'a.*',
                DB::raw('CONCAT(s.firstname, " ", s.lastname) as name'),
                DB::raw('(SELECT name FROM branch WHERE branch_id = a.branch_id) as branch_name'),
                DB::raw('(SELECT CONCAT(firstname, " ", lastname) FROM users WHERE user_id = a.user_id) as ins_name')
            )
                ->orderBy('a.date', 'desc')
                ->get();

            return ApiResponseHelper::success($attendance, 'Attendance view retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get students for exam attendance
     * GET /api/exam-attendance/get-students?branchId=1
     */
    public function getExamAttendanceStudents(Request $request)
    {
        try {
            $request->validate([
                'branchId' => 'required|integer|exists:branch,branch_id',
            ]);

            $branchId = $request->input('branchId');

            // This endpoint seems to need exam_id, but the original POST had only branchId
            // We'll return students by branch for now
            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.branch_id', $branchId)
                ->where('s.active', 1)
                ->select('s.*', 'br.name as branch_name')
                ->get();

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Insert/Update exam attendance (POST)
     */
    public function insertExamAttendance(Request $request)
    {
        try {
            $request->validate([
                'exam_id' => 'required|integer|exists:exam,exam_id',
                'attendance' => 'required|array',
                'attendance.*.exam_attendance_id' => 'required|integer|exists:exam_attendance,exam_attendance_id',
                'attendance.*.attend' => 'required|string|in:P,A,L',
                'attendance.*.certificate_no' => 'nullable|string',
            ]);

            $attendanceData = $request->input('attendance');

            DB::beginTransaction();

            foreach ($attendanceData as $item) {
                DB::table('exam_attendance')
                    ->where('exam_attendance_id', $item['exam_attendance_id'])
                    ->update([
                        'attend' => $item['attend'],
                        'certificate_no' => $item['certificate_no'] ?? null,
                    ]);
            }

            DB::commit();

            return ApiResponseHelper::success(null, 'Exam attendance saved successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get students for event attendance
     * GET /api/event-attendance/get-students?branchId=1&date=2024-01-15
     */
    public function getEventAttendanceStudents(Request $request)
    {
        try {
            $request->validate([
                'branchId' => 'required|integer|exists:branch,branch_id',
                'date' => 'required|date',
            ]);

            $branchId = $request->input('branchId');
            $date = $request->input('date');

            // Get students who have event fees for events on this date
            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('event_fees as ef', 's.student_id', '=', 'ef.student_id')
                ->join('event as e', 'ef.event_id', '=', 'e.event_id')
                ->where('s.branch_id', $branchId)
                ->where('s.active', 1)
                ->where('ef.status', 1)
                ->whereDate('e.date', $date)
                ->select('s.*', 'br.name as branch_name')
                ->distinct()
                ->get();

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Insert/Update event attendance (POST)
     */
    public function insertEventAttendance(Request $request)
    {
        try {
            $request->validate([
                'event_id' => 'required|integer|exists:event,event_id',
                'attendance' => 'required|array',
                'attendance.*.event_attendance_id' => 'required|integer|exists:event_attendance,event_attendance_id',
                'attendance.*.attend' => 'required|string|in:P,A,L',
                'attendance.*.result' => 'nullable|string',
                'attendance.*.medal' => 'nullable|string',
            ]);

            $attendanceData = $request->input('attendance');

            DB::beginTransaction();

            foreach ($attendanceData as $item) {
                DB::table('event_attendance')
                    ->where('event_attendance_id', $item['event_attendance_id'])
                    ->update([
                        'attend' => $item['attend'],
                        'result' => $item['result'] ?? null,
                        'medal' => $item['medal'] ?? null,
                    ]);
            }

            DB::commit();

            return ApiResponseHelper::success(null, 'Event attendance saved successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
}
