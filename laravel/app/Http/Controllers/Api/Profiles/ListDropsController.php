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
            ->latest()
            ->simplePaginate($perPage);

        Drop::loadFeedRelations($drops, $authUserId);

        $formattedDrops = collect($drops->items())
            ->map(fn (Drop $drop): array => $drop->formatDrop($authUser));

        return response()->json([
            'data' => $formattedDrops,
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
        ]);
    }
}
