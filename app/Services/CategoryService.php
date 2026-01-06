<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService
{
    protected $repository;

    public function __construct(CategoryRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCategories()
    {
        return $this->repository->getAll();
    }

    public function createCategory(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateCategory($category, array $data)
    {
        return $this->repository->update($category, $data);
    }

    public function deleteCategory($category)
    {
        return $this->repository->delete($category);
    }
}
