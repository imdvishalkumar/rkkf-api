<?php

namespace App\Http\Controllers\Api\AdminAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Helpers\ApiResponseHelper;
use App\Http\Requests\AdminAPI\CreateUserRequest;
use App\Http\Requests\AdminAPI\UpdateUserRequest;
use App\Enums\UserRole;

class UserManagementController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * List all users (for admin management)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->role != UserRole::ADMIN->value) {
                return ApiResponseHelper::forbidden('Only super admins can access user management');
            }

            $filters = $request->all();
            $users = $this->userService->getAllUsers($filters);

            return ApiResponseHelper::success([
                'users' => $users,
            ], 'Users retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to fetch users',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get user by ID
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
                return ApiResponseHelper::forbidden('Only super admins can access user management');
            }

            $user = $this->userService->getUserById($id);

            return ApiResponseHelper::success([
                'user' => [
                    'user_id' => $user->user_id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => ApiResponseHelper::getRoleValue($user->role),
                ],
            ], 'User retrieved successfully');

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to fetch user',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Create user (admin only)
     * 
     * @param CreateUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateUserRequest $request)
    {
        try {
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->role != UserRole::ADMIN->value) {
                return ApiResponseHelper::forbidden('Only super admins can create users');
            }

            $data = $request->validated();
            $result = $this->userService->createUser($data);

            return ApiResponseHelper::success([
                'user' => [
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
                'Failed to create user',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update user (admin only)
     * 
     * @param UpdateUserRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->role != UserRole::ADMIN->value) {
                return ApiResponseHelper::forbidden('Only super admins can update users');
            }

            $data = $request->validated();
            $result = $this->userService->updateUser($id, $data);

            return ApiResponseHelper::success([
                'user' => [
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
                'Failed to update user',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Delete user (admin only)
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
                return ApiResponseHelper::forbidden('Only super admins can delete users');
            }

            $deleted = $this->userService->deleteUser($id);

            if ($deleted) {
                return ApiResponseHelper::success(null, 'User deleted successfully');
            }

            return ApiResponseHelper::error('Failed to delete user', 500);

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to delete user',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }
}

