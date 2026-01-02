<?php

namespace App\Services;

use App\Repositories\Contracts\BranchRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BranchService
{
    protected $branchRepository;

    public function __construct(BranchRepositoryInterface $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }

    public function getAllBranches()
    {
        return $this->branchRepository->all();
    }

    public function getBranchById(int $id)
    {
        $branch = $this->branchRepository->find($id);
        
        if (!$branch) {
            throw new Exception('Branch not found', 404);
        }

        return $branch;
    }

    public function createBranch(array $data): array
    {
        DB::beginTransaction();
        
        try {
            $branch = $this->branchRepository->create($data);

            DB::commit();

            return [
                'branch' => $branch,
                'message' => 'Branch created successfully'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating branch: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateBranch(int $id, array $data): array
    {
        $branch = $this->branchRepository->find($id);
        
        if (!$branch) {
            throw new Exception('Branch not found', 404);
        }

        $updated = $this->branchRepository->update($id, $data);

        if (!$updated) {
            throw new Exception('Failed to update branch', 500);
        }

        return [
            'branch' => $this->branchRepository->find($id),
            'message' => 'Branch updated successfully'
        ];
    }

    public function deleteBranch(int $id): bool
    {
        $branch = $this->branchRepository->find($id);
        
        if (!$branch) {
            throw new Exception('Branch not found', 404);
        }

        return $this->branchRepository->delete($id);
    }
}


