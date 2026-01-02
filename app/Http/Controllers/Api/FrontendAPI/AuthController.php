<?php

namespace App\Http\Controllers\Api\FrontendAPI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Helpers\ApiResponseHelper;
use Illuminate\Validation\ValidationException;
use App\Enums\UserRole;

class AuthController extends Controller
{
    /**
     * Register User
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerUser(Request $request)
    {
        try {
            $validated = $request->validate([
                'firstname' => 'required|string|max:50',
                'lastname' => 'required|string|max:50',
                'email' => 'required|email|unique:users,email|max:100',
                'password' => 'required|string|min:6',
                'mobile' => 'nullable|string|max:15',
            ]);

            // Create user with role = 0 (regular user) or you can set a specific role
            // Based on existing schema, role 1 = Admin, 2 = Instructor
            // For frontend users, we might need a different role or use role = 0
            // For now, let's assume frontend users are regular users (role = 0)
            $validated['role'] = 0; // Regular user role

            // Use repository to create user (supports plain text passwords)
            $userRepository = app(\App\Repositories\Contracts\UserRepositoryInterface::class);
            $user = $userRepository->create($validated);

            // Create token
            $token = $user->createToken('api-token', ['*'])->plainTextToken;

            return ApiResponseHelper::success([
                'user' => [
                    'user_id' => $user->user_id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => $user->role,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'User registered successfully');

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Registration failed',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Login User
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginUser(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)
                ->where('role', 0) // Only regular users
                ->first();

            if (!$user) {
                return ApiResponseHelper::error(
                    'Invalid credentials',
                    401,
                    ['email' => ['The provided credentials are incorrect.']]
                );
            }

            // Check password - support both plain text (legacy) and hashed passwords
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
                'user' => [
                    'user_id' => $user->user_id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => $user->role,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Login successful');

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Login failed',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Register Instructor
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerInstructor(Request $request)
    {
        try {
            $validated = $request->validate([
                'firstname' => 'required|string|max:50',
                'lastname' => 'required|string|max:50',
                'email' => 'required|email|unique:users,email|max:100',
                'password' => 'required|string|min:6',
                'mobile' => 'nullable|string|max:15',
            ]);

            $validated['role'] = UserRole::INSTRUCTOR->value; // Role 2 = Instructor

            // Use repository to create user (supports plain text passwords)
            $userRepository = app(\App\Repositories\Contracts\UserRepositoryInterface::class);
            $user = $userRepository->create($validated);

            $token = $user->createToken('api-token', ['*'])->plainTextToken;

            return ApiResponseHelper::success([
                'instructor' => [
                    'user_id' => $user->user_id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => $user->role,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Instructor registered successfully');

        } catch (ValidationException $e) {
            return ApiResponseHelper::validationError($e->errors());
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Registration failed',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Login Instructor
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginInstructor(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)
                ->where('role', UserRole::INSTRUCTOR->value)
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
                'instructor' => [
                    'user_id' => $user->user_id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => $user->role,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ], 'Login successful');

        } catch (ValidationException $e) {
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

