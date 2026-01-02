<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

class BranchAccessMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * This middleware checks if the user has access to the requested branch.
     * Admin users have access to all branches.
     * Other users can only access their assigned branch.
     * 
     * Usage: Route::middleware(['branch.access'])->group(...)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Admin users have access to all branches
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Get branch_id from request (route parameter, query, or body)
        $branchId = $request->route('branch_id') 
                 ?? $request->input('branch_id') 
                 ?? $request->query('branch_id');

        // If no branch_id specified, allow access (will be filtered in repository)
        if (!$branchId) {
            return $next($request);
        }

        // Check if user has access to this branch
        $userBranchId = $user->branch_id ?? null;
        
        if ($userBranchId && $userBranchId != $branchId) {
            abort(403, 'You do not have access to this branch.');
        }

        return $next($request);
    }
}



