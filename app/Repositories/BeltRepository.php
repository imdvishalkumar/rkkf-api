<?php

namespace App\Repositories;

use App\Models\Belt;
use App\Repositories\Contracts\BeltRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BeltRepository implements BeltRepositoryInterface
{
    protected $model;

    public function __construct(Belt $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->orderBy('priority')->get();
    }

    public function find(int $id): ?Belt
    {
        return $this->model->with(['students'])->find($id);
    }

    public function create(array $data): Belt
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $belt = $this->find($id);
        
        if (!$belt) {
            return false;
        }

        return $belt->update($data);
    }

    public function delete(int $id): bool
    {
        $belt = $this->find($id);
        
        if (!$belt) {
            return false;
        }

        return $belt->delete();
    }
}


