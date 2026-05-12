<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\LikedProduct;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeProductController extends Controller
{
    public function __invoke(Request $request, int $product_id): JsonResponse
    {
        $user = $request->user();

        $product = Product::find($product_id);

        abort_if($user === null, 401);

        $like = LikedProduct::query()->where([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ])->first();

        if ($like !== null) {
            $like->delete();

            return response()->json([
                'message' => 'Product unliked successfully.',
                'is_liked' => false,
                'nb_likes' => $product->likedProducts()->count(),
            ]);
        }

        LikedProduct::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'message' => 'Product liked successfully.',
            'is_liked' => true,
            'nb_likes' => $product->likedProducts()->count(),
        ]);
    }
}
