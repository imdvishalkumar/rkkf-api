<?php

namespace App\Http\Controllers\Api\AdminAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Helpers\ApiResponseHelper;
use App\Http\Requests\AdminAPI\RegisterSuperAdminRequest;
use App\Http\Requests\AdminAPI\UpdateSuperAdminRequest;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Register Super Admin
     * 
     * @param RegisterSuperAdminRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterSuperAdminRequest $request)
    {
        try {
            // Only existing super admins can register new super admins
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->role != UserRole::ADMIN->value) {
                return ApiResponseHelper::forbidden('Only super admins can register new super admins');
            }

            $data = $request->validated();
            $data['role'] = UserRole::ADMIN->value; // Role 1 = Super Admin

            $result = $this->userService->createUser($data);

            $token = $result['user']->createToken('api-token', ['*'])->plainTextToken;

            return ApiResponseHelper::success([
                'super_admin' => [
                    'user_id' => $result['user']->user_id,
                    'firstname' => $result['user']->firstname,
                    'lastname' => $result['user']->lastname,
                    'email' => $result['user']->email,
                    'mobile' => $result['user']->mobile,
                    'role' => ApiResponseHelper::getRoleValue($result['user']->role),
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Super Admin registered successfully');

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to register super admin',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Update Super Admin
     * 
     * @param UpdateSuperAdminRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateSuperAdminRequest $request, $id)
    {
        try {
            $authenticatedUser = $request->user();
            
            // Super admin can update their own profile or other super admins
            if ($authenticatedUser->role != UserRole::ADMIN->value) {
                return ApiResponseHelper::forbidden('Only super admins can update super admin profiles');
            }

            $data = $request->validated();
            $result = $this->userService->updateUser($id, $data);

            return ApiResponseHelper::success([
                'super_admin' => [
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
                'Failed to update super admin',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Delete Super Admin (Soft Delete)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, $id)
    {
        try {
            $authenticatedUser = $request->user();
            
            if ($authenticatedUser->role != UserRole::ADMIN->value) {
                return ApiResponseHelper::forbidden('Only super admins can delete super admin accounts');
            }

            // Prevent self-deletion
            if ($authenticatedUser->user_id == $id) {
                return ApiResponseHelper::error('You cannot delete your own account', 403);
            }

            $deleted = $this->userService->deleteUser($id);

            if ($deleted) {
                return ApiResponseHelper::success(null, 'Super Admin deleted successfully');
            }

            return ApiResponseHelper::error('Failed to delete super admin', 500);

        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Failed to delete super admin',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Login Super Admin
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = \App\Models\User::where('email', $request->email)
                ->where('role', UserRole::ADMIN->value)
                ->first();

            if (!$user) {
                return ApiResponseHelper::error(
                    'Invalid credentials',
                    401,
                    ['email' => ['The provided credentials are incorrect.']]
                );
            }

            $passwordValid = false;
            
            if ($user->password === $request->password) {
                $passwordValid = true;
            } elseif (Hash::check($request->password, $user->password)) {
                $passwordValid = true;
            }

            if (!$passwordValid) {
                return ApiResponseHelper::error(
                    'Invalid credentials',
                    401,
                    ['password' => ['The provided credentials are incorrect.']]
                );
            }

            $token = $user->createToken('api-token', ['*'])->plainTextToken;

            return ApiResponseHelper::success([
                'super_admin' => [
                    'user_id' => $user->user_id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => ApiResponseHelper::getRoleValue($user->role),
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Login successful');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Login failed',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}

