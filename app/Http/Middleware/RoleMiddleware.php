<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

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
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Get role from config instead of hard-coding
        $roles = config('roles.roles', []);
        $requiredRole = $roles[$role] ?? null;
        
        if ($requiredRole === null) {
            abort(500, "Invalid role specified: {$role}");
        }

        // Check role using enum
        $userRole = UserRole::fromValue($user->role);
        $requiredRoleEnum = UserRole::fromValue($requiredRole);
        
        if ($userRole !== $requiredRoleEnum) {
            abort(403, 'Unauthorized access. Required role: ' . $requiredRoleEnum->label());
        }

        return $next($request);
    }
}
