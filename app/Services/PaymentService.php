<?php

namespace App\Services;

use App\Repositories\Contracts\FeeRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ExamRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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


