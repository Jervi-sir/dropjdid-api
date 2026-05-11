<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Store;

class GetStorePreviewController extends Controller
{
    /**
     * Get basic info for a store.
     */
    public function __invoke(Store $store)
    {
        return response()->json([
            'id' => $store->id,
            'store_name' => $store->store_name,
            'logo' => $store->logo,
        ]);
    }
}
