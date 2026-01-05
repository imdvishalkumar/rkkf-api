<?php

namespace App\Http\Controllers\Api\AdminAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Helpers\ApiResponseHelper;
use App\Http\Requests\AdminAPI\CreateInstructorRequest;
use App\Http\Requests\AdminAPI\UpdateInstructorRequest;
use App\Enums\UserRole;

class InstructorManagementController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * List all instructors (for admin management)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->role != UserRole::ADMIN->value) {
                return ApiResponseHelper::forbidden('Only super admins can access instructor management');
            }

            $filters = ['role' => UserRole::INSTRUCTOR->value];
            $instructors = $this->userService->getAllUsers($filters);

            return ApiResponseHelper::success([
                'instructors' => $instructors,
            ], 'Instructors retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to fetch instructors',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get instructor by ID
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        try {
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->role != UserRole::ADMIN->value) {
                return ApiResponseHelper::forbidden('Only super admins can access instructor management');
            }

            $instructor = $this->userService->getUserById($id);

            // Verify it's an instructor
            if ($instructor->role != UserRole::INSTRUCTOR->value) {
                return ApiResponseHelper::error('User is not an instructor', 404);
            }

            return ApiResponseHelper::success([
                'instructor' => [
                    'user_id' => $instructor->user_id,
                    'firstname' => $instructor->firstname,
                    'lastname' => $instructor->lastname,
                    'email' => $instructor->email,
                    'mobile' => $instructor->mobile,
                    'role' => ApiResponseHelper::getRoleValue($instructor->role),
                ],
            ], 'Instructor retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to fetch instructor',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Create instructor (admin only)
     * 
     * @param CreateInstructorRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateInstructorRequest $request)
    {
        try {
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->role != UserRole::ADMIN->value) {
                return ApiResponseHelper::forbidden('Only super admins can create instructors');
            }

            $data = $request->validated();
            $data['role'] = UserRole::INSTRUCTOR->value; // Role 2 = Instructor

            $result = $this->userService->createUser($data);

            return ApiResponseHelper::success([
                'instructor' => [
                    'user_id' => $result['user']->user_id,
                    'firstname' => $result['user']->firstname,
                    'lastname' => $result['user']->lastname,
                    'email' => $result['user']->email,
                    'mobile' => $result['user']->mobile,
                    'role' => ApiResponseHelper::getRoleValue($result['user']->role),
                ],
            ], $result['message']);

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to create instructor',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update instructor (admin only)
     * 
     * @param UpdateInstructorRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateInstructorRequest $request, $id)
    {
        try {
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->role != UserRole::ADMIN->value) {
                return ApiResponseHelper::forbidden('Only super admins can update instructors');
            }

            $data = $request->validated();
            $result = $this->userService->updateUser($id, $data);

            return ApiResponseHelper::success([
                'instructor' => [
                    'user_id' => $result['user']->user_id,
                    'firstname' => $result['user']->firstname,
                    'lastname' => $result['user']->lastname,
                    'email' => $result['user']->email,
                    'mobile' => $result['user']->mobile,
                    'role' => ApiResponseHelper::getRoleValue($result['user']->role),
                ],
            ], $result['message']);

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to update instructor',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Delete instructor (admin only)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->role != UserRole::ADMIN->value) {
                return ApiResponseHelper::forbidden('Only super admins can delete instructors');
            }

            $deleted = $this->userService->deleteUser($id);

            if ($deleted) {
                return ApiResponseHelper::success(null, 'Instructor deleted successfully');
            }

            return ApiResponseHelper::error('Failed to delete instructor', 500);

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to delete instructor',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }
}

