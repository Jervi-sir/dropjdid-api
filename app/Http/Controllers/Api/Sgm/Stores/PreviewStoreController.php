<?php

namespace App\Http\Controllers\Api\Sgm\Stores;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreviewStoreController extends Controller
{
    public function __invoke(Request $request, $id): JsonResponse
    {
        $store = Store::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'store_name' => $store->store_name,
                'phone_number' => $store->phone_number,
            ],
        ]);
    }
}
