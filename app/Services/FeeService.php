<?php

namespace App\Services;

use App\Repositories\Contracts\FeeRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\CouponRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class FeeService
{
    protected $feeRepository;
    protected $studentRepository;
    protected $couponRepository;

    public function __construct(
        FeeRepositoryInterface $feeRepository,
        StudentRepositoryInterface $studentRepository,
        CouponRepositoryInterface $couponRepository
    ) {
        $this->feeRepository = $feeRepository;
        $this->studentRepository = $studentRepository;
        $this->couponRepository = $couponRepository;
    }

    public function getAllFees(array $filters = [])
    {
        return $this->feeRepository->all($filters);
    }

    public function getPaginatedFees(array $filters = [], int $perPage = 15)
    {
        return $this->feeRepository->paginate($filters, $perPage);
    }

    public function getFeeById(int $id)
    {
        $fee = $this->feeRepository->find($id);
        
        if (!$fee) {
            throw new Exception('Fee not found', 404);
        }

        return $fee;
    }

    public function getFeesByStudent(int $studentId, array $filters = [])
    {
        $student = $this->studentRepository->find($studentId);
        
        if (!$student) {
            throw new Exception('Student not found', 404);
        }

        return $this->feeRepository->getByStudent($studentId, $filters);
    }

    public function createFee(array $data): array
    {
        DB::beginTransaction();
        
        try {
            $student = $this->studentRepository->find($data['student_id']);
            
            if (!$student) {
                throw new Exception('Student not found', 404);
            }

            if (isset($data['coupon_id'])) {
                $coupon = $this->couponRepository->find($data['coupon_id']);
                if (!$coupon) {
                    throw new Exception('Invalid coupon', 422);
                }
            }

            $fee = $this->feeRepository->create($data);

            DB::commit();

            return [
                'fee' => $fee,
                'message' => 'Fee created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating fee: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateFee(int $id, array $data): array
    {
        $fee = $this->feeRepository->find($id);
        
        if (!$fee) {
            throw new Exception('Fee not found', 404);
        }

        $updated = $this->feeRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update fee', 500);
        }

        return [
            'fee' => $this->feeRepository->find($id),
            'message' => 'Fee updated successfully'
        ];
    }

    public function deleteFee(int $id): bool
    {
        $fee = $this->feeRepository->find($id);
        
        if (!$fee) {
            throw new Exception('Fee not found', 404);
        }

        return $this->feeRepository->delete($id);
    }

    public function getFeesByYear(int $year, array $filters = [])
    {
        return $this->feeRepository->getByYear($year, $filters);
    }

    public function getFeesByMonth(int $month, int $year, array $filters = [])
    {
        return $this->feeRepository->getByMonth($month, $year, $filters);
    }
}


