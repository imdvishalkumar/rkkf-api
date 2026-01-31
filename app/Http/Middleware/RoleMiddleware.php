<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;
use App\Helpers\ApiResponseHelper;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            \Illuminate\Support\Facades\Log::warning('RoleMiddleware: No user found');
            return ApiResponseHelper::unauthorized('Authentication required');
        }

        // Map 'user' role to 'student' for Spatie check (DB check)
        // This allows using 'role:user' in routes which matches the 'user' scope/enum
        $spatieRoles = array_map(function ($role) {
            return ($role === 'user') ? 'student' : $role;
        }, $roles);

        // 1. Check DB Role (Spatie)
        if (!$user->hasAnyRole($spatieRoles)) {
            \Illuminate\Support\Facades\Log::warning('RoleMiddleware: Unauthorized (DB Role Mismatch)', [
                'user_id' => $user->user_id,
                'user_roles' => $user->getRoleNames(),
                'required_roles' => $roles,
                'mapped_spatie_roles' => $spatieRoles
            ]);
            return ApiResponseHelper::forbidden('Unauthorized access. Required role: ' . implode(', ', $roles));
        }

        // 2. Check Token Scope (Sanctum Ability)
        // Ensure the token itself has the permission/scope for the requested route role
        $token = $user->currentAccessToken();
        if ($token) {
            $hasScope = false;
            foreach ($roles as $role) {
                // $token->can() checks if the specific ability appears in the token's abilities list
                // or if the token has the '*' ability.
                if ($token->can($role)) {
                    $hasScope = true;
                    break;
                }
            }

            if (!$hasScope) {
                \Illuminate\Support\Facades\Log::warning('RoleMiddleware: Unauthorized (Token Scope Mismatch)', [
                    'user_id' => $user->user_id,
                    'token_abilities' => $token->abilities,
                    'required_scopes' => $roles
                ]);
                return ApiResponseHelper::forbidden('Access denied. Token invalid for this area.');
            }
        }

        return $next($request);
    }
}
