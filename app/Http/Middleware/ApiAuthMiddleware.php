<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ApiResponseHelper;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * This middleware handles API authentication using Sanctum tokens.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via Sanctum token
        if (!auth('sanctum')->check()) {
            return ApiResponseHelper::unauthorized('Authentication required. Please provide a valid token.');
        }

        return $next($request);
    }
}


