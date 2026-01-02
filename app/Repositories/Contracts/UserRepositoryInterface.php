<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function find(int $id): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function create(array $data): User;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function checkEmailExists(string $email, ?int $excludeId = null): bool;
    
    public function softDelete(int $id): bool;
}

