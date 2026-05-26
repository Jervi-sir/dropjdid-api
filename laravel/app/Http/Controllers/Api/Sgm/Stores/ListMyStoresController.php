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

        $stores = Store::with('wilaya')
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->simplePaginate($perPage);


        return response()->json([
            'data' => collect($stores->items())->map(fn(Store $item): array => [
                'id' => $item->id,
                'wilaya_id' => $item->wilaya_id,
                'store_name' => $item->store_name,
                'phone_number' => $item->phone_number,
                'logo' => $item->logo,
                'status' => $item->status_details,
                'is_verified' => $item->is_verified,
                'wilaya' => $item->wilaya ? [
                    'id' => $item->wilaya->id,
                    'code' => $item->wilaya->code,
                    'number' => $item->wilaya->number,
                    'en' => $item->wilaya->en,
                    'fr' => $item->wilaya->fr,
                    'ar' => $item->wilaya->ar,
                ] : null,
            ])->values(),
            'next_page' => $stores->hasMorePages() ? $stores->currentPage() + 1 : null,
        ]);
    }
}
