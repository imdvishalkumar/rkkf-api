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
            return ApiResponseHelper::unauthorized('Authentication required');
        }

        if (!$user->hasAnyRole($roles)) {
            return ApiResponseHelper::forbidden('Unauthorized access. Required role: ' . implode(', ', $roles));
        }

        return $next($request);
    }
}
