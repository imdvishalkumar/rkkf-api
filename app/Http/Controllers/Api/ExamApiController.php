<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExamService;
use App\Services\StudentService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ExamApiController extends Controller
{
    protected $examService;
    protected $studentService;

    public function __construct(
        ExamService $examService,
        StudentService $studentService
    ) {
        $this->examService = $examService;
        $this->studentService = $studentService;
    }

    /**
     * Get eligible students for exam
     * GET /api/exam/get-eligible-students?exam_id=1
     */
    public function getEligibleStudents(Request $request)
    {
        try {
            $request->validate([
                'exam_id' => 'required|integer|exists:exam,exam_id',
            ]);

            $examId = $request->input('exam_id');

            // Get students who have paid exam fees for this exam
            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('exam_fees as ef', 's.student_id', '=', 'ef.student_id')
                ->where('s.active', 1)
                ->where('ef.exam_id', $examId)
                ->where('ef.status', 1)
                ->select('s.*', 'br.name as branch_name')
                ->distinct()
                ->get();

            return ApiResponseHelper::success($students, 'Eligible students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Set exam eligibility (POST)
     */
    public function setEligibility(Request $request)
    {
        try {
            $request->validate([
                'exam_id' => 'required|integer|exists:exam,exam_id',
                'student_id' => 'required|integer|exists:students,student_id',
                'eligible' => 'required|boolean',
            ]);

            $examId = $request->input('exam_id');
            $studentId = $request->input('student_id');
            $eligible = $request->input('eligible');

            // Update exam fees status
            DB::table('exam_fees')
                ->where('exam_id', $examId)
                ->where('student_id', $studentId)
                ->update(['status' => $eligible ? 1 : 0]);

            return ApiResponseHelper::success(null, 'Exam eligibility updated successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get exam applied students
     * GET /api/exam/get-applied?branch_id=1&param=true
     */
    public function getExamApplied(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
                'param' => 'nullable|string',
            ]);

            $branchId = $request->input('branch_id');
            $param = $request->input('param');

            $query = DB::table('exam_fees as ef')
                ->join('students as s', 'ef.student_id', '=', 's.student_id')
                ->join('exam as e', 'ef.exam_id', '=', 'e.exam_id')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.branch_id', $branchId)
                ->where('ef.status', 1);

            if ($param === 'true') {
                // Additional filtering if needed
            }

            $applied = $query->select(
                'ef.*',
                's.student_id as grno',
                DB::raw('CONCAT(s.firstname, " ", s.lastname) as student_name'),
                'e.name as exam_name',
                'br.name as branch_name'
            )
                ->orderBy('ef.date', 'desc')
                ->get();

            return ApiResponseHelper::success($applied, 'Exam applied students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Exam result report
     * GET /api/exam/result-report?exam_id=1&branch_id=1
     */
    public function getResultReport(Request $request)
    {
        try {
            $request->validate([
                'exam_id' => 'required|integer|exists:exam,exam_id',
                'branch_id' => 'required|integer|exists:branch,branch_id',
            ]);

            $examId = $request->input('exam_id');
            $branchId = $request->input('branch_id');

            $results = DB::table('exam_attendance as ea')
                ->join('students as s', 'ea.student_id', '=', 's.student_id')
                ->join('exam as e', 'ea.exam_id', '=', 'e.exam_id')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('ea.exam_id', $examId)
                ->where('s.branch_id', $branchId)
                ->where('s.active', 1)
                ->select(
                    'ea.*',
                    's.student_id as grno',
                    DB::raw('CONCAT(s.firstname, " ", s.lastname) as student_name'),
                    'e.name as exam_name',
                    'br.name as branch_name'
                )
                ->get();

            return ApiResponseHelper::success($results, 'Exam result report retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Special exam - set eligibility (POST)
     */
    public function setSpecialEligibility(Request $request)
    {
        try {
            $request->validate([
                'exam_id' => 'required|integer|exists:exam,exam_id',
                'student_id' => 'required|integer|exists:students,student_id',
                'eligible' => 'required|boolean',
            ]);

            $examId = $request->input('exam_id');
            $studentId = $request->input('student_id');
            $eligible = $request->input('eligible');

            // Update special exam eligibility (assuming there's a special_exam table or flag)
            DB::table('exam_fees')
                ->where('exam_id', $examId)
                ->where('student_id', $studentId)
                ->update([
                    'status' => $eligible ? 1 : 0,
                    'is_special' => 1
                ]);

            return ApiResponseHelper::success(null, 'Special exam eligibility updated successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get exam progress for authenticated student
     * GET /api/exams/progress
     * 
     * Returns count of exams applied and total exams available
     */
    public function getProgress(Request $request)
    {
        try {
            $user = $request->user();
            $student = \App\Models\Student::where('email', $user->email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Student profile not found', 404);
            }

            $studentId = $student->student_id;

            // Count exams applied (paid)
            $examsApplied = DB::table('exam_fees')
                ->where('student_id', $studentId)
                ->where('status', 1)
                ->count();

            // Count total published exams
            $totalExams = DB::table('exam')
                ->where('isPublished', 1)
                ->count();

            return ApiResponseHelper::success([
                'exams_applied' => $examsApplied,
                'total_exams' => $totalExams
            ], 'Exam progress retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get upcoming exams with eligibility info for authenticated student
     * GET /api/exams/list
     * 
     * Returns upcoming exams with eligibility percentage and status
     */
    public function getUpcomingExams(Request $request)
    {
        try {
            $user = $request->user();
            $student = \App\Models\Student::where('email', $user->email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Student profile not found', 404);
            }

            $studentId = $student->student_id;
            $today = date('Y-m-d');

            // Get upcoming published exams
            $exams = DB::table('exam as e')
                ->select([
                    'e.exam_id',
                    'e.name',
                    'e.date',
                    'e.location',
                    'e.fees as exam_fees',
                    'e.fess_due_date as fees_due_date',
                    'e.from_criteria',
                    'e.to_criteria',
                    'e.sessions_count'
                ])
                ->where('e.date', '>=', $today)
                ->where('e.isPublished', 1)
                ->orderBy('e.date', 'asc')
                ->get();

            // Process each exam for eligibility
            $exams = $exams->map(function ($exam) use ($studentId, $today) {
                // Check if already paid
                $paid = DB::table('exam_fees')
                    ->where('exam_id', $exam->exam_id)
                    ->where('student_id', $studentId)
                    ->where('status', 1)
                    ->exists();

                $exam->paid = $paid;

                if ($paid) {
                    $exam->status = 'Applied';
                    $exam->eligibility_percentage = 100;
                    $exam->eligibility_attendance = true;
                    $exam->eligibility_fees = true;
                    $exam->ineligibility_reason = null;
                    return $exam;
                }

                // Check attendance eligibility
                $attendanceCount = DB::table('attendance')
                    ->where('student_id', $studentId)
                    ->where('attend', 'P')
                    ->whereBetween('date', [$exam->from_criteria, $exam->to_criteria])
                    ->count();

                $eligibleAttendance = $attendanceCount >= $exam->sessions_count;

                // Calculate eligibility percentage based on attendance
                $eligibilityPercentage = $exam->sessions_count > 0
                    ? min(100, round(($attendanceCount / $exam->sessions_count) * 100))
                    : 0;

                // Check fees eligibility
                $fromDate = new \DateTime($exam->from_criteria);
                $toDate = new \DateTime($exam->to_criteria);
                $diffInMonths = ($toDate->format('Y') - $fromDate->format('Y')) * 12
                    + ($toDate->format('m') - $fromDate->format('m')) + 1;

                $feesPaidCount = DB::table('fees')
                    ->where('student_id', $studentId)
                    ->whereRaw("CAST(CONCAT(year, '-', months, '-01') AS DATE) >= ?", [$exam->from_criteria])
                    ->whereRaw("CAST(CONCAT(year, '-', months, '-01') AS DATE) <= ?", [$exam->to_criteria])
                    ->count();

                $eligibleFees = $feesPaidCount >= $diffInMonths;

                // Check special case
                if (!$eligibleAttendance) {
                    $specialCase = DB::table('special_case_exam')
                        ->where('student_id', $studentId)
                        ->where('exam_id', $exam->exam_id)
                        ->where('eligible', 1)
                        ->exists();

                    if ($specialCase) {
                        $eligibleAttendance = true;
                        $eligibilityPercentage = 100;
                    }
                }

                // Check if due date gone
                $dueDateGone = strtotime($today) > strtotime($exam->fees_due_date);

                $exam->eligibility_percentage = $eligibilityPercentage;
                $exam->eligibility_attendance = $eligibleAttendance;
                $exam->eligibility_fees = $eligibleFees;
                $exam->due_date_gone = $dueDateGone;

                // Determine status and reason
                if ($eligibleAttendance && $eligibleFees) {
                    $exam->status = 'Eligible';
                    $exam->ineligibility_reason = null;
                } else {
                    $exam->status = 'Not Eligible';
                    $reasons = [];
                    if (!$eligibleAttendance) {
                        $reasons[] = 'poor attendance';
                    }
                    if (!$eligibleFees) {
                        $reasons[] = 'pending fee payments';
                    }
                    $exam->ineligibility_reason = 'Not Eligible for Exam due to ' . implode(' and ', $reasons);
                }

                // Get exam fees for student's next belt
                $nextBelt = DB::table('belt')
                    ->where('belt_id', '=', DB::raw("(SELECT belt_id + 1 FROM students WHERE student_id = {$studentId})"))
                    ->first();

                if ($nextBelt) {
                    $exam->fees_amount = $nextBelt->exam_fees ?? $exam->exam_fees;
                    $exam->next_belt_id = $nextBelt->belt_id;
                    $exam->next_belt_name = $nextBelt->name ?? null;
                } else {
                    $exam->fees_amount = $exam->exam_fees;
                }

                return $exam;
            });

            return ApiResponseHelper::success($exams, 'Upcoming exams retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get overall exam results performance for authenticated student
     * GET /api/exams/results/overview
     * 
     * Returns passed/failed/total counts for performance chart
     */
    public function getResultsOverview(Request $request)
    {
        try {
            $user = $request->user();
            $student = \App\Models\Student::where('email', $user->email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Student profile not found', 404);
            }

            $studentId = $student->student_id;

            // Get all exam attendance records
            $results = DB::table('exam_attendance as ea')
                ->join('exam_fees as ef', function ($join) {
                    $join->on('ef.exam_id', '=', 'ea.exam_id')
                        ->on('ef.student_id', '=', 'ea.student_id');
                })
                ->where('ea.student_id', $studentId)
                ->where('ef.exam_belt_id', '>', 2)
                ->select('ea.attend')
                ->get();

            $totalExams = $results->count();
            $passed = $results->where('attend', 'P')->count();
            $failed = $results->where('attend', 'F')->count();
            $success = $results->where('attend', 'S')->count(); // Special mention/honors

            $performancePercentage = $totalExams > 0
                ? round(($passed / $totalExams) * 100)
                : 0;

            return ApiResponseHelper::success([
                'total_exams' => $totalExams,
                'passed' => $passed,
                'failed' => $failed,
                'success' => $success,
                'performance_percentage' => $performancePercentage
            ], 'Exam results overview retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get all exam results for authenticated student
     * GET /api/exams/results
     * 
     * Returns all exam results with pass/fail status, certificate info
     */
    public function getResults(Request $request)
    {
        try {
            $user = $request->user();
            $student = \App\Models\Student::where('email', $user->email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Student profile not found', 404);
            }

            $studentId = $student->student_id;

            // Get all exam results with belt info
            $results = DB::table('exam_attendance as ea')
                ->join('exam as e', 'ea.exam_id', '=', 'e.exam_id')
                ->join('exam_fees as ef', function ($join) {
                    $join->on('ef.exam_id', '=', 'ea.exam_id')
                        ->on('ef.student_id', '=', 'ea.student_id');
                })
                ->leftJoin('belt as from_belt', 'ef.exam_belt_id', '=', DB::raw('from_belt.belt_id + 1'))
                ->leftJoin('belt as to_belt', 'ef.exam_belt_id', '=', 'to_belt.belt_id')
                ->where('ea.student_id', $studentId)
                ->where('ef.exam_belt_id', '>', 2)
                ->select([
                    'ea.exam_attendance_id',
                    'ea.exam_id',
                    'e.name as exam_name',
                    'e.date as exam_date',
                    'ea.attend',
                    'ea.certificate_no',
                    'from_belt.name as from_belt',
                    'to_belt.name as to_belt'
                ])
                ->orderBy('e.date', 'desc')
                ->get();

            // Process results
            $results = $results->map(function ($result) {
                $result->status = $result->attend === 'P' ? 'Pass' : ($result->attend === 'F' ? 'Fail' : 'Special');

                // Add remarks based on status if not set
                // Add remarks based on status
                $result->remarks = $result->status === 'Pass'
                    ? 'Congratulations!'
                    : 'Better Luck Next Time!';

                // Add certificate download URL if passed
                if ($result->status === 'Pass' && $result->certificate_no) {
                    $result->download_certificate_url = "/api/certificates/{$result->certificate_no}";
                }

                return $result;
            });

            return ApiResponseHelper::success($results, 'Exam results retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get single exam result details for authenticated student
     * GET /api/exams/results/{id}
     * 
     * Returns detailed exam result info
     */
    public function getResultDetails(Request $request, $id)
    {
        try {
            $user = $request->user();
            $student = \App\Models\Student::where('email', $user->email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Student profile not found', 404);
            }

            $studentId = $student->student_id;

            // Get specific exam result
            $result = DB::table('exam_attendance as ea')
                ->join('exam as e', 'ea.exam_id', '=', 'e.exam_id')
                ->join('exam_fees as ef', function ($join) {
                    $join->on('ef.exam_id', '=', 'ea.exam_id')
                        ->on('ef.student_id', '=', 'ea.student_id');
                })
                ->leftJoin('belt as from_belt', 'ef.exam_belt_id', '=', DB::raw('from_belt.belt_id + 1'))
                ->leftJoin('belt as to_belt', 'ef.exam_belt_id', '=', 'to_belt.belt_id')
                ->where('ea.student_id', $studentId)
                ->where('ea.exam_attendance_id', $id)
                ->select([
                    'ea.exam_attendance_id',
                    'ea.exam_id',
                    'e.name as exam_name',
                    'e.date as exam_date',
                    'e.location',
                    'ea.attend',
                    'ea.certificate_no',
                    'from_belt.name as from_belt',
                    'to_belt.name as to_belt'
                ])
                ->first();

            if (!$result) {
                return ApiResponseHelper::error('Exam result not found', 404);
            }

            $result->status = $result->attend === 'P' ? 'Pass' : ($result->attend === 'F' ? 'Fail' : 'Special');

            $result->remarks = $result->status === 'Pass'
                ? 'Congratulations!'
                : 'Better Luck Next Time!';

            if ($result->status === 'Pass' && $result->certificate_no) {
                $result->download_certificate_url = "/api/certificates/{$result->certificate_no}";
            }

            return ApiResponseHelper::success($result, 'Exam result details retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
}
