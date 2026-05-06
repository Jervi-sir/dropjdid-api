<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\SavedDrop;
use App\Models\SavedProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyProfileController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');

        $friendsCount = $user->sentFriendships()
            ->where('status', 'accepted')
            ->count()
            + $user->receivedFriendships()
                ->where('status', 'accepted')
                ->count();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->username,
                'username' => $user->username,
                'image' => $user->image,
                'role' => [
                    'id' => $user->role?->id,
                    'code' => $user->role?->code,
                    'name' => $user->role?->en,
                ],
                'stats' => [
                    'friends' => $friendsCount,
                    'followed_creators' => $user->followedCreators()->count(),
                    'saved' => SavedProduct::query()->where('user_id', $user->id)->count()
                        + SavedDrop::query()->where('user_id', $user->id)->count(),
                    'followers' => $user->followers()->count(),
                    'stores' => $user->stores()->count(),
                ],
            ],
        ]);
    }
}
