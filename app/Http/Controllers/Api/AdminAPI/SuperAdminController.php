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
use Illuminate\Support\Facades\Log;

class SuperAdminController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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

            // Validate Role
            $role = $user->role; // Already cast to UserRole enum

            if ($role !== UserRole::ADMIN) {
                Log::warning('Login violation', [
                    'email' => $request->email,
                    'endpoint' => $request->path(),
                    'attempted_role' => $role->value,
                ]);
                return ApiResponseHelper::forbidden('Only admin accounts may login here');
            }

            // Create token
            $token = $user->createToken('auth-token')->plainTextToken;

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

