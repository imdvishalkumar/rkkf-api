<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductService
{
    protected $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function getAllProducts(array $filters = [])
    {
        return $this->productRepository->all($filters);
    }

    public function getPaginatedProducts(array $filters = [], int $perPage = 15)
    {
        return $this->productRepository->paginate($filters, $perPage);
    }

    public function getProductById(int $id)
    {
        $product = $this->productRepository->find($id);
        
        if (!$product) {
            throw new Exception('Product not found', 404);
        }

        return $product;
    }

    public function getActiveProducts(array $filters = [])
    {
        return $this->productRepository->getActive($filters);
    }

    public function createProduct(array $data): array
    {
        DB::beginTransaction();
        
        try {
            $product = $this->productRepository->create($data);

            DB::commit();

            return [
                'product' => $product,
                'message' => 'Product created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating product: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateProduct(int $id, array $data): array
    {
        $product = $this->productRepository->find($id);
        
        if (!$product) {
            throw new Exception('Product not found', 404);
        }

        $updated = $this->productRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update product', 500);
        }

        return [
            'product' => $this->productRepository->find($id),
            'message' => 'Product updated successfully'
        ];
    }

    public function deleteProduct(int $id): bool
    {
        $product = $this->productRepository->find($id);
        
        if (!$product) {
            throw new Exception('Product not found', 404);
        }

        return $this->productRepository->delete($id);
    }

    /**
     * Get product list with belt_id filter
     * Returns products with variations where qty > 0
     * Filters by belt_id if provided (checks if belt_id is in comma-separated belt_ids string)
     */
    public function getProductList(?int $beltId = null, int $perPage = 10, int $page = 1)
    {
        return $this->productRepository->getProductList($beltId, $perPage, $page);
    }
}


