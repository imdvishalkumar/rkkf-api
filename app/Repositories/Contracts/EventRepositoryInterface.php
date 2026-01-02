<?php

namespace App\Repositories\Contracts;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

interface EventRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function find(int $id): ?Event;
    
    public function create(array $data): Event;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function getPublished(array $filters = []): Collection;
    
    public function getByDateRange(string $startDate, string $endDate): Collection;
}


