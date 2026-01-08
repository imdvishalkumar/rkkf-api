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

        \Illuminate\Support\Facades\Log::info('RoleMiddleware check', [
            'user_id' => $user->user_id,
            'user_role_attribute' => $user->role instanceof \App\Enums\UserRole ? $user->role->value : $user->role,
            'spatie_roles' => $user->getRoleNames(),
            'required_roles' => $roles,
        ]);

        if (!$user->hasAnyRole($roles)) {
            \Illuminate\Support\Facades\Log::warning('RoleMiddleware: Unauthorized', [
                'user_id' => $user->user_id,
                'required' => $roles
            ]);
            return ApiResponseHelper::forbidden('Unauthorized access. Required role: ' . implode(', ', $roles));
        }

        return $next($request);
    }
}
