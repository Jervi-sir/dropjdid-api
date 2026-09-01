<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsernameAvailabilityController extends Controller
{
    /**
     * Check if a username is available.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-zA-Z0-9._]+$/'],
        ], [
            'username.regex' => 'Username can only contain letters, numbers, dots, and underscores.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'available' => false,
                'message' => $validator->errors()->first('username'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $username = strtolower(trim($request->input('username')));

        $isTaken = User::where('username', $username)->exists();

        if ($isTaken) {
            return response()->json([
                'available' => false,
                'message' => 'Username is already taken.',
            ], 200);
        }

        return response()->json([
            'available' => true,
            'message' => 'Username is available.',
        ], 200);
    }
}
