<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class RefreshController extends Controller
{
    /**
     * Refresh the product to bump it to the top of the feed.
     */
    public function __invoke(Product $product): JsonResponse
    {
        // Check if user owns the product (through store)
        if ($product->store->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $now = Carbon::now();
        
        // Cooldown period: 24 hours
        if ($product->refreshed_at && $product->refreshed_at->addDay() > $now) {
            $nextRefresh = $product->refreshed_at->addDay();
            return response()->json([
                'message' => 'Cooldown: You can refresh again in ' . $now->diffForHumans($nextRefresh, true),
                'next_refresh' => $nextRefresh->toDateTimeString(),
            ], 422);
        }

        // Update refreshed_at and created_at to bump it
        $product->update([
            'refreshed_at' => $now,
            'created_at' => $now,
        ]);

        return response()->json([
            'message' => 'Product refreshed successfully!',
            'refreshed_at' => $now->toDateTimeString(),
        ]);
    }
}
