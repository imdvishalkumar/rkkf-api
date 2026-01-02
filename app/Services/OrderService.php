<?php

namespace App\Services;

use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderService
{
    protected $orderRepository;
    protected $studentRepository;
    protected $productRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        StudentRepositoryInterface $studentRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->studentRepository = $studentRepository;
        $this->productRepository = $productRepository;
    }

    public function getAllOrders(array $filters = [])
    {
        return $this->orderRepository->all($filters);
    }

    public function getPaginatedOrders(array $filters = [], int $perPage = 15)
    {
        return $this->orderRepository->paginate($filters, $perPage);
    }

    public function getOrderById(int $id)
    {
        $order = $this->orderRepository->find($id);
        
        if (!$order) {
            throw new Exception('Order not found', 404);
        }

        return $order;
    }

    public function getOrdersByStudent(int $studentId, array $filters = [])
    {
        $student = $this->studentRepository->find($studentId);
        
        if (!$student) {
            throw new Exception('Student not found', 404);
        }

        return $this->orderRepository->getByStudent($studentId, $filters);
    }

    public function createOrder(array $data): array
    {
        DB::beginTransaction();
        
        try {
            $student = $this->studentRepository->find($data['student_id']);
            
            if (!$student) {
                throw new Exception('Student not found', 404);
            }

            $product = $this->productRepository->find($data['product_id']);
            
            if (!$product) {
                throw new Exception('Product not found', 404);
            }

            $order = $this->orderRepository->create($data);

            DB::commit();

            return [
                'order' => $order,
                'message' => 'Order created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateOrder(int $id, array $data): array
    {
        $order = $this->orderRepository->find($id);
        
        if (!$order) {
            throw new Exception('Order not found', 404);
        }

        $updated = $this->orderRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update order', 500);
        }

        return [
            'order' => $this->orderRepository->find($id),
            'message' => 'Order updated successfully'
        ];
    }

    public function deleteOrder(int $id): bool
    {
        $order = $this->orderRepository->find($id);
        
        if (!$order) {
            throw new Exception('Order not found', 404);
        }

        return $this->orderRepository->delete($id);
    }

    public function markOrderAsViewed(int $id): bool
    {
        $order = $this->orderRepository->find($id);
        
        if (!$order) {
            throw new Exception('Order not found', 404);
        }

        return $this->orderRepository->markAsViewed($id);
    }

    public function markOrderAsDelivered(int $id): bool
    {
        $order = $this->orderRepository->find($id);
        
        if (!$order) {
            throw new Exception('Order not found', 404);
        }

        return $this->orderRepository->markAsDelivered($id);
    }
}


