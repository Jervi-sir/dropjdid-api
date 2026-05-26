<?php

namespace App\Http\Controllers\Api\Sgm\Stores\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CalculatePrizeController extends Controller
{
    public function __invoke(Request $request, $store_id = null)
    {
        $validated = $request->validate([
            'original_price' => 'required|numeric|min:0',
            'has_creator' => 'nullable|boolean',
            'creator_id' => 'nullable|exists:users,id',
        ]);

        $originalPrice = floatval($validated['original_price']);
        
        // Handle has_creator support for different query formats (e.g. string "true")
        $hasCreator = filter_var($request->input('has_creator', false), FILTER_VALIDATE_BOOLEAN);

        if ($request->filled('creator_id')) {
            $hasCreator = true;
        }

        $showPrice = $originalPrice * 1.30;
        $storePrice = $originalPrice;
        $octaprizeShare = $originalPrice * 0.30;

        if ($hasCreator) {
            $creatorShare = $originalPrice * 0.15;
            $octaprizeAfterCreator = $originalPrice * 0.15;
        } else {
            $creatorShare = 0.0;
            $octaprizeAfterCreator = $originalPrice * 0.30;
        }

        return response()->json([
            'original_price' => $originalPrice,
            'show_price' => $showPrice,
            'store_price' => $storePrice,
            'octaprize_share' => $octaprizeShare,
            'creator_share' => $creatorShare,
            'octaprize_after_creator' => $octaprizeAfterCreator,
        ]);
    }
}
