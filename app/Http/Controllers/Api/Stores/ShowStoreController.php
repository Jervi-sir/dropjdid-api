<?php

namespace App\Http\Controllers\Api\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowStoreController extends Controller
{
    public function __invoke(Request $request, Store $store): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_unless($store->user_id === $user->id, 404);

        $store->load('wilaya');

        return response()->json([
            'data' => [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'phone_number' => $store->phone_number,
                'status' => $store->status,
                'wilaya' => [
                    'id' => $store->wilaya?->id,
                    'code' => $store->wilaya?->code,
                    'number' => $store->wilaya?->number,
                    'name' => $store->wilaya?->en,
                ],
            ],
        ]);
    }
}
