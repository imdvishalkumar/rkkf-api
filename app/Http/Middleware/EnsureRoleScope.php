<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\UserRole;

class EnsureRoleScope
{
    /**
     * Handle an incoming request.
     * 
     * Validates that:
     * 1. Token scope matches the user's role
     * 2. Spatie role exists and matches token scope
     * 
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Get token from request
        $token = $request->user()->currentAccessToken();
        
        if (!$token) {
            Log::warning('RBAC violation: No token found', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'endpoint' => $request->path(),
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Invalid token.'
            ], 403);
        }

        // Get token abilities (scopes)
        // Sanctum stores abilities as JSON, access via abilities attribute or tokenCan method
        $tokenAbilities = is_array($token->abilities) ? $token->abilities : [];
        
        // Fallback: if abilities is not an array, try to decode it
        if (empty($tokenAbilities) && !empty($token->abilities)) {
            $tokenAbilities = json_decode($token->abilities, true) ?? [];
        }
        
        // Get user's role from database
        $userRole = $user->role;
        
        if (!$userRole instanceof UserRole) {
            Log::critical('Role misconfiguration detected', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'role_value' => $user->role,
                'endpoint' => $request->path(),
            ]);
            
            abort(500, 'System role misconfigured');
        }

        // Expected scope based on role
        $expectedScope = $userRole->value;
        
        // Validate token scope matches role
        if (!in_array($expectedScope, $tokenAbilities) && !in_array('*', $tokenAbilities)) {
            Log::warning('RBAC violation: Token scope mismatch', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'endpoint' => $request->path(),
                'user_role' => $userRole->value,
                'token_scopes' => $tokenAbilities,
                'expected_scope' => $expectedScope,
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'Token scope does not match user role.'
            ], 403);
        }

        // Validate Spatie role exists and matches
        $spatieRoleName = $userRole->spatieRole();
        
        try {
            $spatieRole = \Spatie\Permission\Models\Role::where('name', $spatieRoleName)->first();
            
            if (!$spatieRole) {
                Log::critical('Role misconfiguration detected: Spatie role missing', [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                    'expected_spatie_role' => $spatieRoleName,
                    'user_role' => $userRole->value,
                    'endpoint' => $request->path(),
                ]);
                
                abort(500, 'System role misconfigured');
            }

            // Verify user has the Spatie role assigned
            if (!$user->hasRole($spatieRoleName)) {
                Log::warning('RBAC violation: User missing Spatie role', [
                    'user_id' => $user->user_id,
                    'email' => $user->email,
                    'endpoint' => $request->path(),
                    'expected_spatie_role' => $spatieRoleName,
                    'user_role' => $userRole->value,
                    'token_scope' => $expectedScope,
                ]);
                
                return response()->json([
                    'status' => false,
                    'message' => 'User role assignment mismatch.'
                ], 403);
            }
            
            // Note: Token scope must match UserRole value (user/instructor/admin)
            // Spatie role name can be different (e.g., 'student' for 'user' role)
            // This is already validated above by checking token scope matches expectedScope
            
        } catch (\Exception $e) {
            Log::critical('Role misconfiguration detected: Exception during validation', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'endpoint' => $request->path(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            abort(500, 'System role misconfigured');
        }

        return $next($request);
    }
}
