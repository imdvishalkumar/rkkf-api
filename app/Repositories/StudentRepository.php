<?php

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class StudentRepository implements StudentRepositoryInterface
{
    protected $model;

    public function __construct(Student $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['branch_id'])) {
            $query->byBranch($filters['branch_id']);
        }

        if (isset($filters['belt_id'])) {
            $query->byBelt($filters['belt_id']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->with(['branch', 'belt'])->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['branch_id'])) {
            $query->byBranch($filters['branch_id']);
        }

        if (isset($filters['belt_id'])) {
            $query->byBelt($filters['belt_id']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->byDateRange($filters['start_date'], $filters['end_date']);
        }

        if (isset($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->with(['branch', 'belt'])
            ->orderBy('student_id', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Student
    {
        return $this->model->with(['branch', 'belt', 'fees'])->find($id);
    }

    public function findByEmail(string $email): ?Student
    {
        return $this->model->where('email', $email)->first();
    }

    public function search(string $term, array $filters = []): Collection
    {
        $query = $this->model->search($term);

        if (isset($filters['branch_id'])) {
            $query->byBranch($filters['branch_id']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->with(['branch', 'belt'])->get();
    }

    public function create(array $data): Student
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        if (!isset($data['active'])) {
            $data['active'] = StudentStatus::ACTIVE->value;
        }

        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $student = $this->find($id);
        
        if (!$student) {
            return false;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $student->update($data);
    }

    public function delete(int $id): bool
    {
        $student = $this->find($id);
        
        if (!$student) {
            return false;
        }

        return $student->delete();
    }

    public function activate(int $id): bool
    {
        return $this->update($id, ['active' => StudentStatus::ACTIVE->value]);
    }

    public function deactivate(int $id): bool
    {
        return $this->update($id, ['active' => StudentStatus::INACTIVE->value]);
    }

    public function resetPassword(int $id, string $password): bool
    {
        $student = $this->find($id);
        
        if (!$student) {
            return false;
        }

        return $student->update(['password' => Hash::make($password)]);
    }

    public function getByBranch(int $branchId, array $filters = []): Collection
    {
        $query = $this->model->byBranch($branchId);

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->with(['branch', 'belt'])->get();
    }

    public function getByBelt(int $beltId, array $filters = []): Collection
    {
        $query = $this->model->byBelt($beltId);

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->with(['branch', 'belt'])->get();
    }

    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        $query = $this->model->byDateRange($startDate, $endDate);

        if (isset($filters['branch_id'])) {
            $query->byBranch($filters['branch_id']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        return $query->with(['branch', 'belt'])->get();
    }

    public function checkEmailExists(string $email, ?int $excludeId = null): bool
    {
        $query = $this->model->where('email', $email);

        if ($excludeId) {
            $query->where('student_id', '!=', $excludeId);
        }

        return $query->exists();
    }
}


