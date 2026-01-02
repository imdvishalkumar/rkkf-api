<?php

namespace App\Repositories\Contracts;

use App\Models\Exam;
use Illuminate\Database\Eloquent\Collection;

interface ExamRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function find(int $id): ?Exam;
    
    public function create(array $data): Exam;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function getPublished(array $filters = []): Collection;
    
    public function getByDateRange(string $startDate, string $endDate): Collection;
}


