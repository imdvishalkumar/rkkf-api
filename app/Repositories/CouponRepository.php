<?php

namespace App\Repositories;

use App\Models\Coupon;
use App\Repositories\Contracts\CouponRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CouponRepository implements CouponRepositoryInterface
{
    protected $model;

    public function __construct(Coupon $model)
    {
        $this->model = $model;
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['used'])) {
            $query->where('used', $filters['used']);
        }

        return $query->orderBy('coupon_id', 'desc')->get();
    }

    public function find(int $id): ?Coupon
    {
        return $this->model->find($id);
    }

    public function findByCode(string $code): ?Coupon
    {
        return $this->model->where('coupon_txt', $code)->first();
    }

    public function create(array $data): Coupon
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $coupon = $this->find($id);
        
        if (!$coupon) {
            return false;
        }

        return $coupon->update($data);
    }

    public function delete(int $id): bool
    {
        $coupon = $this->find($id);
        
        if (!$coupon) {
            return false;
        }

        return $coupon->delete();
    }

    public function getAvailable(): Collection
    {
        return $this->all(['used' => 0]);
    }

    public function markAsUsed(int $id): bool
    {
        return $this->update($id, ['used' => 1]);
    }
}


