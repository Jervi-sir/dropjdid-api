<?php

namespace App\Http\Controllers\Api\Sgm\Stores\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Todo
|--------------------------------------------------------------------------
|
| push the delete drop into a queue
|
*/


class DeleteProductController extends Controller
{
    /**
     * Soft delete a product.
     * Hidden from explore, drops, search immediately due to SoftDeletes.
     */
    public function __invoke(Request $request, int $store_id, int $product_id): JsonResponse
    {
        $product = Product::find($product_id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found.',
            ], 404);
        }

        // Security check: ensure the user owns the store
        if (! $request->user() || $product->store->user_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized action.',
            ], 403);
        }

        // Check drops associated with this product
        $drops = $product->drops;
        foreach ($drops as $drop) {
            $hasOtherProducts = $drop->products()
                ->where('products.id', '!=', $product->id)
                ->exists();

            if (!$hasOtherProducts) {
                $drop->delete();
            }
        }

        // Perform soft delete
        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully. It will be permanently removed after 7 days if all orders are completed.',
        ]);
    }
}
