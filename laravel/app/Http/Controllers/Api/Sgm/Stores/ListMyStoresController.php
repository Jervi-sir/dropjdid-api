<?php

namespace App\Http\Controllers\Api\Sgm\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListMyStoresController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 10);

        $stores = Store::where('user_id', $request->user()->id)
            ->latest()
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $stores->items(),
            'next_page' => $stores->hasMorePages() ? $stores->currentPage() + 1 : null,
        ]);

    }
}
