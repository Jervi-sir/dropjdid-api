<?php

namespace App\Http\Controllers\Api\MyAccount;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UsernameController extends Controller
{
    /**
     * Get the current user's username.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->query('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $rawUsername = (string) ($user->username ?? '');
        $formattedUsername = $rawUsername !== '' ? ('@'.ltrim($rawUsername, '@')) : '';

        return response()->json([
            'id' => (int) $user->id,
            'username' => $rawUsername,
            'formatted_username' => $formattedUsername,
        ], 200);
    }

    /**
     * Change / update current user's username.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->input('user_id') ?? $request->query('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Accept 'username' or 'new_username'
        $rawUsername = $request->input('username') ?? $request->input('new_username');

        // Strip leading '@' and trim whitespace
        $cleanUsername = is_string($rawUsername) ? ltrim(trim($rawUsername), '@') : $rawUsername;

        $validator = Validator::make([
            'username' => $cleanUsername,
        ], [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9._]+$/',
                Rule::unique(User::class, 'username')->ignore($user->id),
            ],
        ], [
            'username.required' => 'The username field is required.',
            'username.string' => 'The username must be a string.',
            'username.min' => 'The username must be at least 3 characters.',
            'username.max' => 'The username may not be greater than 30 characters.',
            'username.regex' => 'Username can only contain letters, numbers, dots, and underscores.',
            'username.unique' => 'The username has already been taken.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $normalizedUsername = strtolower($cleanUsername);

        // Update user
        $user->username = $normalizedUsername;
        $user->save();

        // Keep any existing UserContact record of type 'username' in sync
        UserContact::where('user_id', $user->id)
            ->where('type', 'username')
            ->update([
                'value' => '@'.$normalizedUsername,
            ]);

        $formattedUsername = '@'.$normalizedUsername;

        return response()->json([
            'message' => 'Username updated successfully.',
            'username' => $normalizedUsername,
            'formatted_username' => $formattedUsername,
            'data' => [
                'id' => (int) $user->id,
                'username' => $normalizedUsername,
                'formatted_username' => $formattedUsername,
                'name' => (string) ($user->full_name ?? $user->name ?? $normalizedUsername),
            ],
        ], 200);
    }
}
