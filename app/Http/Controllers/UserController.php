<?php

namespace App\Http\Controllers\Api\FrontendAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Helpers\ApiResponseHelper;
use App\Http\Requests\FrontendAPI\RegisterUserRequest;
use App\Http\Requests\FrontendAPI\UpdateUserRequest;
use Illuminate\Validation\ValidationException;
use App\Enums\UserRole;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Register User
     * 
     * @param RegisterUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterUserRequest $request)
    {
        try {
            $data = $request->validated();
            // Set default role to USER if not provided
            $data['role'] = $data['role'] ?? UserRole::USER->value;

            $result = $this->userService->createUser($data);

            $token = $result['user']->createToken('api-token', ['*'])->plainTextToken;

            return ApiResponseHelper::success([
                'user' => [
                    'user_id' => $result['user']->user_id,
                    'firstname' => $result['user']->firstname,
                    'lastname' => $result['user']->lastname,
                    'email' => $result['user']->email,
                    'mobile' => $result['user']->mobile,
                    'role' => $result['user']->role,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], $result['message']);

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to register user',
                $e->getCode() ?: 500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update User
     * 
     * @param UpdateUserRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, $id)
    {
        try {
            // Ensure user can only update their own profile
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->user_id != $id) {
                return ApiResponseHelper::forbidden('You can only update your own profile');
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
                    'role' => $result['user']->role,
                ],
            ], $result['message']);

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to update user',
                $e->getCode() ?: 500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Delete User (Soft Delete)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $id)
    {
        try {
            // Ensure user can only delete their own account
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->user_id != $id) {
                return ApiResponseHelper::forbidden('You can only delete your own account');
            }

            $deleted = $this->userService->deleteUser($id);

            if ($deleted) {
                // Revoke all tokens
                $authenticatedUser->tokens()->delete();

                return ApiResponseHelper::success(null, 'User deleted successfully');
            }

            return ApiResponseHelper::error('Failed to delete user', 500);

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to delete user',
                $e->getCode() ?: 500,
                ['error' => $e->getMessage()]
            );
        }
    }
}

