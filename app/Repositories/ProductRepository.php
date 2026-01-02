<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->with(['variations'])->orderBy('product_id', 'desc')->get();
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->with(['variations'])
            ->orderBy('product_id', 'desc')
            ->paginate($perPage);
    }

    public function find(int $id): ?Product
    {
        return $this->model->with(['variations'])->find($id);
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $product = $this->find($id);
        
        if (!$product) {
            return false;
        }

        return $product->update($data);
    }

    public function delete(int $id): bool
    {
        $product = $this->find($id);
        
        if (!$product) {
            return false;
        }

        return $product->delete();
    }

    public function getActive(array $filters = []): Collection
    {
        return $this->all(array_merge($filters, ['is_active' => 1]));
    }
}


