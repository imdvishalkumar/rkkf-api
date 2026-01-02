<?php

namespace App\Repositories\Contracts;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Collection;

interface CouponRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function find(int $id): ?Coupon;
    
    public function findByCode(string $code): ?Coupon;
    
    public function create(array $data): Coupon;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function getAvailable(): Collection;
    
    public function markAsUsed(int $id): bool;
}


