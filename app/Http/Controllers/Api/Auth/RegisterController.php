<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
        ]);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'phone_number' => ['required', 'string', 'max:255', 'unique:users,phone_number'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $role = Role::query()->firstOrCreate(
            ['code' => 'user'],
            ['en' => 'User', 'fr' => 'Utilisateur', 'ar' => 'User']
        );

        $user = User::query()->create([
            'role_id' => $role->id,
            'username' => $validated['username'],
            'phone_number' => $validated['phone_number'],
            'password' => $validated['password'],
        ]);

        return response()->json([
            'token' => $user->createToken('mobile-app')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ], 201);
    }
}
