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
}
