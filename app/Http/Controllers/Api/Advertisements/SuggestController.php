<?php

namespace App\Http\Controllers\Api\Advertisements;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuggestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $advertisements = Advertisement::query()
            ->activeForFeed()
            ->inRandomOrder()
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $advertisements->getCollection()->map(
                fn (Advertisement $advertisement): array => $advertisement->toFeedArray()
            )->values(),
            'next_page' => $advertisements->hasMorePages() ? $advertisements->currentPage() + 1 : null,
        ]);
    }
}
