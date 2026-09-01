<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Authenticate user with username/email and password and return Bearer token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['nullable', 'string'],
            'email' => ['nullable', 'string'],
            'login' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $loginIdentifier = $request->input('username')
            ?? $request->input('email')
            ?? $request->input('login');

        if (! $loginIdentifier) {
            return response()->json([
                'message' => 'Username or email is required.',
            ], 422);
        }

        $loginIdentifier = strtolower(trim($loginIdentifier));

        // Find user by username or email
        $user = User::where('username', $loginIdentifier)
            ->orWhere('email', $loginIdentifier)
            ->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (! $user->is_active || $user->user_status === 'blocked') {
            return response()->json([
                'message' => 'Your account is deactivated or blocked. Please contact support.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user->toAuthArray(),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Revoke current bearer token on logout.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 200);
    }
}
