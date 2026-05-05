<?php

namespace App\Http\Controllers\Api\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\LikedDrop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function __invoke(Request $request, Drop $drop): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $like = LikedDrop::query()->where([
            'user_id' => $user->id,
            'drop_id' => $drop->id,
        ])->first();

        if ($like !== null) {
            $like->delete();

            return response()->json([
                'message' => 'Drop unliked successfully.',
                'is_liked' => false,
                'nb_likes' => $drop->likedDrops()->count(),
            ]);
        }

        LikedDrop::query()->create([
            'user_id' => $user->id,
            'drop_id' => $drop->id,
        ]);

        return response()->json([
            'message' => 'Drop liked successfully.',
            'is_liked' => true,
            'nb_likes' => $drop->likedDrops()->count(),
        ]);
    }
}
