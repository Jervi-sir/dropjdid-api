<?php

namespace App\Http\Controllers\Api\Profiles;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListDropsController extends Controller
{
    public function __invoke(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $authUser = $request->user();
        $authUserId = $authUser?->id;

        $drops = $user->drops()
            ->where('status', Drop::STATUS_PUBLISHED)
            ->withCount(['likedDrops', 'products', 'savedDrops'])
            ->with([
                'creator',
                'images',
                'products.store.user',
                'products.images',
                'products' => function ($query) use ($authUserId): void {
                    if ($authUserId !== null) {
                        $query->with([
                            'savedProducts' => fn ($saveQuery) => $saveQuery->where('user_id', $authUserId),
                        ]);
                    }
                },
                'likedDrops' => function ($query) use ($authUserId) {
                    return $authUserId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $authUserId);
                },
                'savedDrops' => function ($query) use ($authUserId) {
                    return $authUserId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $authUserId);
                },
            ])
            ->latest()
            ->simplePaginate($perPage);

        $formattedDrops = collect($drops->items())
            ->map(fn (Drop $drop): array => $drop->formatDrop($authUser));

        return response()->json([
            'data' => $formattedDrops,
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
        ]);
    }
}
