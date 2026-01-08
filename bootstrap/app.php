<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'role.scope' => \App\Http\Middleware\EnsureRoleScope::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            \Illuminate\Support\Facades\Log::warning('AuthenticationException caught', [
                'path' => $request->path(),
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        });
        
        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            \Illuminate\Support\Facades\Log::warning('AuthorizationException caught', [
                'path' => $request->path(),
                'message' => $e->getMessage(),
                'user' => $request->user()?->user_id,
            ]);
            return response()->json([
                'status' => false,
                'message' => 'This action is unauthorized.'
            ], 403);
        });
    })->create();
