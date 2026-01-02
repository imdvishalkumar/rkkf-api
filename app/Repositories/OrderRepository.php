<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    protected $model;

    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['viewed'])) {
            $query->where('viewed', $filters['viewed']);
        }

        return $query->with(['student', 'product'])->orderBy('order_id', 'desc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with(['student', 'product'])
            ->orderBy('order_id', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Order
    {
        return $this->model->with(['student', 'product'])->find($id);
    }

    public function create(array $data): Order
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $order = $this->find($id);
        
        if (!$order) {
            return false;
        }

        return $order->update($data);
    }

    public function delete(int $id): bool
    {
        $order = $this->find($id);
        
        if (!$order) {
            return false;
        }

        return $order->delete();
    }

    public function getByStudent(int $studentId, array $filters = []): Collection
    {
        $query = $this->model->where('student_id', $studentId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with(['product'])->orderBy('order_id', 'desc')->get();
    }

    public function getByStatus(int $status, array $filters = []): Collection
    {
        $query = $this->model->where('status', $status);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        return $query->with(['student', 'product'])->get();
    }

    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        $query = $this->model->whereBetween('date', [$startDate, $endDate]);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with(['student', 'product'])->get();
    }

    public function markAsViewed(int $id): bool
    {
        return $this->update($id, ['viewed' => 1]);
    }

    public function markAsDelivered(int $id): bool
    {
        return $this->update($id, ['flag_delivered' => 1]);
    }
}


