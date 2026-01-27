<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FeeService;
use App\Services\StudentService;
use App\Services\PaymentService;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class FeeApiController extends Controller
{
    protected $feeService;
    protected $studentService;
    protected $paymentService;

    public function __construct(
        FeeService $feeService,
        StudentService $studentService,
        PaymentService $paymentService
    ) {
        $this->feeService = $feeService;
        $this->studentService = $studentService;
        $this->paymentService = $paymentService;
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

    /**
     * Get due fees for authenticated student
     * GET /api/fees/due
     * 
     * Returns calculated due fees based on branch, belt, and fastrack status
     */
    public function getDueFees(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponseHelper::error('User not authenticated', 401);
            }

            // Get student through relationship (User->Student via email)
            $student = $user->student;

            if (!$student) {
                return ApiResponseHelper::error('No student profile linked to this user', 404);
            }

            $studentId = $student->student_id;
            $result = $this->feeService->calculateDueFees($studentId);

            if (!$result['success']) {
                return ApiResponseHelper::error($result['message'], 404);
            }

            return ApiResponseHelper::success([
                'last_paid' => $result['data'],
                'due' => $result['due'],
            ], 'Due fees calculated successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Initiate fee payment via Razorpay
     * POST /api/fees/payment/initiate
     * 
     * Request body:
     * - months: string (comma-separated month numbers, e.g., "1,2,3")
     * - year: int (year for the fee)
     * - amount: decimal (total amount to pay)
     * - coupon_id: int (optional, coupon ID if applicable)
     * 
     * Returns Razorpay order ID and key for client-side checkout
     */
    public function initiatePayment(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return ApiResponseHelper::error('User not authenticated', 401);
            }

            // Get student through relationship (User->Student via email)
            $student = $user->student;

            if (!$student) {
                return ApiResponseHelper::error('No student profile linked to this user', 404);
            }

            $request->validate([
                'months' => 'required|string',
                'year' => 'required|integer|min:2020|max:2100',
                'amount' => 'required|numeric|min:1',
                'coupon_id' => [
                    'nullable',
                    'integer',
                    function ($attribute, $value, $fail) {
                        if ($value != 0 && !\DB::table('coupon')->where('coupon_id', $value)->exists()) {
                            $fail('The selected coupon is invalid.');
                        }
                    },
                ],
            ]);

            $data = [
                'student_id' => $student->student_id,
                'months' => $request->input('months'),
                'year' => $request->input('year'),
                'amount' => $request->input('amount'),
                'coupon_id' => $request->input('coupon_id', 0),
                'type' => 'fees',
            ];

            $result = $this->paymentService->createRazorpayOrder($data);

            return ApiResponseHelper::success($result, 'Payment order created successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Verify fee payment after Razorpay checkout
     * POST /api/fees/payment/verify
     * 
     * Request body:
     * - razorpay_order_id: string (Razorpay order ID)
     * - razorpay_payment_id: string (Razorpay payment ID)
     * - razorpay_signature: string (Razorpay signature for verification)
     * 
     * On success, fee records are created and transaction is marked as completed
     */
    public function verifyPayment(Request $request)
    {
        try {
            $request->validate([
                'razorpay_order_id' => 'required|string',
                'razorpay_payment_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            $data = $request->only([
                'razorpay_order_id',
                'razorpay_payment_id',
                'razorpay_signature',
            ]);

            $result = $this->paymentService->verifyPayment($data);

            return ApiResponseHelper::success($result, 'Payment verified successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Handle Razorpay webhook events
     * POST /api/fees/webhook (public endpoint, no auth required)
     */
    public function handleWebhook(Request $request)
    {
        try {
            $payload = $request->all();
            $signature = $request->header('X-Razorpay-Signature', '');

            $result = $this->paymentService->handleWebhook($payload, $signature);

            return response()->json($result);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get fees summary for authenticated student
     * GET /api/fees/summary
     * 
     * Returns upcoming payment info and payment options (3 months, 6 months, 1 year)
     */
    public function getSummary(Request $request)
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

            $result = $this->feeService->getFeesSummary($student->student_id);

            if (!$result['success']) {
                return ApiResponseHelper::error($result['message'], 404);
            }

            return ApiResponseHelper::success([
                'last_paid' => $result['last_paid'] ?? null,
                'upcoming_payment' => $result['upcoming_payment'],
                'payment_options' => $result['payment_options'],
                'student' => $result['student'],
            ], 'Fees summary retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }

    /**
     * Get payment history for authenticated student
     * GET /api/fees/history
     * 
     * Query params:
     * - start_date: string (optional, format: YYYY-MM-DD)
     * - end_date: string (optional, format: YYYY-MM-DD)
     * - per_page: int (optional, default: 15)
     */
    public function getHistory(Request $request)
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
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $perPage = $request->input('per_page', 15);

            $result = $this->feeService->getPaymentHistory(
                $student->student_id,
                $startDate,
                $endDate,
                $perPage
            );

            return ApiResponseHelper::success([
                'payments' => $result['payments'],
                'pagination' => $result['pagination'],
            ], 'Payment history retrieved successfully');

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
    /**
     * Download Invoice (PDF)
     * GET /api/fees/invoice/{id}/download?type=online|manual
     */
    public function downloadInvoice(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return ApiResponseHelper::error('User not authenticated', 401);
            }

            $type = $request->input('type', 'online');

            $data = [];

            // Helper function to map month numbers to names
            $getMonthNames = function ($monthsStr) {
                if (!$monthsStr)
                    return '';
                $monthsMap = [
                    1 => 'JAN',
                    2 => 'FEB',
                    3 => 'MAR',
                    4 => 'APR',
                    5 => 'MAY',
                    6 => 'JUN',
                    7 => 'JUL',
                    8 => 'AUG',
                    9 => 'SEP',
                    10 => 'OCT',
                    11 => 'NOV',
                    12 => 'DEC'
                ];
                $parts = is_array($monthsStr) ? $monthsStr : explode(',', $monthsStr);
                $names = [];
                foreach ($parts as $m) {
                    $names[] = $monthsMap[(int) $m] ?? '';
                }
                return implode(' ', $names);
            };

            if ($type === 'online') {
                $transaction = \App\Models\Transaction::with(['student.branch', 'student.belt'])->find($id);

                if (!$transaction) {
                    return ApiResponseHelper::error('Transaction not found', 404);
                }

                // Verify ownership: Authentication user's student should match transaction student
                // Note: user->student might be null if admin, but here assume student context
                $student = $user->student;
                if ($student && $student->student_id !== $transaction->student_id) {
                    return ApiResponseHelper::error('Unauthorized access to this invoice', 403);
                }

                $data = [
                    'student' => $transaction->student,
                    'receipt_no' => $transaction->transcation_id,
                    'date' => $transaction->date ? $transaction->date->format('d-m-Y') : 'N/A',
                    'amount' => $transaction->amount,
                    'payment_mode' => 'Online (' . $transaction->type . ')',
                    'transaction_ref' => $transaction->order_id,
                    'months_display' => $getMonthNames($transaction->months) . ' ' . $transaction->year,
                ];

            } else { // manual
                $fee = \App\Models\Fee::with(['student.branch', 'student.belt'])->find($id);

                if (!$fee) {
                    return ApiResponseHelper::error('Fee record not found', 404);
                }

                // Verify ownership
                $student = $user->student;
                if ($student && $student->student_id !== $fee->student_id) {
                    return ApiResponseHelper::error('Unauthorized access to this invoice', 403);
                }

                $data = [
                    'student' => $fee->student,
                    'receipt_no' => 'M-' . $fee->fee_id,
                    'date' => $fee->date ? $fee->date->format('d-m-Y') : 'N/A',
                    'amount' => $fee->amount,
                    'payment_mode' => $fee->mode ?: 'Cash',
                    'transaction_ref' => 'Manual Entry',
                    'months_display' => $getMonthNames([$fee->months]) . ' ' . $fee->year,
                ];
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.fee_receipt', $data);

            // Generate a filename
            $filename = 'invoice_' . $id . '.pdf';

            // Return download response
            return $pdf->download($filename);

        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), ApiResponseHelper::getStatusCode($e, 500));
        }
    }
}
