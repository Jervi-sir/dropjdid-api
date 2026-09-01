<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Handle user registration and issue bearer token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9._]+$/',
                'unique:users,username',
            ],
            'name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(6)],
        ], [
            'username.unique' => 'This username is already taken.',
            'username.regex' => 'Username can only contain letters, numbers, dots, and underscores.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $username = strtolower(trim($request->input('username')));
        $fullName = $request->input('full_name') ?? $request->input('name') ?? $username;
        $email = $request->input('email') ?? "{$username}@djapp.local";

        $user = User::create([
            'username' => $username,
            'full_name' => $fullName,
            'email' => $email,
            'password' => Hash::make($request->input('password')),
            'is_active' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user->toAuthArray(),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }
}
