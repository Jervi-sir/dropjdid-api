<?php

namespace App\Http\Controllers\Api\Creators\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\LikedDrop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListLikesController extends Controller
{
    public function __invoke(Request $request, $drop_id): JsonResponse
    {
        $drop = Drop::findOrFail($drop_id);

        $likes = LikedDrop::query()
            ->where('drop_id', $drop->id)
            ->with('user')
            ->latest('id')
            ->simplePaginate(20);

        return response()->json([
            'status' => 'success',
            'data' => collect($likes->items())->map(function (LikedDrop $like): array {
                return [
                    'id' => $like->id,
                    'user_id' => $like->user?->id,
                    'name' => $like->user?->username,
                    'username' => $like->user?->username,
                    'image' => $like->user?->image,
                ];
            })->values(),
            'next_page' => $likes->hasMorePages() ? $likes->currentPage() + 1 : null,
            'total' => $drop->likedDrops()->count(),
        ]);
    }
}
