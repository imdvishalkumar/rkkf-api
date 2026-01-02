<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Helpers\ApiResponseHelper;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    /**
     * Login user and generate API token
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

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return ApiResponseHelper::error(
                    'Invalid credentials',
                    401,
                    ['email' => ['The provided credentials are incorrect.']]
                );
            }

            // Check password - support both plain text (legacy) and hashed passwords
            $passwordValid = false;
            
            // Check plain text password (for existing system compatibility)
            if ($user->password === $request->password) {
                $passwordValid = true;
                // Optionally hash the password for future use
                // $user->password = Hash::make($request->password);
                // $user->save();
            }
            // Check hashed password (for new Laravel system)
            elseif (Hash::check($request->password, $user->password)) {
                $passwordValid = true;
            }

            if (!$passwordValid) {
                return ApiResponseHelper::error(
                    'Invalid credentials',
                    401,
                    ['password' => ['The provided credentials are incorrect.']]
                );
            }

            // Revoke all existing tokens (optional - for single device login)
            // $user->tokens()->delete();

            // Create new token
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
     * Get authenticated user information
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return ApiResponseHelper::success([
            'user' => [
                'user_id' => $user->user_id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'role' => $user->role,
            ],
        ], 'User information retrieved successfully');
    }

    /**
     * Logout user and revoke token
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Revoke the current token
            $request->user()->currentAccessToken()->delete();

            return ApiResponseHelper::success(null, 'Logged out successfully');
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Logout failed',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Logout from all devices (revoke all tokens)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutAll(Request $request)
    {
        try {
            // Revoke all tokens for the user
            $request->user()->tokens()->delete();

            return ApiResponseHelper::success(null, 'Logged out from all devices successfully');
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Logout failed',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}


