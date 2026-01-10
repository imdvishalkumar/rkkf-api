<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Fee;
use App\Models\Transaction;
use App\Repositories\Contracts\FeeRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class PaymentService
{
    protected $feeRepository;
    protected $orderRepository;
    protected $examRepository;
    protected $eventRepository;

    public function __construct(
        FeeRepositoryInterface $feeRepository,
        OrderRepositoryInterface $orderRepository,
        ExamRepositoryInterface $examRepository,
        EventRepositoryInterface $eventRepository
    ) {
        $this->feeRepository = $feeRepository;
        $this->orderRepository = $orderRepository;
        $this->examRepository = $examRepository;
        $this->eventRepository = $eventRepository;
    }

    /**
     * Get Razorpay credentials based on student's branch
     *
     * @param int $studentId
     * @param string $type Type of payment: 'fees', 'product', 'exam', 'event'
     * @return array
     */
    public function getRazorpayCredentials(int $studentId, string $type = 'fees'): array
    {
        // For product payments, always use the default RF Sales account
        if ($type === 'product') {
            $account = config('razorpay.accounts.rf_sales');
            return [
                'key_id' => $account['key_id'],
                'key_secret' => $account['key_secret'],
                'account_name' => 'RF SALES',
            ];
        }

        // Get student's branch
        $student = Student::with('branch')->find($studentId);
        if (!$student || !$student->branch) {
            throw new Exception('Student or branch not found');
        }

        $branchId = $student->branch->branch_id;

        // Check each account's branch list
        $accounts = config('razorpay.accounts');

        foreach (['kuku_exam', 'yogoju_event', 'rkkf_fee'] as $accountKey) {
            if (in_array($branchId, $accounts[$accountKey]['branches'])) {
                return [
                    'key_id' => $accounts[$accountKey]['key_id'],
                    'key_secret' => $accounts[$accountKey]['key_secret'],
                    'account_name' => strtoupper(str_replace('_', ' ', $accountKey)),
                ];
            }
        }

        // Default to RF Sales account
        $defaultAccount = $accounts[config('razorpay.default_account')];
        return [
            'key_id' => $defaultAccount['key_id'],
            'key_secret' => $defaultAccount['key_secret'],
            'account_name' => 'RF SALES',
        ];
    }

    /**
     * Create a Razorpay order for fee payment
     *
     * @param array $data
     * @return array
     */
    public function createRazorpayOrder(array $data): array
    {
        $studentId = $data['student_id'];
        $months = $data['months'];
        $year = $data['year'];
        $amount = $data['amount'];
        $couponId = $data['coupon_id'] ?? 0;
        $type = $data['type'] ?? 'fees';

        // Get Razorpay credentials based on branch
        $credentials = $this->getRazorpayCredentials($studentId, $type);

        DB::beginTransaction();

        try {
            // Create Razorpay order via API
            $orderData = [
                'amount' => $amount * 100, // Convert to paise
                'currency' => config('razorpay.currency', 'INR'),
            ];

            $response = Http::withBasicAuth($credentials['key_id'], $credentials['key_secret'])
                ->post('https://api.razorpay.com/v1/orders', $orderData);

            if (!$response->successful()) {
                throw new Exception('Failed to create Razorpay order: ' . $response->body());
            }

            $razorpayOrder = $response->json();
            $razorpayOrderId = $razorpayOrder['id'];

            // Create transaction record
            $transaction = Transaction::create([
                'student_id' => $studentId,
                'order_id' => $razorpayOrderId,
                'status' => Transaction::STATUS_PENDING,
                'type' => $type,
                'ref_id' => 0,
                'amount' => $amount,
                'date' => now()->toDateString(),
                'months' => $months,
                'year' => $year,
                'coupon_id' => $couponId,
            ]);

            DB::commit();

            return [
                'success' => true,
                'orderId' => $razorpayOrderId,
                'keyId' => $credentials['key_id'],
                'amount' => $amount,
                'currency' => config('razorpay.currency', 'INR'),
                'transaction_id' => $transaction->transcation_id,
                'message' => 'Order created successfully',
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating Razorpay order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Verify Razorpay payment and update records
     *
     * @param array $data
     * @return array
     */
    public function verifyPayment(array $data): array
    {
        $orderId = $data['razorpay_order_id'];
        $paymentId = $data['razorpay_payment_id'];
        $signature = $data['razorpay_signature'];

        DB::beginTransaction();

        try {
            // Find the transaction
            $transaction = Transaction::where('order_id', $orderId)->first();

            if (!$transaction) {
                throw new Exception('Transaction not found for order ID: ' . $orderId);
            }

            // Get credentials based on student's branch
            $credentials = $this->getRazorpayCredentials($transaction->student_id, $transaction->type);

            // Verify signature
            $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $credentials['key_secret']);

            if ($signature !== $expectedSignature) {
                // Update transaction as failed
                $transaction->update(['status' => Transaction::STATUS_FAILED]);
                DB::commit();
                throw new Exception('Payment signature verification failed');
            }

            // Process based on payment type
            if ($transaction->type === 'fees') {
                $this->processFeePaymentSuccess($transaction);
            }

            // Update transaction as completed
            $transaction->update(['status' => Transaction::STATUS_COMPLETED]);

            // Activate student if fees are up to date
            $this->activateStudentIfEligible($transaction->student_id);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Payment verified successfully',
                'transaction_id' => $transaction->transcation_id,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error verifying payment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process successful fee payment - insert fee records
     *
     * @param Transaction $transaction
     * @return void
     */
    private function processFeePaymentSuccess(Transaction $transaction): void
    {
        $monthsArray = explode(',', $transaction->months);
        $count = count($monthsArray);
        $amount = $transaction->amount;
        $remainder = $amount % $count;
        $feePerMonth = ($amount - $remainder) / $count;

        $year = $transaction->year;
        $isFirst = true;

        foreach ($monthsArray as $month) {
            $month = (int) $month;
            $feeAmount = $isFirst ? ($feePerMonth + $remainder) : $feePerMonth;
            $isFirst = false;

            // Check if fee record already exists
            $exists = Fee::where('student_id', $transaction->student_id)
                ->where('months', $month)
                ->where('year', $year)
                ->where('mode', $transaction->order_id)
                ->exists();

            if (!$exists) {
                Fee::create([
                    'student_id' => $transaction->student_id,
                    'months' => $month,
                    'year' => $year,
                    'date' => $transaction->date,
                    'amount' => $feeAmount,
                    'coupon_id' => $transaction->coupon_id,
                    'additional' => false,
                    'disabled' => false,
                    'mode' => $transaction->order_id,
                    'remarks' => '',
                ]);
            }

            // Handle year increment when crossing December
            if ($month == 12) {
                $year++;
            }
        }

        // Update transaction ref_id with the last fee ID
        $lastFee = Fee::where('student_id', $transaction->student_id)
            ->where('mode', $transaction->order_id)
            ->orderBy('fee_id', 'desc')
            ->first();

        if ($lastFee) {
            $transaction->update(['ref_id' => $lastFee->fee_id]);
        }
    }

    /**
     * Activate student if their fees are up to date
     *
     * @param int $studentId
     * @return void
     */
    private function activateStudentIfEligible(int $studentId): void
    {
        // Get the latest fee record
        $latestFee = Fee::where('student_id', $studentId)
            ->orderBy('year', 'desc')
            ->orderBy('months', 'desc')
            ->first();

        if (!$latestFee) {
            return;
        }

        // Check if fees are current (within last month)
        $feeDate = \Carbon\Carbon::createFromFormat('Y-m-d', "{$latestFee->year}-{$latestFee->months}-01");
        $oneMonthAgo = now()->startOfMonth()->subMonth();

        if ($feeDate->gte($oneMonthAgo)) {
            Student::where('student_id', $studentId)->update(['active' => true]);
        }
    }

    /**
     * Handle Razorpay webhook events
     *
     * @param array $payload The webhook payload
     * @param string $signature The webhook signature header
     * @return array
     */
    public function handleWebhook(array $payload, string $signature): array
    {
        $webhookSecret = config('razorpay.webhook_secret');

        if (empty($webhookSecret)) {
            Log::warning('Razorpay webhook secret not configured');
        }

        $event = $payload['event'] ?? null;
        $orderId = $payload['payload']['payment']['entity']['order_id'] ?? null;
        $description = $payload['payload']['payment']['entity']['description'] ?? null;

        if (!$orderId) {
            return ['success' => false, 'message' => 'Order ID not found in webhook payload'];
        }

        Log::info('Razorpay webhook received', ['event' => $event, 'order_id' => $orderId, 'description' => $description]);

        switch ($event) {
            case 'payment.authorized':
            case 'payment.captured':
                return $this->handlePaymentSuccess($orderId, $description);

            case 'payment.failed':
                return $this->handlePaymentFailure($orderId, $description);

            default:
                Log::info('Unhandled webhook event: ' . $event);
                return ['success' => true, 'message' => 'Event acknowledged'];
        }
    }

    /**
     * Handle successful payment from webhook
     *
     * @param string $orderId
     * @param string|null $description
     * @return array
     */
    private function handlePaymentSuccess(string $orderId, ?string $description): array
    {
        $transaction = Transaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            Log::warning('Transaction not found for webhook order: ' . $orderId);
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        // Skip if already processed
        if ($transaction->status == Transaction::STATUS_COMPLETED) {
            return ['success' => true, 'message' => 'Already processed'];
        }

        DB::beginTransaction();
        try {
            if ($transaction->type === 'fees' || $description === 'monthlyFees') {
                $this->processFeePaymentSuccess($transaction);
            }

            $transaction->update(['status' => Transaction::STATUS_COMPLETED]);
            $this->activateStudentIfEligible($transaction->student_id);

            DB::commit();
            return ['success' => true, 'message' => 'Payment processed successfully'];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing webhook payment: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Processing failed'];
        }
    }

    /**
     * Handle failed payment from webhook
     *
     * @param string $orderId
     * @param string|null $description
     * @return array
     */
    private function handlePaymentFailure(string $orderId, ?string $description): array
    {
        $transaction = Transaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            Log::warning('Transaction not found for failed payment webhook: ' . $orderId);
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        $transaction->update(['status' => Transaction::STATUS_FAILED]);

        return ['success' => true, 'message' => 'Failure recorded'];
    }

    // Legacy methods for backward compatibility

    public function processFeePayment(array $data): array
    {
        DB::beginTransaction();

        try {
            $fee = $this->feeRepository->find($data['fee_id']);

            if (!$fee) {
                throw new Exception('Fee not found', 404);
            }

            $updated = $this->feeRepository->update($data['fee_id'], [
                'mode' => $data['mode'] ?? 'cash',
                'date' => $data['date'] ?? date('Y-m-d'),
            ]);

            if (!$updated) {
                throw new Exception('Failed to process payment', 500);
            }

            DB::commit();

            return [
                'fee' => $this->feeRepository->find($data['fee_id']),
                'message' => 'Payment processed successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing fee payment: ' . $e->getMessage());
            throw $e;
        }
    }

    public function processOrderPayment(array $data): array
    {
        DB::beginTransaction();

        try {
            $order = $this->orderRepository->find($data['order_id']);

            if (!$order) {
                throw new Exception('Order not found', 404);
            }

            $updated = $this->orderRepository->update($data['order_id'], [
                'status' => $data['status'] ?? 1,
            ]);

            if (!$updated) {
                throw new Exception('Failed to process payment', 500);
            }

            DB::commit();

            return [
                'order' => $this->orderRepository->find($data['order_id']),
                'message' => 'Payment processed successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing order payment: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPaymentReport(array $filters = []): array
    {
        $fees = $this->feeRepository->getByDateRange(
            $filters['start_date'] ?? date('Y-m-01'),
            $filters['end_date'] ?? date('Y-m-d')
        );

        $orders = $this->orderRepository->getByDateRange(
            $filters['start_date'] ?? date('Y-m-01'),
            $filters['end_date'] ?? date('Y-m-d')
        );

        $totalFees = $fees->sum('amount');
        $totalOrders = $orders->sum('p_price');

        return [
            'fees' => $fees,
            'orders' => $orders,
            'total_fees' => $totalFees,
            'total_orders' => $totalOrders,
            'grand_total' => $totalFees + $totalOrders,
        ];
    }
}


