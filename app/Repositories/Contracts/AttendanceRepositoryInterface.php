<?php

namespace App\Repositories\Contracts;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function find(int $id): ?Attendance;
    
    public function create(array $data): Attendance;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function getByStudent(int $studentId, array $filters = []): Collection;
    
    public function getByBranch(int $branchId, array $filters = []): Collection;
    
    public function getByDate(string $date, array $filters = []): Collection;
    
    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection;
    
    public function markAttendance(array $attendanceData): bool;
}


