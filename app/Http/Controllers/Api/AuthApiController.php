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

            // Ensure role is assigned in Spatie (self-healing)
            $roleString = $user->role instanceof \App\Enums\UserRole ? $user->role->value : (string) $user->role;
            $spatieRole = match ($roleString) {
                'admin', '1' => 'admin',
                'instructor', '2' => 'instructor',
                'user', '0' => 'student',
                default => null
            };

            if ($spatieRole && !$user->hasRole($spatieRole)) {
                $user->assignRole($spatieRole);
            }

            // Create new token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Get role string value (user->role is now a UserRole enum, ->value gives the string)
            $roleString = $user->role instanceof \App\Enums\UserRole ? $user->role->value : (string) $user->role;
            $userKey = $roleString; // Use role string as response key

            return ApiResponseHelper::success([
                $userKey => [
                    'user_id' => $user->user_id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => $roleString,
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
     * Unified logout endpoint for all roles
     * 
     * Supports optional parameter to logout from all devices:
     * - Default: Revokes current token only
     * - With "all_devices": true in request body, revokes all tokens
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $logoutAll = $request->input('all_devices', false);

            if ($logoutAll) {
                // Revoke all tokens for the user (logout from all devices)
                $user->tokens()->delete();
                $message = 'Logged out from all devices successfully';
            } else {
                // Revoke only the current token (logout from current device)
                $request->user()->currentAccessToken()->delete();
                $message = 'Logged out successfully';
            }

            return ApiResponseHelper::success(null, $message);
        } catch (\Exception $e) {
            return ApiResponseHelper::error(
                'Logout failed',
                ApiResponseHelper::getStatusCode($e),
                ['error' => $e->getMessage()]
            );
        }
    }
}


