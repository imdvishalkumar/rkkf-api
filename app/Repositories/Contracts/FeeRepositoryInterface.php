<?php

namespace App\Repositories\Contracts;

use App\Models\Fee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface FeeRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(int $id): ?Fee;
    
    public function create(array $data): Fee;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function getByStudent(int $studentId, array $filters = []): Collection;
    
    public function getByYear(int $year, array $filters = []): Collection;
    
    public function getByMonth(int $month, int $year, array $filters = []): Collection;
    
    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection;
}


