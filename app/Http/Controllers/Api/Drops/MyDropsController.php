<?php

namespace App\Http\Controllers\Api\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyDropsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $userId = $user->getAuthIdentifier();

        $drops = Drop::query()
            ->where('creator_id', $userId)
            ->withCount('likedDrops')
            ->with([
                'creator',
                'images',
                'likedDrops' => fn ($query) => $query->where('user_id', $userId),
                'savedDrops' => fn ($query) => $query->where('user_id', $userId),
                'products.store.user',
                'products.images',
                'products' => function ($query) use ($userId): void {
                    $query->with([
                        'savedProducts' => fn ($savedProductsQuery) => $savedProductsQuery->where('user_id', $userId),
                    ]);
                },
            ])
            ->latest()
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $drops->getCollection()->map(fn (Drop $drop): array => $drop->formatDrop($user) + [
                'status' => $drop->status,
            ])->values(),
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
        ]);
    }
}
