<?php

namespace App\Http\Controllers\Api\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListMyStoresController extends Controller
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

        $stores = Store::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $stores->getCollection()->map(fn (Store $store): array => [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'phone_number' => $store->phone_number,
                'logo' => $store->logo,
                'description' => $store->description,
                'balance' => (float) $store->balance,
                'status' => $store->status,
            ])->values(),
            'next_page' => $stores->hasMorePages() ? $stores->currentPage() + 1 : null,
        ]);
    }
}
