<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StudentService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class StudentApiController extends Controller
{
    protected $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    /**
     * Get students by branch
     * GET /api/students/get-by-branch?branch_id=1&belt_id=2&start_date=2024-01-01&end_date=2024-12-31
     */
    public function getStudentsByBranch(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
                'belt_id' => 'nullable|integer|exists:belt,belt_id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $filters = [
                'branch_id' => $request->input('branch_id'),
            ];

            if ($request->has('belt_id')) {
                $filters['belt_id'] = $request->input('belt_id');
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $filters['start_date'] = $request->input('start_date');
                $filters['end_date'] = $request->input('end_date');
            }

            $students = $this->studentService->getAllStudents($filters);

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Search students by GR number or name
     * GET /api/students/search?grno=101
     */
    public function searchStudents(Request $request)
    {
        try {
            $request->validate([
                'grno' => 'required|string',
            ]);

            $grno = $request->input('grno');

            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('belt as b', 's.belt_id', '=', 'b.belt_id')
                ->where('s.student_id', 'like', $grno . '%')
                ->orWhere(DB::raw('CONCAT(s.firstname, " ", s.lastname)'), 'like', '%' . $grno . '%')
                ->select(
                    's.*',
                    'br.name as branch_name',
                    'b.name as belt_name'
                )
                ->get();

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get students for additional attendance
     * GET /api/students/get-for-additional?grno=101
     */
    public function getStudentsForAdditional(Request $request)
    {
        try {
            $request->validate([
                'grno' => 'required|string',
            ]);

            $grno = $request->input('grno');

            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.student_id', 'like', $grno . '%')
                ->orWhere(DB::raw('CONCAT(s.firstname, " ", s.lastname)'), 'like', '%' . $grno . '%')
                ->where('s.active', 1)
                ->select(
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
     * Get students for fastrack
     * GET /api/students/get-for-fastrack?grno=101
     */
    public function getStudentsForFastrack(Request $request)
    {
        try {
            $request->validate([
                'grno' => 'required|string',
            ]);

            $grno = $request->input('grno');

            $students = DB::table('students as s')
                ->join('fastrack as f', 's.student_id', '=', 'f.student_id')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.student_id', 'like', $grno . '%')
                ->orWhere(DB::raw('CONCAT(s.firstname, " ", s.lastname)'), 'like', '%' . $grno . '%')
                ->where('s.active', 1)
                ->select(
                    's.*',
                    'br.name as branch_name'
                )
                ->get();

            return ApiResponseHelper::success($students, 'Fastrack students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Deactive report
     * GET /api/students/deactive-report?branch_id=1&start_date=2024-01-01&end_date=2024-12-31
     */
    public function getDeactiveReport(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $branchId = $request->input('branch_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.branch_id', $branchId)
                ->where('s.active', 0)
                ->whereBetween('s.doj', [$startDate, $endDate])
                ->select(
                    's.*',
                    'br.name as branch_name'
                )
                ->get();

            return ApiResponseHelper::success($students, 'Deactive students report retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * View foundation students
     * GET /api/students/view-foundation?branch_id=1
     */
    public function getFoundationStudents(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
            ]);

            $branchId = $request->input('branch_id');

            // Foundation students are typically those with belt_id = 1 (white belt)
            $students = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('belt as b', 's.belt_id', '=', 'b.belt_id')
                ->where('s.branch_id', $branchId)
                ->where('s.belt_id', 1) // Assuming belt_id 1 is foundation/white belt
                ->where('s.active', 1)
                ->select(
                    's.*',
                    'br.name as branch_name',
                    'b.name as belt_name'
                )
                ->get();

            return ApiResponseHelper::success($students, 'Foundation students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Set status (activate/deactivate)
     * POST /api/students/set-status
     */
    public function setStatus(Request $request)
    {
        try {
            $request->validate([
                'stuId' => 'required|integer|exists:students,student_id',
                'from' => 'required|integer|in:0,1', // 0 = deactivate, 1 = activate
            ]);

            $studentId = $request->input('stuId');
            $from = $request->input('from');

            if ($from == 1) {
                $this->studentService->activateStudent($studentId);
                $message = 'Student activated successfully';
            } else {
                $this->studentService->deactivateStudent($studentId);
                $message = 'Student deactivated successfully';
            }

            return ApiResponseHelper::success(null, $message);
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
}
