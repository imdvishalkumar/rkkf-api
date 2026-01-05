<?php

namespace App\Http\Controllers\Api\FrontendAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Helpers\ApiResponseHelper;
use App\Http\Requests\FrontendAPI\RegisterInstructorRequest;
use App\Http\Requests\FrontendAPI\UpdateInstructorRequest;
use App\Enums\UserRole;

class InstructorController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Register Instructor
     * 
     * @param RegisterInstructorRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterInstructorRequest $request)
    {
        try {
            $data = $request->validated();
            $data['role'] = UserRole::INSTRUCTOR->value; // Role 2 = Instructor

            $result = $this->userService->createUser($data);

            $token = $result['user']->createToken('api-token', ['*'])->plainTextToken;

            return ApiResponseHelper::success([
                'instructor' => [
                    'user_id' => $result['user']->user_id,
                    'firstname' => $result['user']->firstname,
                    'lastname' => $result['user']->lastname,
                    'email' => $result['user']->email,
                    'mobile' => $result['user']->mobile,
                    'role' => ApiResponseHelper::getRoleValue($result['user']->role),
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Instructor registered successfully');

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to register instructor',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update Instructor
     * 
     * @param UpdateInstructorRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateInstructorRequest $request, $id)
    {
        try {
            // Ensure instructor can only update their own profile
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->user_id != $id) {
                return ApiResponseHelper::forbidden('You can only update your own profile');
            }

            // Ensure user is an instructor
            if ($authenticatedUser->role != UserRole::INSTRUCTOR->value) {
                return ApiResponseHelper::forbidden('Only instructors can update instructor profiles');
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
     * Delete Instructor (Soft Delete)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $id)
    {
        try {
            // Ensure instructor can only delete their own account
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->user_id != $id) {
                return ApiResponseHelper::forbidden('You can only delete your own account');
            }

            // Ensure user is an instructor
            if ($authenticatedUser->role != UserRole::INSTRUCTOR->value) {
                return ApiResponseHelper::forbidden('Only instructors can delete instructor accounts');
            }

            $deleted = $this->userService->deleteUser($id);

            if ($deleted) {
                // Revoke all tokens
                $authenticatedUser->tokens()->delete();

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

