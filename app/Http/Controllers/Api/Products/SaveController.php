<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SavedProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaveController extends Controller
{
    public function __invoke(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $save = SavedProduct::query()->where([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ])->first();

        if ($save !== null) {
            $save->delete();

            return response()->json([
                'message' => 'Product unsaved successfully.',
                'is_saved' => false,
            ]);
        }

        SavedProduct::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'message' => 'Product saved successfully.',
            'is_saved' => true,
        ]);
    }
}
