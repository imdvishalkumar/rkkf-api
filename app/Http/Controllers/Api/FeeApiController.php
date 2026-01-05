<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FeeService;
use App\Services\StudentService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class FeeApiController extends Controller
{
    protected $feeService;
    protected $studentService;

    public function __construct(
        FeeService $feeService,
        StudentService $studentService
    ) {
        $this->feeService = $feeService;
        $this->studentService = $studentService;
    }

    /**
     * Get student info by GR number
     * GET /api/fees/get-student-info?grno=101
     */
    public function getStudentInfo(Request $request)
    {
        try {
            $request->validate([
                'grno' => 'required|string',
            ]);

            $grno = $request->input('grno');

            $student = DB::table('students as s')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->join('belt as b', 's.belt_id', '=', 'b.belt_id')
                ->where('s.student_id', 'like', $grno . '%')
                ->orWhere(DB::raw('CONCAT(s.firstname, " ", s.lastname)'), 'like', '%' . $grno . '%')
                ->select(
                    's.*',
                    'br.name as branch_name',
                    'b.name as belt_name'
                )
                ->first();

            if (!$student) {
                return ApiResponseHelper::notFound('Student not found');
            }

            // Get fees for this student
            $fees = DB::table('fees')
                ->where('student_id', $student->student_id)
                ->orderBy('year', 'desc')
                ->orderBy('months', 'desc')
                ->get();

            $student->fees = $fees;

            return ApiResponseHelper::success($student, 'Student info retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get fees list with filters
     * GET /api/fees/get-fees?branch_id=1&start_date=2024-01&end_date=2024-12&param=true
     */
    public function getFees(Request $request)
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

            $query = DB::table('fees as f')
                ->join('students as s', 'f.student_id', '=', 's.student_id')
                ->join('branch as br', 's.branch_id', '=', 'br.branch_id')
                ->where('s.branch_id', $branchId)
                ->whereBetween('f.date', [$startDate, $endDate])
                ->where('s.active', 1);

            if ($param === 'true') {
                // Additional filtering if needed
            }

            $fees = $query->select(
                'f.*',
                DB::raw('CONCAT(s.firstname, " ", s.lastname) as student_name'),
                's.student_id as grno',
                'br.name as branch_name'
            )
            ->orderBy('f.date', 'desc')
            ->get();

            return ApiResponseHelper::success($fees, 'Fees retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Delete fee (POST - keeping for compatibility)
     */
    public function deleteFee(Request $request)
    {
        try {
            $request->validate([
                'fee_id' => 'required|integer|exists:fees,fee_id',
            ]);

            $feeId = $request->input('fee_id');
            $deleted = $this->feeService->deleteFee($feeId);

            if ($deleted) {
                return ApiResponseHelper::success(null, 'Fee deleted successfully');
            }

            return ApiResponseHelper::error('Failed to delete fee', 500);
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get student for entering fees
     * GET /api/fees/enter/get-student?grno=101
     */
    public function getStudentForEnterFees(Request $request)
    {
        try {
            $request->validate([
                'grno' => 'required|string',
            ]);

            $grno = $request->input('grno');

            $students = DB::table('students')
                ->where('student_id', 'like', $grno . '%')
                ->orWhere(DB::raw('CONCAT(firstname, " ", lastname)'), 'like', '%' . $grno . '%')
                ->select(
                    'student_id',
                    DB::raw('CONCAT(firstname, " ", lastname) as name')
                )
                ->get();

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get student for entering old fees
     * GET /api/fees/enter-old/get-student?grno=101
     */
    public function getStudentForOldFees(Request $request)
    {
        try {
            $request->validate([
                'grno' => 'required|string',
            ]);

            $grno = $request->input('grno');

            $students = DB::table('students')
                ->where('student_id', 'like', $grno . '%')
                ->orWhere(DB::raw('CONCAT(firstname, " ", lastname)'), 'like', '%' . $grno . '%')
                ->select(
                    'student_id',
                    DB::raw('CONCAT(firstname, " ", lastname) as name')
                )
                ->get();

            // Get last fee for each student
            foreach ($students as $student) {
                $lastFee = DB::table('fees')
                    ->where('student_id', $student->student_id)
                    ->orderBy('year', 'desc')
                    ->orderBy('months', 'desc')
                    ->first();
                
                $student->last_fee = $lastFee;
            }

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Enter exam fees (POST)
     */
    public function enterExamFees(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|integer|exists:students,student_id',
                'exam_id' => 'required|integer|exists:exam,exam_id',
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
                'mode' => 'required|string|in:app,cash',
                'rp_order_id' => 'nullable|string',
                'exam_belt_id' => 'nullable|integer|exists:belt,belt_id',
            ]);

            $data = $request->only([
                'student_id',
                'exam_id',
                'amount',
                'date',
                'mode',
                'rp_order_id',
                'exam_belt_id',
            ]);

            DB::table('exam_fees')->insert($data);

            return ApiResponseHelper::success(null, 'Exam fee entered successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * View combined fees
     * GET /api/fees/combined?branch_id=1&start_date=2024-01&end_date=2024-12
     */
    public function getCombinedFees(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
                'start_date' => 'required|string', // Format: YYYY-MM
                'end_date' => 'required|string', // Format: YYYY-MM
            ]);

            $branchId = $request->input('branch_id');
            $startDate = $request->input('start_date') . '-01';
            $endDate = $request->input('end_date') . '-31';

            $fees = DB::table('fees as f')
                ->join('students as s', 'f.student_id', '=', 's.student_id')
                ->where('s.branch_id', $branchId)
                ->whereBetween('f.date', [$startDate, $endDate])
                ->where('s.active', 1)
                ->select(
                    's.student_id',
                    DB::raw('CONCAT(s.firstname, " ", s.lastname) as student_name'),
                    DB::raw('SUM(f.amount) as total_amount'),
                    DB::raw('COUNT(f.fee_id) as fee_count')
                )
                ->groupBy('s.student_id', 's.firstname', 's.lastname')
                ->get();

            return ApiResponseHelper::success($fees, 'Combined fees retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * View fees without admission
     * GET /api/fees/view-without-admission?branch_id=1&start_date=2024-01&end_date=2024-12
     */
    public function getFeesWithoutAdmission(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|integer|exists:branch,branch_id',
                'start_date' => 'required|string', // Format: YYYY-MM
                'end_date' => 'required|string', // Format: YYYY-MM
            ]);

            $branchId = $request->input('branch_id');
            $startDate = $request->input('start_date') . '-01';
            $endDate = $request->input('end_date') . '-31';

            $fees = DB::table('fees as f')
                ->join('students as s', 'f.student_id', '=', 's.student_id')
                ->where('s.branch_id', $branchId)
                ->whereBetween('f.date', [$startDate, $endDate])
                ->where('s.active', 1)
                ->where('f.additional', 0) // Exclude admission fees
                ->select(
                    'f.*',
                    DB::raw('CONCAT(s.firstname, " ", s.lastname) as student_name'),
                    's.student_id as grno'
                )
                ->orderBy('f.date', 'desc')
                ->get();

            return ApiResponseHelper::success($fees, 'Fees without admission retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get student for disable fees
     * GET /api/fees/disable/get-student?grno=101&disable_student_id=101
     */
    public function getStudentForDisable(Request $request)
    {
        try {
            $request->validate([
                'grno' => 'required|string',
                'disable_student_id' => 'nullable|integer|exists:students,student_id',
            ]);

            $grno = $request->input('grno');
            $studentId = $request->input('disable_student_id');

            if ($studentId) {
                // Get fees for specific student
                $fees = DB::table('fees')
                    ->where('student_id', $studentId)
                    ->where('disabled', 0)
                    ->orderBy('year', 'desc')
                    ->orderBy('months', 'desc')
                    ->get();

                return ApiResponseHelper::success($fees, 'Student fees retrieved successfully');
            }

            // Search students by GR number
            $students = DB::table('students')
                ->where('student_id', 'like', $grno . '%')
                ->orWhere(DB::raw('CONCAT(firstname, " ", lastname)'), 'like', '%' . $grno . '%')
                ->select(
                    'student_id',
                    DB::raw('CONCAT(firstname, " ", lastname) as name')
                )
                ->get();

            return ApiResponseHelper::success($students, 'Students retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Fix payment entry (POST)
     */
    public function fixPaymentEntry(Request $request)
    {
        try {
            $request->validate([
                'fee_id' => 'required|integer|exists:fees,fee_id',
                'amount' => 'required|numeric|min:0',
                'date' => 'required|date',
            ]);

            $feeId = $request->input('fee_id');
            $amount = $request->input('amount');
            $date = $request->input('date');

            DB::table('fees')
                ->where('fee_id', $feeId)
                ->update([
                    'amount' => $amount,
                    'date' => $date,
                ]);

            return ApiResponseHelper::success(null, 'Payment entry fixed successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Payment report
     * GET /api/fees/payment-report?type=fees&mode=app&start_date=2024-01-01&end_date=2024-12-31
     */
    public function getPaymentReport(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|string|in:fees,exam_fees,event_fees',
                'mode' => 'required|string|in:app,cash',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $type = $request->input('type');
            $mode = $request->input('mode');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $table = $type === 'fees' ? 'fees' : ($type === 'exam_fees' ? 'exam_fees' : 'event_fees');

            $payments = DB::table($table)
                ->where('mode', $mode)
                ->whereBetween('date', [$startDate, $endDate])
                ->select(
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw('COUNT(*) as total_count'),
                    'date'
                )
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get();

            return ApiResponseHelper::success($payments, 'Payment report retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Full report (combined fees and orders)
     * GET /api/fees/full-report?start_date=2024-01-01&end_date=2024-12-31&mode=app
     */
    public function getFullReport(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'mode' => 'nullable|string|in:app,cash',
            ]);

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $mode = $request->input('mode');

            $feesQuery = DB::table('fees')
                ->whereBetween('date', [$startDate, $endDate]);

            $ordersQuery = DB::table('orders')
                ->whereBetween('created_at', [$startDate, $endDate]);

            if ($mode) {
                $feesQuery->where('mode', $mode);
                $ordersQuery->where('mode', $mode);
            }

            $fees = $feesQuery->select(
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as total_count')
            )->first();

            $orders = $ordersQuery->select(
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('COUNT(*) as total_count')
            )->first();

            $report = [
                'fees' => $fees,
                'orders' => $orders,
                'grand_total' => ($fees->total_amount ?? 0) + ($orders->total_amount ?? 0),
            ];

            return ApiResponseHelper::success($report, 'Full report retrieved successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
}
