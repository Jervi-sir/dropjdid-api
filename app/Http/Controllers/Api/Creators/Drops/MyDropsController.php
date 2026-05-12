<?php

namespace App\Http\Controllers\Api\Creators\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyDropsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $user = $request->user();
        $userId = $user->id;

        $drops = Drop::query()
            ->where('creator_id', $userId)
            ->withCount('likedDrops')
            ->with([
                'creator',
                'images',
                'products.store.user',
                'products.images',
                'products' => function ($query) use ($userId): void {
                    $query->with([
                        'savedProducts' => fn ($saveQuery) => $saveQuery->where('user_id', $userId),
                    ]);
                },
                'likedDrops' => function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                },
                'savedDrops' => function ($query) use ($userId) {
                    return $query->where('user_id', $userId);
                },
            ])
            ->latest()
            ->paginate($perPage);

        $formattedDrops = collect($drops->items())
            ->map(fn (Drop $drop): array => array_merge($drop->formatDrop($user), [
                'status' => $drop->status,
            ]));

        return response()->json([
            'data' => $formattedDrops,
            'total' => $drops->total(),
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
        ]);
    }
}
