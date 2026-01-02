<?php

namespace App\Repositories\Contracts;

use App\Models\Belt;
use Illuminate\Database\Eloquent\Collection;

interface BeltRepositoryInterface
{
    public function all(): Collection;
    
    public function find(int $id): ?Belt;
    
    public function create(array $data): Belt;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
}


