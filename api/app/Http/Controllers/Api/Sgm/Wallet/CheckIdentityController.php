<?php

namespace App\Http\Controllers\Api\Sgm\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CheckIdentityController extends Controller
{
    /**
     * Verify the authenticated user's password for the store wallet.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            $userId = $request->input('user_id') ?? $request->header('X-User-Id');
            if ($userId) {
                $user = User::find($userId);
            }
        }

        $storeId = $request->input('store_id') ?? $request->header('X-Store-Id');
        if ($storeId && ! $user) {
            $store = Store::find($storeId);
            if ($store) {
                $user = $store->user;
            }
        }

        if (! $user) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Verify store ownership if store_id is provided
        if ($storeId) {
            $isOwner = Store::where('id', $storeId)->where('user_id', $user->id)->exists();
            if (! $isOwner) {
                return response()->json([
                    'success' => false,
                    'valid' => false,
                    'message' => 'You do not have permission to access this store wallet.',
                ], 403);
            }
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
            'store_id' => $storeId ? (int) $storeId : null,
            'level' => $storeId ? 'store' : 'user',
            'message' => 'Identity verified successfully.',
        ], 200);
    }
}
