<?php

namespace App\Repositories\Contracts;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

interface BranchRepositoryInterface
{
    public function all(): Collection;
    
    public function find(int $id): ?Branch;
    
    public function create(array $data): Branch;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
}


