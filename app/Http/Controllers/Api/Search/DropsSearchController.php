<?php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropsSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = trim($validated['query']);

        if ($query === '') {
            return response()->json([
                'data' => [],
                'next_page' => null,
            ]);
        }

        $perPage = $validated['per_page'] ?? 10;
        $user = $request->user();
        $userId = $user?->getAuthIdentifier();

        $drops = Drop::query()
            ->where('status', 'published')
            ->whereHas('creator')
            ->where(function (Builder $builder) use ($query): void {
                $builder
                    ->where('title', 'like', '%'.$query.'%')
                    ->orWhereHas('products.keywords', function (Builder $keywordQuery) use ($query): void {
                        $keywordQuery
                            ->where('code', 'like', '%'.$query.'%')
                            ->orWhereHas('label', function (Builder $labelQuery) use ($query): void {
                                $labelQuery
                                    ->where('code', 'like', '%'.$query.'%')
                                    ->orWhere('en', 'like', '%'.$query.'%')
                                    ->orWhere('fr', 'like', '%'.$query.'%')
                                    ->orWhere('ar', 'like', '%'.$query.'%');
                            });
                    })
                    ->orWhereHas('products.productKeywords.label', function (Builder $labelQuery) use ($query): void {
                        $labelQuery
                            ->where('code', 'like', '%'.$query.'%')
                            ->orWhere('en', 'like', '%'.$query.'%')
                            ->orWhere('fr', 'like', '%'.$query.'%')
                            ->orWhere('ar', 'like', '%'.$query.'%');
                    });
            })
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
            'data' => collect($drops->items())->map(fn (Drop $drop): array => $drop->formatDrop($user))->values(),
            'next_page' => $drops->hasMorePages() ? $drops->currentPage() + 1 : null,
        ]);
    }
}
