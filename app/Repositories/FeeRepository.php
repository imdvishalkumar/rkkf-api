<?php

namespace App\Repositories;

use App\Models\Fee;
use App\Repositories\Contracts\FeeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FeeRepository implements FeeRepositoryInterface
{
    protected $model;

    public function __construct(Fee $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (isset($filters['months'])) {
            $query->whereIn('months', (array)$filters['months']);
        }

        return $query->with(['student', 'coupon'])->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if (isset($filters['months'])) {
            $query->whereIn('months', (array)$filters['months']);
        }

        return $query->with(['student', 'coupon'])
            ->orderBy('fee_id', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Fee
    {
        return $this->model->with(['student', 'coupon'])->find($id);
    }

    public function create(array $data): Fee
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $fee = $this->find($id);
        
        if (!$fee) {
            return false;
        }

        return $fee->update($data);
    }

    public function delete(int $id): bool
    {
        $fee = $this->find($id);
        
        if (!$fee) {
            return false;
        }

        return $fee->delete();
    }

    public function getByStudent(int $studentId, array $filters = []): Collection
    {
        $query = $this->model->where('student_id', $studentId);

        if (isset($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        return $query->with(['coupon'])->orderBy('year', 'desc')->orderBy('months', 'desc')->get();
    }

    public function getByYear(int $year, array $filters = []): Collection
    {
        $query = $this->model->where('year', $year);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        return $query->with(['student'])->get();
    }

    public function getByMonth(int $month, int $year, array $filters = []): Collection
    {
        $query = $this->model->where('months', $month)->where('year', $year);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        return $query->with(['student'])->get();
    }

    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        $query = $this->model->whereBetween('date', [$startDate, $endDate]);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        return $query->with(['student'])->get();
    }
}


