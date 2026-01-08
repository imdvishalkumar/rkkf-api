<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Laravel\Sanctum\Sanctum::usePersonalAccessTokenModel(\Laravel\Sanctum\PersonalAccessToken::class);

        \Laravel\Sanctum\Sanctum::authenticateAccessTokensUsing(function ($accessToken, $isValid) {
            // Only return user if token is valid
            if (!$isValid) {
                \Illuminate\Support\Facades\Log::warning('Sanctum Token Invalid', [
                    'token_id' => $accessToken->id,
                    'tokenable_id' => $accessToken->tokenable_id,
                    'tokenable_type' => $accessToken->tokenable_type,
                ]);
                return null;
            }

            // Check if tokenable_type matches User model
            if ($accessToken->tokenable_type !== \App\Models\User::class) {
                \Illuminate\Support\Facades\Log::warning('Sanctum Token: Invalid tokenable_type', [
                    'token_id' => $accessToken->id,
                    'tokenable_id' => $accessToken->tokenable_id,
                    'tokenable_type' => $accessToken->tokenable_type,
                    'expected_type' => \App\Models\User::class,
                ]);
                return null;
            }

            $user = \App\Models\User::where('user_id', $accessToken->tokenable_id)->first();

            if (!$user) {
                \Illuminate\Support\Facades\Log::warning('Sanctum Token: User not found', [
                    'token_id' => $accessToken->id,
                    'tokenable_id' => $accessToken->tokenable_id,
                ]);
                return null;
            }

            \Illuminate\Support\Facades\Log::info('Sanctum Token Resolution', [
                'token_id' => $accessToken->id,
                'tokenable_id' => $accessToken->tokenable_id,
                'is_valid' => $isValid,
                'user_found' => !is_null($user),
                'user_id' => $user->user_id,
                'user_role' => $user->role?->value,
            ]);

            return $user;
        });
    }
}
