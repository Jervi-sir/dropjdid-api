<?php

namespace App\Http\Controllers\Api\Profiles;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SendFollowCreatorController extends Controller
{
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        abort_if($authUser === null, 401);
        abort_if($authUser->id === $user->id, 422, 'You cannot follow yourself.');

        $existingFollow = CreatorFollower::query()
            ->where('user_id', $authUser->id)
            ->where('creator_id', $user->id)
            ->first();

        if ($existingFollow !== null) {
            $existingFollow->delete();

            return response()->json([
                'message' => 'Creator unfollowed successfully.',
                'is_following' => false,
            ]);
        }

        CreatorFollower::query()->create([
            'user_id' => $authUser->id,
            'creator_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Creator followed successfully.',
            'is_following' => true,
        ]);
    }
}
