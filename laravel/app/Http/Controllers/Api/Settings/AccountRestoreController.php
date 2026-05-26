<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountRestoreController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        // Restore the user
        $user->restore();

        // Delete the support request for deletion
        UserSupportRequest::where('user_id', $user->id)
            ->where('target', UserSupportRequest::TARGET_DELETE_ACCOUNT)
            ->delete();

        return response()->json([
            'message' => 'Account restored successfully.',
            'user' => $user->load('roles'),
        ]);
    }
}
