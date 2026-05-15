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
            'phone_number' => trim((string) $request->input('phone_number')),
        ]);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'phone_number' => ['required', 'string', 'max:255', 'unique:users,phone_number'],
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
            'phone_number' => $validated['phone_number'],
            'password' => $validated['password'],
            'password_platintext' => $validated['password'],
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
