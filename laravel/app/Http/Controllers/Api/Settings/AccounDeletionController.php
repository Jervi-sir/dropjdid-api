<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccounDeletionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        // Create deletion request in support requests
        UserSupportRequest::create([
            'user_id' => $user->id,
            'contact' => $user->username ?? $user->email ?? $user->phone_number ?? 'unknown',
            'type' => UserSupportRequest::TYPE_USERNAME,
            'status' => UserSupportRequest::STATUS_PENDING,
            'note' => 'User requested account deletion.',
            'target' => UserSupportRequest::TARGET_DELETE_ACCOUNT,
        ]);

        // Soft delete the user
        $user->delete();

        return response()->json([
            'message' => 'Account scheduled for deletion successfully.',
        ]);
    }
}
