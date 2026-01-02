<?php

namespace App\Repositories;

use App\Models\Branch;
use App\Repositories\Contracts\BranchRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BranchRepository implements BranchRepositoryInterface
{
    protected $model;

    public function __construct(Branch $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->orderBy('name')->get();
    }

    public function find(int $id): ?Branch
    {
        return $this->model->with(['students'])->find($id);
    }

    public function create(array $data): Branch
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $branch = $this->find($id);
        
        if (!$branch) {
            return false;
        }

        return $branch->update($data);
    }

    public function delete(int $id): bool
    {
        $branch = $this->find($id);
        
        if (!$branch) {
            return false;
        }

        return $branch->delete();
    }
}


