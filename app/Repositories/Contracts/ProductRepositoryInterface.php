<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function all(array $filters = []): Collection;
    
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    public function find(int $id): ?Product;
    
    public function create(array $data): Product;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function getActive(array $filters = []): Collection;
    
    public function getProductList(?int $beltId = null): Collection;
}


