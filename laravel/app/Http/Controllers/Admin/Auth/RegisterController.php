<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('auth/register');
    }

    /**
     * Handle the registration request.
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Generate a unique username slug
        $username = Str::slug($request->name) . '-' . Str::random(4);

        $admin = Admin::create([
            'full_name' => $request->name,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
            'password_plaintext' => $request->password,
        ]);

        Auth::guard('web')->login($admin);

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }
}
