<?php

namespace App\Repositories;

use App\Models\Exam;
use App\Repositories\Contracts\ExamRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ExamRepository implements ExamRepositoryInterface
{
    protected $model;

    public function __construct(Exam $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['isPublished'])) {
            $query->where('isPublished', $filters['isPublished']);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function find(int $id): ?Exam
    {
        return $this->model->find($id);
    }

    public function create(array $data): Exam
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $exam = $this->find($id);
        
        if (!$exam) {
            return false;
        }

        return $exam->update($data);
    }

    public function delete(int $id): bool
    {
        $exam = $this->find($id);
        
        if (!$exam) {
            return false;
        }

        return $exam->delete();
    }

    public function getPublished(array $filters = []): Collection
    {
        return $this->all(array_merge($filters, ['isPublished' => 1]));
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
    }
}


