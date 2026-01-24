<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExamService;
use App\Services\StudentService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
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
     * Returns count of exams applied, total exams, and list of upcoming exams with eligibility
     */
    public function getProgress(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Entering getProgress');
            $user = $request->user();

            if (!$user) {
                return ApiResponseHelper::error('Unauthenticated', 401);
            }

            $student = \App\Models\Student::where('email', $user->email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Student profile not found', 404);
            }

            $studentId = $student->student_id;
            $today = date('Y-m-d');

            // Count exams applied (paid)
            $examsApplied = DB::table('exam_fees')
                ->where('student_id', $studentId)
                ->where('status', 1)
                ->count();

            // Count total published exams
            $totalExams = DB::table('exam')
                ->where('isPublished', 1)
                ->count();

            // --- Get Exam List Logic (merged from getUpcomingExams) ---
            $exams = DB::table('exam as e')
                ->select([
                    'e.exam_id',
                    'e.name as exam_name', // Consistency in naming
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
                    // Format date
                    $exam->formatted_date = date('F j, Y', strtotime($exam->date));
                    return $exam;
                }

                // Check attendance eligibility
                $attendanceCount = DB::table('attendance')
                    ->where('student_id', $studentId)
                    ->where('attend', 'P')
                    ->whereBetween('date', [$exam->from_criteria, $exam->to_criteria])
                    ->count();

                $eligibleAttendance = $attendanceCount >= $exam->sessions_count;

                // Calculate eligibility percentage
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

                // Check due date
                $dueDateGone = strtotime($today) > strtotime($exam->fees_due_date);

                $exam->eligibility_percentage = $eligibilityPercentage;
                $exam->eligibility_attendance = $eligibleAttendance;
                $exam->eligibility_fees = $eligibleFees;
                $exam->due_date_gone = $dueDateGone;
                $exam->formatted_date = date('F j, Y', strtotime($exam->date));

                if ($eligibleAttendance && $eligibleFees) {
                    $exam->status = 'Eligible'; // Could be 'Not Applied' but Eligible
                    $exam->ineligibility_reason = null;
                } else {
                    $exam->status = 'Not Eligible';
                    $reasons = [];
                    if (!$eligibleAttendance)
                        $reasons[] = 'poor attendance';
                    if (!$eligibleFees)
                        $reasons[] = 'pending fee payments';
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

            return ApiResponseHelper::success([
                'summary' => [
                    'exams_applied' => $examsApplied,
                    'total_exams' => $totalExams
                ],
                'exams' => $exams
            ], 'Exam progress retrieved successfully');

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
            \Illuminate\Support\Facades\Log::info('Entering getResultsOverview');
            $user = $request->user();

            if (!$user) {
                return ApiResponseHelper::error('Unauthenticated', 401);
            }

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
    /**
     * Get all exam results for authenticated student with performance stats
     * GET /api/exams/results
     * 
     * Returns dashboard with performance stats and list of all exam results
     */
    public function getResults(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Entering getResults');
            $user = $request->user();

            if (!$user) {
                return ApiResponseHelper::error('Unauthenticated', 401);
            }

            $student = \App\Models\Student::where('email', $user->email)->first();

            if (!$student) {
                return ApiResponseHelper::error('Student profile not found', 404);
            }

            $studentId = $student->student_id;

            // --- 1. Get Overall Performance Stats ---
            $allAssessments = DB::table('exam_attendance as ea')
                ->join('exam_fees as ef', function ($join) {
                    $join->on('ef.exam_id', '=', 'ea.exam_id')
                        ->on('ef.student_id', '=', 'ea.student_id');
                })
                ->where('ea.student_id', $studentId)
                ->where('ef.exam_belt_id', '>', 2)
                ->select('ea.attend')
                ->get();

            $totalExams = $allAssessments->count();
            $passed = $allAssessments->where('attend', 'P')->count();
            $failed = $allAssessments->where('attend', 'F')->count();

            $performancePercentage = $totalExams > 0
                ? round(($passed / $totalExams) * 100)
                : 0;

            $performanceStats = [
                'total' => $totalExams,
                'passed' => $passed,
                'failed' => $failed,
                'percentage' => $performancePercentage
            ];

            // --- 2. Get All Exam Results with Details (using DISTINCT to prevent duplicates) ---
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
                    'e.location',
                    'ea.attend',
                    'ea.certificate_no',
                    'from_belt.name as from_belt',
                    'to_belt.name as to_belt'
                ])
                ->distinct()
                ->orderBy('e.date', 'desc')
                ->get();

            // Process results
            $formattedResults = $results->map(function ($result) {
                $result->status = $result->attend === 'P' ? 'Pass' : ($result->attend === 'F' ? 'Fail' : 'Special');
                $result->is_passed = $result->status === 'Pass';

                $result->remarks = $result->status === 'Pass'
                    ? 'Congratulations!'
                    : 'Better Luck Next Time!';

                if ($result->status === 'Pass' && $result->certificate_no) {
                    // Generate signed URL valid for 24 hours for secure certificate download
                    $result->download_certificate_url = \Illuminate\Support\Facades\URL::signedRoute(
                        'api.exams.results.download',
                        ['certificate_no' => $result->certificate_no],
                        now()->addHours(24)
                    );
                } else {
                    $result->download_certificate_url = null;
                }

                $result->belt_transition = [
                    'from' => $result->from_belt ?? 'Unknown',
                    'to' => $result->to_belt ?? 'Unknown'
                ];

                // Cleanup raw fields if preferred, or keep them
                unset($result->from_belt);
                unset($result->to_belt);

                return $result;
            });

            return ApiResponseHelper::success([
                'performance_stats' => $performanceStats,
                'results' => $formattedResults
            ], 'Exam results retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Download exam result certificate as PDF
     * GET /api/exams/results/{certificate_no}/download
     * 
     * Generates and downloads a PDF certificate for the exam result
     * This is a public endpoint - no authentication required
     */
    public function downloadResultCertificate(Request $request, string $certificateNo)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Entering downloadResultCertificate', ['cert' => $certificateNo]);

            // Validate signed URL
            if (!$request->hasValidSignature()) {
                return ApiResponseHelper::error('Invalid or expired download link. Please request a new link from the results page.', 403);
            }

            // Get the exam result for this certificate (public access by certificate number)
            $result = DB::table('exam_attendance as ea')
                ->join('exam as e', 'ea.exam_id', '=', 'e.exam_id')
                ->join('exam_fees as ef', function ($join) {
                    $join->on('ef.exam_id', '=', 'ea.exam_id')
                        ->on('ef.student_id', '=', 'ea.student_id');
                })
                ->leftJoin('belt as from_belt', 'ef.exam_belt_id', '=', DB::raw('from_belt.belt_id + 1'))
                ->leftJoin('belt as to_belt', 'ef.exam_belt_id', '=', 'to_belt.belt_id')
                ->where('ea.certificate_no', $certificateNo)
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
                return ApiResponseHelper::error('Certificate not found or access denied', 404);
            }

            // Prepare data for PDF
            $status = $result->attend === 'P' ? 'Pass' : ($result->attend === 'F' ? 'Fail' : 'Special');
            $isPassed = $status === 'Pass';

            $pdfData = [
                'exam_name' => $result->exam_name,
                'exam_date' => $result->exam_date,
                'location' => $result->location ?? 'N/A',
                'attend' => $result->attend,
                'certificate_no' => $result->certificate_no,
                'status' => $status,
                'is_passed' => $isPassed,
                'remarks' => $isPassed ? 'Congratulations!' : 'Better Luck Next Time!',
                'belt_transition' => [
                    'from' => $result->from_belt ?? 'Unknown',
                    'to' => $result->to_belt ?? 'Unknown'
                ]
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdf.exam-result-certificate', $pdfData);

            // Set paper size and orientation
            $pdf->setPaper('A4', 'portrait');

            // Set options for better rendering
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

            // Generate filename
            $filename = "Exam_Result_{$certificateNo}.pdf";

            // Return download response
            return $pdf->download($filename);

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
}
