<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Exception;

class BranchService
{
    public function getAllBranches()
    {
        return Branch::where('is_active', 1)->get();
    }

    public function getBranchById($id)
    {
        return Branch::find($id);
    }

    public function createBranch(array $data)
    {
        DB::beginTransaction();

        try {
            // Ensure defaults
            if (!isset($data['is_active'])) {
                $data['is_active'] = true;
            }

            $branch = Branch::create($data);

            DB::commit();
            return $branch;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateBranch($id, array $data)
    {
        $branch = Branch::find($id);

        if (!$branch) {
            return null;
        }

        DB::beginTransaction();
        try {
            $branch->update($data);
            DB::commit();
            return $branch;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteBranch($id)
    {
        $branch = Branch::find($id);
        if (!$branch) {
            return false;
        }

        // Optional: Check conflicts (e.g. students assigned to branch)
        // If students exist, maybe just deactivate?
        // For now, attempting delete.

        return $branch->delete();
    }
}
