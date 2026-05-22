<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
            'full_name' => trim((string) $request->input('full_name')),
        ]);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'full_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $role = Role::query()->firstOrCreate(
            ['code' => 'user'],
            [
                'en' => 'User',
                'fr' => 'Utilisateur',
                'ar' => 'مستخدم',
            ]
        );

        $user = User::query()->create([
            'username' => $validated['username'],
            'full_name' => $validated['full_name'],
            'password' => $validated['password'],
            'password_plaintext' => $validated['password'],
        ]);

        $user->roles()->syncWithoutDetaching([$role->id]);

        $user->load('roles');

        return response()->json([
            'token' => $user->createToken('mobile-app')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }
}
