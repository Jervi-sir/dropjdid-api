<?php

namespace App\Http\Controllers\Api\Drops;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\Drop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'filter' => ['nullable', 'string', 'in:for_you,creators_i_follow,trending'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $drops = Drop::query()
            ->where('status', 'published')
            ->withCount('likedDrops')
            ->with([
                'creator',
                'images',
                'products.store.user',
                'products.images',
                'products' => function ($query) use ($userId): void {
                    if ($userId !== null) {
                        $query->with([
                            'savedProducts' => fn ($saveQuery) => $saveQuery->where('user_id', $userId),
                        ]);
                    }
                },
                'likedDrops' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
                'savedDrops' => function ($query) use ($userId) {
                    return $userId === null
                        ? $query->whereRaw('1 = 0')
                        : $query->where('user_id', $userId);
                },
            ])
            ->latest()
            ->simplePaginate($perPage);

        return response()->json([
            'data' => Advertisement::injectIntoFeed(
                $drops->getCollection()->map(fn (Drop $drop): array => $drop->formatDrop($user)),
            )->values(),
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
        ]);
    }

    private function formatAdvertisement(Advertisement $advertisement): array
    {
        return [
            'type' => 'advertisement',
            'id' => $advertisement->id,
            'title' => $advertisement->title,
            'image' => $advertisement->image,
            'url' => $advertisement->url,
        ];
    }
}
