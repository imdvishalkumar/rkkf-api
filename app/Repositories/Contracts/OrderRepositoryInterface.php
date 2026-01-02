<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(int $id): ?Order;
    
    public function create(array $data): Order;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function getByStudent(int $studentId, array $filters = []): Collection;
    
    public function getByStatus(int $status, array $filters = []): Collection;
    
    public function getByDateRange(string $startDate, string $endDate, array $filters = []): Collection;
    
    public function markAsViewed(int $id): bool;
    
    public function markAsDelivered(int $id): bool;
}


