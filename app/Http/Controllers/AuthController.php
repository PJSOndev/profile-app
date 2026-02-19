<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();

            return $user && strtolower((string) $user->role) === 'user'
                ? redirect()->route('profile.show')
                : redirect()->route('dashboard');
        }

        return view('pages.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        $status = strtolower((string) $user->status);

        if ($status === 'suspended') {
            throw ValidationException::withMessages([
                'email' => 'Your account is suspended. Please contact an administrator.',
            ]);
        }

        if ($status !== 'approved') {
            throw ValidationException::withMessages([
                'email' => 'Your account is not approved yet.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $defaultRoute = strtolower((string) $user->role) === 'user'
            ? route('profile.show')
            : route('dashboard');

        return redirect()->intended($defaultRoute);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
