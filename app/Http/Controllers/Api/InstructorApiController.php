<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class InstructorApiController extends Controller
{
    /**
     * Get branch days
     * GET /api/instructor/branches/{id}/days
     */
    public function getBranchDays($id)
    {
        try {
            if (!is_numeric($id)) {
                return ApiResponseHelper::error('Invalid ID!', 422);
            }

            $branch = DB::table('branch')
                ->where('branch_id', $id)
                ->first(['days']);

            if (!$branch) {
                return ApiResponseHelper::error('Invalid Branch ID!', 422);
            }

            return ApiResponseHelper::success([
                'days' => $branch->days,
            ], 'Branch days retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Search students by name or GR number
     * GET /api/instructor/students/search?name=john
     */
    public function searchStudents(Request $request)
    {
        try {
            $name = $request->query('name', '');
            $name = trim($name);

            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.active', 1)
                ->where(function ($query) use ($name) {
                    $query->where('s.student_id', 'like', $name . '%')
                        ->orWhere(DB::raw("CONCAT(s.firstname, ' ', s.lastname)"), 'like', '%' . $name . '%');
                })
                ->select(
                    's.*',
                    DB::raw("CONCAT(s.firstname, ' ', s.lastname) as name"),
                    'br.name as branch_name'
                )
                ->limit(50)
                ->get();

            if ($students->isEmpty()) {
                return ApiResponseHelper::success([
                    'data' => [],
                ], 'No Student found!');
            }

            return ApiResponseHelper::success([
                'data' => $students,
            ], 'Students retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get attendance count for additional attendance
     * POST /api/instructor/attendance/count
     */
    public function getAttendanceCount(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|integer',
                'date' => 'required|date',
                'branch_id' => 'required|integer',
            ]);

            $studentId = $request->input('student_id');
            $date = date('Y-m-d', strtotime($request->input('date')));
            $branchId = $request->input('branch_id');

            $attendance = DB::table('attendance')
                ->where('date', $date)
                ->where('student_id', $studentId)
                ->get();

            $presentCount = 0;
            $eventCount = 0;

            foreach ($attendance as $record) {
                if ($record->attend == 'P') {
                    $presentCount++;
                } elseif ($record->attend == 'E') {
                    $eventCount++;
                }
            }

            return ApiResponseHelper::success([
                'present_count' => $presentCount,
                'event_count' => $eventCount,
                'done' => 1,
            ], 'Attendance count retrieved');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Insert fastrack attendance
     * POST /api/instructor/fastrack/attendance
     */
    public function insertFastrackAttendance(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|integer',
                'hours' => 'required|numeric',
                'branch_id' => 'required|integer',
            ]);

            $user = Auth::user();
            $studentId = $request->input('student_id');
            $hours = $request->input('hours');
            $branchId = $request->input('branch_id');
            $date = date('Y-m-d');

            // Insert fastrack attendance
            DB::table('fastrack_attendance')->insert([
                'student_id' => $studentId,
                'hours' => $hours,
                'date' => $date,
                'branch_id' => $branchId,
                'user_id' => $user->user_id,
            ]);

            return ApiResponseHelper::success([
                'saved' => 1,
            ], 'Attendance Submitted.');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get events for attendance
     * GET /api/instructor/events/for-attendance
     */
    public function getEventsForAttendance()
    {
        try {
            $events = DB::table('event')
                ->orderBy('from_date', 'desc')
                ->limit(5)
                ->get();

            if ($events->isEmpty()) {
                return ApiResponseHelper::error('No event Found!', 422);
            }

            return ApiResponseHelper::success([
                'data' => $events,
            ], 'Events retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get students enrolled for an event
     * GET /api/instructor/events/{id}/students
     */
    public function getStudentsForEvent($id)
    {
        try {
            if (!is_numeric($id)) {
                return ApiResponseHelper::error('Invalid ID!', 422);
            }

            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('event_fees as ef', 'ef.student_id', '=', 's.student_id')
                ->where('s.active', 1)
                ->where('ef.status', 1)
                ->where('ef.event_id', $id)
                ->select('s.*', 'br.name as branch_name')
                ->get();

            if ($students->isEmpty()) {
                return ApiResponseHelper::success([
                    'data' => [],
                ], 'No Student found in Event!');
            }

            return ApiResponseHelper::success([
                'data' => $students,
            ], 'Students retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Insert event attendance
     * POST /api/instructor/events/attendance
     */
    public function insertEventAttendance(Request $request)
    {
        try {
            $request->validate([
                'event_id' => 'required|integer',
                'attendanceArray' => 'required|string',
            ]);

            $user = Auth::user();
            $eventId = $request->input('event_id');
            $attenArray = $request->input('attendanceArray');

            // Check if attendance already exists
            $existing = DB::table('event_attendance')
                ->where('event_id', $eventId)
                ->exists();

            if ($existing) {
                return ApiResponseHelper::success([
                    'saved' => 0,
                ], 'Attendance already exists!');
            }

            $decodedArray = json_decode($attenArray, true);
            $presentArr = [];
            $absentArr = [];
            $leaveArr = [];

            foreach ($decodedArray as $value) {
                if (isset($value['present_student_id'])) {
                    $presentArr[] = $value['present_student_id'];
                }
                if (isset($value['absent_student_id'])) {
                    $absentArr[] = $value['absent_student_id'];
                }
                if (isset($value['leave_student_id'])) {
                    $leaveArr[] = $value['leave_student_id'];
                }
            }

            // Insert present students
            foreach ($presentArr as $studentId) {
                DB::table('event_attendance')->insert([
                    'event_id' => $eventId,
                    'student_id' => $studentId,
                    'attend' => 'P',
                    'user_id' => $user->user_id,
                ]);
            }

            // Insert absent students
            foreach ($absentArr as $studentId) {
                DB::table('event_attendance')->insert([
                    'event_id' => $eventId,
                    'student_id' => $studentId,
                    'attend' => 'A',
                    'user_id' => $user->user_id,
                ]);
            }

            // Insert leave students
            foreach ($leaveArr as $studentId) {
                DB::table('event_attendance')->insert([
                    'event_id' => $eventId,
                    'student_id' => $studentId,
                    'attend' => 'F',
                    'user_id' => $user->user_id,
                ]);
            }

            return ApiResponseHelper::success([
                'saved' => 1,
            ], 'Attendance Submitted.');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get exams for attendance (today's exams)
     * GET /api/instructor/exams/for-attendance
     */
    public function getExamsForAttendance()
    {
        try {
            $exams = DB::table('exam')
                ->whereDate('date', now()->format('Y-m-d'))
                ->orderBy('date', 'asc')
                ->get();

            if ($exams->isEmpty()) {
                return ApiResponseHelper::error('No Exam Found!', 422);
            }

            return ApiResponseHelper::success([
                'data' => $exams,
            ], 'Exams retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get students enrolled for an exam
     * GET /api/instructor/exams/{id}/students
     */
    public function getStudentsForExam($id)
    {
        try {
            if (!is_numeric($id)) {
                return ApiResponseHelper::error('Invalid ID!', 422);
            }

            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('exam_fees as ef', 'ef.student_id', '=', 's.student_id')
                ->where('s.active', 1)
                ->where('ef.status', 1)
                ->where('ef.exam_id', $id)
                ->select('s.*', 'br.name as branch_name')
                ->get();

            if ($students->isEmpty()) {
                return ApiResponseHelper::success([
                    'data' => [],
                ], 'No Student found in Exam!');
            }

            return ApiResponseHelper::success([
                'data' => $students,
            ], 'Students retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Insert exam attendance with certificate generation
     * POST /api/instructor/exams/attendance
     */
    public function insertExamAttendance(Request $request)
    {
        try {
            $request->validate([
                'exam_id' => 'required|integer',
                'attendanceArray' => 'required|string',
            ]);

            $user = Auth::user();
            $examId = $request->input('exam_id');
            $attenArray = $request->input('attendanceArray');

            // Check if attendance already exists
            $existing = DB::table('exam_attendance')
                ->where('exam_id', $examId)
                ->exists();

            if ($existing) {
                return ApiResponseHelper::success([
                    'saved' => 0,
                ], 'Attendance already exists!');
            }

            $decodedArray = json_decode($attenArray, true);
            $presentArr = [];
            $absentArr = [];
            $leaveArr = [];

            foreach ($decodedArray as $value) {
                if (isset($value['present_student_id'])) {
                    $presentArr[] = $value['present_student_id'];
                }
                if (isset($value['absent_student_id'])) {
                    $absentArr[] = $value['absent_student_id'];
                }
                if (isset($value['leave_student_id'])) {
                    $leaveArr[] = $value['leave_student_id'];
                }
            }

            // Insert present students with certificate
            foreach ($presentArr as $studentId) {
                $examData = DB::table('belt as b')
                    ->join('exam as e', function ($join) use ($examId) {
                        $join->where('e.exam_id', '=', $examId);
                    })
                    ->join('exam_fees as ef', function ($join) use ($examId, $studentId) {
                        $join->where('ef.exam_id', '=', $examId)
                            ->where('ef.student_id', '=', $studentId)
                            ->where('ef.status', '=', 1);
                    })
                    ->where('ef.exam_belt_id', '=', DB::raw('b.belt_id'))
                    ->select('b.code', 'e.date', 'ef.exam_belt_id')
                    ->first();

                $certificateNo = '';
                if ($examData) {
                    $date = str_replace('-', '', $examData->date);
                    $certificateNo = $date . $examData->code . $studentId . $examData->exam_belt_id;

                    // Update student belt
                    DB::table('students')
                        ->where('student_id', $studentId)
                        ->update(['belt_id' => $examData->exam_belt_id]);
                }

                DB::table('exam_attendance')->insert([
                    'exam_id' => $examId,
                    'student_id' => $studentId,
                    'attend' => 'P',
                    'user_id' => $user->user_id,
                    'certificate_no' => $certificateNo,
                ]);
            }

            // Insert absent students
            foreach ($absentArr as $studentId) {
                DB::table('exam_attendance')->insert([
                    'exam_id' => $examId,
                    'student_id' => $studentId,
                    'attend' => 'A',
                    'user_id' => $user->user_id,
                    'certificate_no' => '',
                ]);
            }

            // Insert leave students
            foreach ($leaveArr as $studentId) {
                DB::table('exam_attendance')->insert([
                    'exam_id' => $examId,
                    'student_id' => $studentId,
                    'attend' => 'F',
                    'user_id' => $user->user_id,
                    'certificate_no' => '',
                ]);
            }

            return ApiResponseHelper::success([
                'saved' => 1,
            ], 'Attendance Submitted.');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get exam details with eligibility info
     * GET /api/instructor/exams/{id}
     */
    public function getExamDetails(Request $request, $studentId)
    {
        try {
            if (!is_numeric($studentId)) {
                return ApiResponseHelper::error('Invalid ID!', 422);
            }

            $paid = false;
            $error = false;
            $eligibleFee = false;
            $eligibleAtten = false;
            $dueDateGone = false;

            // Check for special case exam first
            $exam = DB::table('exam as e')
                ->join('special_case_exam as s', 'e.exam_id', '=', 's.exam_id')
                ->where('e.date', '>=', now()->format('Y-m-d'))
                ->where('e.isPublished', 1)
                ->where('s.student_id', $studentId)
                ->where('s.eligible', 1)
                ->orderBy('date', 'desc')
                ->first();

            if (!$exam) {
                return ApiResponseHelper::error('No Exam Found!', 422);
            }

            // Check if already paid
            $feesPaid = DB::table('exam_fees')
                ->where('exam_id', $exam->exam_id)
                ->where('student_id', $studentId)
                ->where('status', 1)
                ->exists();

            if ($feesPaid) {
                $exam->paid = true;
                return ApiResponseHelper::success([
                    'error' => false,
                    'data' => $exam,
                ], 'Exam details retrieved');
            }

            // Check attendance eligibility
            $attendanceCount = DB::table('attendance')
                ->where('attend', 'P')
                ->where('student_id', $studentId)
                ->whereBetween('date', [$exam->from_criteria, $exam->to_criteria])
                ->count();

            $eligibleAtten = $attendanceCount >= $exam->sessions_count;

            // Check special case if not eligible by attendance
            if (!$eligibleAtten) {
                $specialCase = DB::table('special_case_exam')
                    ->where('student_id', $studentId)
                    ->where('exam_id', $exam->exam_id)
                    ->where('eligible', 1)
                    ->exists();

                if ($specialCase) {
                    $eligibleAtten = true;
                }
            }

            // Calculate months in criteria period
            $diffInMonths = DB::selectOne("
                SELECT TIMESTAMPDIFF(month, ?, ?) + 1 AS DateDiff
            ", [$exam->from_criteria, $exam->to_criteria])->DateDiff ?? 0;

            // Check fees eligibility
            $feeCount = DB::table('fees')
                ->where('student_id', $studentId)
                ->whereRaw("CAST(CONCAT(year,'-', months,'-01') as date) >= ?", [$exam->from_criteria])
                ->whereRaw("CAST(CONCAT(year,'-', months,'-01') as date) <= ?", [$exam->to_criteria])
                ->count();

            $eligibleFee = $feeCount >= $diffInMonths;

            // Check due date
            $dueDateGone = now()->gt($exam->fess_due_date);

            // Get next belt fee
            $nextBelt = DB::table('belt')
                ->whereRaw('belt_id = ((SELECT belt_id FROM students WHERE student_id = ?) + 1)', [$studentId])
                ->first(['belt_id', 'exam_fees']);

            $examData = (array) $exam;
            $examData['paid'] = $paid;
            $examData['eligibleAttendance'] = $eligibleAtten;
            $examData['eligibleFee'] = $eligibleFee;
            $examData['dueDateGone'] = $dueDateGone;
            $examData['fees'] = $nextBelt->exam_fees ?? '0';
            $examData['belt_id'] = $nextBelt->belt_id ?? '0';

            return ApiResponseHelper::success([
                'error' => $error,
                'data' => [$examData],
            ], 'Exam details retrieved');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }
}
