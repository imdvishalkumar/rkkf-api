<?php

namespace App\Services;

use App\Repositories\Contracts\CouponRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CouponService
{
    protected $couponRepository;

    public function __construct(CouponRepositoryInterface $couponRepository)
    {
        $this->couponRepository = $couponRepository;
    }

    public function getAllCoupons(array $filters = [])
    {
        return $this->couponRepository->all($filters);
    }

    public function getCouponById(int $id)
    {
        $coupon = $this->couponRepository->find($id);
        
        if (!$coupon) {
            throw new Exception('Coupon not found', 404);
        }

        return $coupon;
    }

    public function getCouponByCode(string $code)
    {
        $coupon = $this->couponRepository->findByCode($code);
        
        if (!$coupon) {
            throw new Exception('Coupon not found', 404);
        }

        return $coupon;
    }

    public function getAvailableCoupons()
    {
        return $this->couponRepository->getAvailable();
    }

    public function createCoupon(array $data): array
    {
        DB::beginTransaction();
        
        try {
            $existing = $this->couponRepository->findByCode($data['coupon_txt']);
            
            if ($existing) {
                throw new Exception('Coupon code already exists', 422);
            }

            $coupon = $this->couponRepository->create($data);

            DB::commit();

            return [
                'coupon' => $coupon,
                'message' => 'Coupon created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating coupon: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateCoupon(int $id, array $data): array
    {
        $coupon = $this->couponRepository->find($id);
        
        if (!$coupon) {
            throw new Exception('Coupon not found', 404);
        }

        if (isset($data['coupon_txt']) && $data['coupon_txt'] !== $coupon->coupon_txt) {
            $existing = $this->couponRepository->findByCode($data['coupon_txt']);
            
            if ($existing && $existing->coupon_id !== $id) {
                throw new Exception('Coupon code already exists', 422);
            }
        }

        $updated = $this->couponRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update coupon', 500);
        }

        return [
            'coupon' => $this->couponRepository->find($id),
            'message' => 'Coupon updated successfully'
        ];
    }

    public function deleteCoupon(int $id): bool
    {
        $coupon = $this->couponRepository->find($id);
        
        if (!$coupon) {
            throw new Exception('Coupon not found', 404);
        }

        return $this->couponRepository->delete($id);
    }

    public function markCouponAsUsed(int $id): bool
    {
        $coupon = $this->couponRepository->find($id);
        
        if (!$coupon) {
            throw new Exception('Coupon not found', 404);
        }

        return $this->couponRepository->markAsUsed($id);
    }
}


