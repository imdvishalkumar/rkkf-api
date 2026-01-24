<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BranchService;
use App\Http\Resources\BranchResource;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Helpers\ApiResponseHelper;
use Illuminate\Http\Request;
use Exception;

class BranchApiController extends Controller
{
    protected $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    /**
     * Get the branch for the currently logged-in user
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Allow admins to see all branches if needed, or stick to the requirement.
            // Requirement says: "return only the branch details based on the branch_id of the currently logged-in user"
            // So we will strictly follow that.

            // Check if user has a related student record
            $student = $user->student;

            if (!$student) {
                return ApiResponseHelper::error('No student profile found for this user', 404);
            }

            if (!$student->branch_id) {
                return ApiResponseHelper::error('No branch assigned to this student', 404);
            }

            $branch = $this->branchService->getBranchById($student->branch_id);

            if (!$branch) {
                return ApiResponseHelper::error('Branch not found', 404);
            }

            return ApiResponseHelper::success(
                new BranchResource($branch),
                'Branch retrieved successfully'
            );
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Create a new branch
     */
    public function store(StoreBranchRequest $request)
    {
        try {
            $branch = $this->branchService->createBranch($request->validated());
            return ApiResponseHelper::success(
                new BranchResource($branch),
                'Branch created successfully',
                201
            );
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Get branch details
     */
    public function show($id)
    {
        try {
            $branch = $this->branchService->getBranchById($id);
            if (!$branch) {
                return ApiResponseHelper::error('Branch not found', 404);
            }
            return ApiResponseHelper::success(
                new BranchResource($branch),
                'Branch details retrieved successfully'
            );
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Update branch details
     */
    public function update(UpdateBranchRequest $request, $id)
    {
        try {
            $branch = $this->branchService->updateBranch($id, $request->validated());
            if (!$branch) {
                return ApiResponseHelper::error('Branch not found', 404);
            }
            return ApiResponseHelper::success(
                new BranchResource($branch),
                'Branch updated successfully'
            );
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }

    /**
     * Delete branch
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->branchService->deleteBranch($id);
            if (!$deleted) {
                return ApiResponseHelper::error('Branch not found', 404);
            }
            return ApiResponseHelper::success(null, 'Branch deleted successfully');
        } catch (Exception $e) {
            return ApiResponseHelper::error($e->getMessage(), 500);
        }
    }
}
