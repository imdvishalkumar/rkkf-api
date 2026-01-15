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
     * List all active branches
     */
    public function index()
    {
        try {
            $branches = $this->branchService->getAllBranches();
            return ApiResponseHelper::success(
                BranchResource::collection($branches),
                'Branches retrieved successfully'
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
