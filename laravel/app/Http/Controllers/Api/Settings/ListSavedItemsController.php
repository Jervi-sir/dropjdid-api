<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\SavedDrop;
use App\Models\SavedProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListSavedItemsController extends Controller
{
    public function listProducts(Request $request): JsonResponse
    {
        $user = $request->user();

        $savedProducts = SavedProduct::with(['product' => function ($query) {
            $query->with(['images', 'store.user', 'savedProducts']);
        }])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));

        return response()->json([
            'data' => $savedProducts->map(function ($saved) use ($user) {
                return $saved->product->formatProduct($saved->product, $user);
            }),
            'next_page' => $savedProducts->currentPage() < $savedProducts->lastPage() ? $savedProducts->currentPage() + 1 : null,
        ]);
    }

    public function listDrops(Request $request): JsonResponse
    {
        $user = $request->user();

        $savedDrops = SavedDrop::with(['drop' => function ($query): void {
            $query->withCount(['likedDrops', 'products', 'savedDrops']);
        }])
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate($request->get('per_page', 10));

        $drops = $savedDrops->pluck('drop')->filter();
        Drop::loadFeedRelations($drops, $user?->id);

        return response()->json([
            'data' => $savedDrops->map(function ($saved) use ($user) {
                return $saved->drop->formatDrop($user);
            }),
            'next_page' => $savedDrops->currentPage() < $savedDrops->lastPage() ? $savedDrops->currentPage() + 1 : null,
        ]);
    }
}
