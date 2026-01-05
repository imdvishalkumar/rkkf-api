<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;
use App\Helpers\ApiResponseHelper;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Usage: Route::middleware(['role:admin'])->group(...)
     *        Route::middleware(['role:instructor'])->group(...)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Check authentication for both web and API
        $user = auth('sanctum')->user() ?? auth()->user();
        
        if (!$user) {
            // If API request, return JSON error; otherwise redirect
            if ($request->expectsJson() || $request->is('api/*')) {
                return ApiResponseHelper::unauthorized('Authentication required');
            }
            return redirect()->route('login');
        }
        
        // Get role from config instead of hard-coding
        $roles = config('roles.roles', []);
        $requiredRole = $roles[$role] ?? null;
        
        if ($requiredRole === null) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ApiResponseHelper::error("Invalid role specified: {$role}", 500);
            }
            abort(500, "Invalid role specified: {$role}");
        }

        // Check role using enum - handle both enum and integer values
        $userRoleValue = $user->role instanceof UserRole ? $user->role->value : (int)$user->role;
        $userRole = UserRole::tryFrom($userRoleValue);
        $requiredRoleEnum = UserRole::tryFrom($requiredRole);
        
        if (!$userRole || !$requiredRoleEnum || $userRole !== $requiredRoleEnum) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ApiResponseHelper::forbidden('Unauthorized access. Required role: ' . ($requiredRoleEnum?->label() ?? $role));
            }
            abort(403, 'Unauthorized access. Required role: ' . ($requiredRoleEnum?->label() ?? $role));
        }

        return $next($request);
    }
}
