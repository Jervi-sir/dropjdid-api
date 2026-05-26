<?php

namespace App\Http\Controllers\Api\Creators\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\SavedDrop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListSavesController extends Controller
{
    public function __invoke(Request $request, int $drop_id): JsonResponse
    {
        $drop = Drop::findOrFail($drop_id);

        $saves = SavedDrop::query()
            ->where('drop_id', $drop->id)
            ->with('user')
            ->latest('id')
            ->simplePaginate(20);

        return response()->json([
            'status' => 'success',
            'data' => collect($saves->items())->map(function (SavedDrop $save): array {
                return [
                    'id' => $save->id,
                    'user_id' => $save->user?->id,
                    'name' => $save->user?->full_name ?? $save->user?->username,
                    'username' => $save->user?->username,
                    'image' => $save->user?->image,
                ];
            })->values(),
            'next_page' => $saves->hasMorePages() ? $saves->currentPage() + 1 : null,
            'total' => $drop->savedDrops()->count(),
        ]);
    }
}
