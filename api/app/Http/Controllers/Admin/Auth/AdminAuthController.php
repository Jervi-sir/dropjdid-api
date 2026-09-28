<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthController extends Controller
{
    /**
     * Display the admin login view.
     */
    public function showLogin(): Response
    {
        return Inertia::render('admin/auth/login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle admin login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $login = trim($validated['login']);
        $password = $validated['password'];
        $remember = (bool) ($validated['remember'] ?? false);

        // Find admin by username, email, or phone_number
        $admin = Admin::where('username', $login)
            ->orWhere('email', $login)
            ->orWhere('phone_number', $login)
            ->first();

        if (! $admin || ! Hash::check($password, $admin->password)) {
            throw ValidationException::withMessages([
                'login' => [__('auth.failed')],
            ]);
        }

        if (! $admin->is_approved) {
            throw ValidationException::withMessages([
                'login' => ['Your admin account is pending approval or inactive.'],
            ]);
        }

        Auth::guard('admin')->login($admin, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.creators.requests.index'));
    }

    /**
     * Destroy admin session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
