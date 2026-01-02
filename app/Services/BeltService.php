<?php

namespace App\Services;

use App\Repositories\Contracts\BeltRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BeltService
{
    protected $beltRepository;

    public function __construct(BeltRepositoryInterface $beltRepository)
    {
        $this->beltRepository = $beltRepository;
    }

    public function getAllBelts()
    {
        return $this->beltRepository->all();
    }

    public function getBeltById(int $id)
    {
        $belt = $this->beltRepository->find($id);
        
        if (!$belt) {
            throw new Exception('Belt not found', 404);
        }

        return $belt;
    }

    public function createBelt(array $data): array
    {
        DB::beginTransaction();
        
        try {
            $belt = $this->beltRepository->create($data);

            DB::commit();

            return [
                'belt' => $belt,
                'message' => 'Belt created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating belt: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateBelt(int $id, array $data): array
    {
        $belt = $this->beltRepository->find($id);
        
        if (!$belt) {
            throw new Exception('Belt not found', 404);
        }

        $updated = $this->beltRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update belt', 500);
        }

        return [
            'belt' => $this->beltRepository->find($id),
            'message' => 'Belt updated successfully'
        ];
    }

    public function updateExamFees(array $beltFees): bool
    {
        DB::beginTransaction();
        
        try {
            foreach ($beltFees as $beltId => $examFees) {
                $this->beltRepository->update($beltId, ['exam_fees' => $examFees]);
            }

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating belt exam fees: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteBelt(int $id): bool
    {
        $belt = $this->beltRepository->find($id);
        
        if (!$belt) {
            throw new Exception('Belt not found', 404);
        }

        return $this->beltRepository->delete($id);
    }
}


