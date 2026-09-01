<?php

namespace App\Http\Controllers\Api\Creator\Wallet;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CheckIdentityController extends Controller
{
    /**
     * Verify the authenticated creator's password for wallet access.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->input('user_id') ?? $request->header('X-User-Id') ?? $request->query('user_id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        if (! $user) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $password = $request->input('password') ?? $request->input('current_password');

        $validator = Validator::make(
            ['password' => $password],
            ['password' => ['required', 'string']],
            [
                'password.required' => 'The password field is required.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $isValid = Hash::check((string) $password, $user->password);

        if (! $isValid) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'The provided password is incorrect.',
                'errors' => [
                    'password' => ['The provided password is incorrect.'],
                ],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'valid' => true,
            'user_id' => (int) $user->id,
            'message' => 'Identity verified successfully.',
        ], 200);
    }
}
