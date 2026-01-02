<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if user exists and has admin role (using config instead of hard-coding)
        $adminRole = config('roles.roles.admin', 1);
        $user = User::where('email', $credentials['email'])
            ->where('role', $adminRole)
            ->first();

        if ($user) {
            // Support both plain text (existing system) and hashed passwords
            $passwordMatch = false;
            
            // Check plain text password (for existing system compatibility)
            if ($user->password === $credentials['password']) {
                $passwordMatch = true;
            }
            // Check hashed password (for new Laravel system)
            elseif (Hash::check($credentials['password'], $user->password)) {
                $passwordMatch = true;
            }
            
            if ($passwordMatch) {
                Auth::login($user);
                $request->session()->regenerate();

                // Handle special email redirects using config (replaces hard-coded emails)
                $specialUsers = config('roles.special_users', []);
                if (isset($specialUsers[$credentials['email']])) {
                    $redirectRoute = $specialUsers[$credentials['email']]['redirect_route'] ?? null;
                    if ($redirectRoute && \Route::has($redirectRoute)) {
                        return redirect()->route($redirectRoute);
                    }
                }

                // Default redirect based on role
                $defaultRoute = config('roles.default_routes.admin', 'dashboard');
                return redirect()->intended(route($defaultRoute));
            }
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
