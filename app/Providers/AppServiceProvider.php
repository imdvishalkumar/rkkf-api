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
            $user = \App\Models\User::where('user_id', $accessToken->tokenable_id)->first();

            \Illuminate\Support\Facades\Log::info('Sanctum Token Resolution', [
                'token_id' => $accessToken->id,
                'tokenable_id' => $accessToken->tokenable_id,
                'is_valid' => $isValid,
                'user_found' => !is_null($user),
                'user_id' => $user?->user_id,
                'user_role' => $user?->role?->value,
            ]);

            return $user;
        });
    }
}
