<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActionsController extends Controller
{
    /**
     * Toggle save/bookmark status for a product.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function toggleSave(Request $request, int $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->input('user_id');

        if (! $userId) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $isSaved = $product->savedUsers()->where('user_id', $userId)->exists();

        if ($isSaved) {
            $product->savedUsers()->detach($userId);
            $isSaved = false;
            $message = 'Product unsaved successfully.';
        } else {
            $product->savedUsers()->attach($userId);
            $isSaved = true;
            $message = 'Product saved successfully.';
        }

        $nbSaved = $product->savedUsers()->count();

        return response()->json([
            'product_id' => (int) $product->id,
            'is_saved' => (bool) $isSaved,
            'nb_saved' => (int) $nbSaved,
            'message' => $message,
        ], 200);
    }

    /**
     * Toggle like/interested status for a product.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function toggleLike(Request $request, int $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->input('user_id');

        if (! $userId) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $product = Product::find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $isLiked = $product->likedUsers()->where('user_id', $userId)->exists();

        if ($isLiked) {
            $product->likedUsers()->detach($userId);
            $isLiked = false;
            $message = 'Product unliked successfully.';
        } else {
            $product->likedUsers()->attach($userId);
            $isLiked = true;
            $message = 'Product liked successfully.';
        }

        $nbLiked = $product->likedUsers()->count();

        return response()->json([
            'product_id' => (int) $product->id,
            'is_liked' => (bool) $isLiked,
            'nb_liked' => (int) $nbLiked,
            'message' => $message,
        ], 200);
    }
}
