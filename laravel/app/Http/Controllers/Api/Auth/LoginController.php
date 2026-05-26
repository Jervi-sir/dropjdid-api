<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->merge([
            'username' => strtolower(trim((string) $request->input('username'))),
        ]);

        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::withTrashed()
            ->with('roles')
            ->where(function ($query) use ($credentials) {
                $query->where('username', $credentials['username'])
                      ->orWhere('email', $credentials['username']);
            })
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->deleted_at !== null) {
            if ($user->deleted_at->addDays(30)->isPast()) {
                throw ValidationException::withMessages([
                    'username' => ['This account has been permanently deleted.'],
                ]);
            }
        }

        return response()->json([
            'token' => $user->createToken('mobile-app')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }
}
