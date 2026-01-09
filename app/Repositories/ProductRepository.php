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

    /**
     * Get product list with belt_id filter
     * Joins products and variation tables, filters by qty > 0
     * If belt_id provided, filters products where belt_id is in comma-separated belt_ids string
     * Returns flattened structure matching core PHP API (product + variation fields in same row)
     */
    public function getProductList(?int $beltId = null, ?int $productCategoryId = null, ?string $productCategoryName = null, ?string $search = null, int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with([
                'variations' => function ($q) {
                    $q->where('qty', '>', 0);
                },
                'productCategory' // Eager load category for search optimization if needed
            ])
            ->whereHas('variations', function ($q) {
                $q->where('qty', '>', 0);
            })
            ->where('is_active', 1);

        if ($beltId) {
            // Filter products where belt_id is in comma-separated belt_ids string
            $query->whereRaw('FIND_IN_SET(?, products.belt_ids)', [$beltId]);
        }

        if ($productCategoryId) {
            $query->where('product_category_id', $productCategoryId);
        }

        if ($productCategoryName) {
            $query->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
                ->where('product_categories.name', 'like', '%' . $productCategoryName . '%')
                ->select('products.*');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', '%' . $search . '%')
                    ->orWhereHas('productCategory', function ($q2) use ($search) {
                        $q2->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query->orderBy('products.product_id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}


