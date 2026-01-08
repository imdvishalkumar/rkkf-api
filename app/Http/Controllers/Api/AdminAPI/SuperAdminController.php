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
use App\Models\User;

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
     * Role-Endpoint Binding:
     * - admin: Allowed
     * - user: NOT ALLOWED (must use /login)
     * - instructor: NOT ALLOWED (must use /login)
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

            $user = User::where('email', $request->email)
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

            // Validate Role - Enforce Role-Endpoint Binding
            $role = $user->role; // Already cast to UserRole enum

            if ($role !== UserRole::ADMIN) {
                Log::warning('RBAC violation', [
                    'email' => $request->email,
                    'endpoint' => $request->path(),
                    'attempted_role' => $role->value,
                ]);
                return ApiResponseHelper::forbidden('Only admin accounts may login here');
            }

            // Spatie Role Safety Validation - Check role exists before assignment
            $spatieRoleName = $role->spatieRole();
            
            if (!User::validateSpatieRoleExists($spatieRoleName)) {
                Log::critical('Role misconfiguration detected', [
                    'email' => $request->email,
                    'endpoint' => $request->path(),
                    'role' => $role->value,
                    'spatie_role_name' => $spatieRoleName,
                ]);
                abort(500, 'System role misconfigured');
            }

            // Ensure user has Spatie role assigned (validates existence first)
            $user->ensureSpatieRole();

            // Create token with admin scope
            $tokenScope = $role->value; // 'admin'
            $token = $user->createToken('auth-token', [$tokenScope])->plainTextToken;

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
