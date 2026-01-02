<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    protected $model;

    public function __construct(Attendance $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        return $query->with(['student', 'branch'])->get();
    }

    public function find(int $id): ?Attendance
    {
        return $this->model->with(['student', 'branch'])->find($id);
    }

    public function create(array $data): Attendance
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $attendance = $this->find($id);
        
        if (!$attendance) {
            return false;
        }

        return $attendance->update($data);
    }

    public function delete(int $id): bool
    {
        $attendance = $this->find($id);
        
        if (!$attendance) {
            return false;
        }

        return $attendance->delete();
    }

    public function getByStudent(int $studentId, array $filters = []): Collection
    {
        $query = $this->model->where('student_id', $studentId);

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->with(['branch'])->orderBy('date', 'desc')->get();
    }

    public function getByBranch(int $branchId, array $filters = []): Collection
    {
        $query = $this->model->where('branch_id', $branchId);

        if (isset($filters['date'])) {
            $query->where('date', $filters['date']);
        }

        return $query->with(['student'])->get();
    }

    public function getByDate(string $date, array $filters = []): Collection
    {
        $query = $this->model->where('date', $date);

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        return $query->with(['student', 'branch'])->get();
    }

    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection
    {
        $query = $this->model->whereBetween('date', [$startDate, $endDate]);

        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        return $query->with(['student', 'branch'])->get();
    }

    public function markAttendance(array $attendanceData): bool
    {
        foreach ($attendanceData as $data) {
            $existing = $this->model->where('student_id', $data['student_id'])
                ->where('date', $data['date'])
                ->where('branch_id', $data['branch_id'])
                ->first();

            if ($existing) {
                $existing->update($data);
            } else {
                $this->model->create($data);
            }
        }

        return true;
    }
}


