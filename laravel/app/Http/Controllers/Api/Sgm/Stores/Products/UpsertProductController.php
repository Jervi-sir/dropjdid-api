<?php

namespace App\Http\Controllers\Api\Sgm\Stores\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpsertProductController extends Controller
{
    public function __invoke(Request $request, ?Product $product = null)
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'category_id' => 'nullable|exists:categories,id', // Make nullable if not selected
            'gender_id' => 'nullable|exists:genders,id',
            'quality_id' => 'nullable|exists:qualities,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'description' => 'nullable|string',
            'original_price' => 'nullable|numeric',
            'show_price' => 'nullable|numeric',
            'store_price' => 'nullable|numeric',
            'status' => 'required|in:draft,published,archived',
            'images' => 'nullable|array',
            'images.*.image' => 'required|string',
            'images.*.sort_order' => 'nullable|integer',
            'images.*.is_main' => 'nullable|boolean',
            'variants' => 'nullable|array',
            'variants.*.size_id' => 'required|exists:sizes,id',
            'variants.*.quantity' => 'required|integer',
            'keywords' => 'nullable|array',
            'keywords.*.label_id' => 'required|exists:labels,id',
            'keywords.*.keyword_id' => 'required|exists:keywords,id',
        ]);

        return DB::transaction(function () use ($validated, $product) {
            if ($product) {
                $product->update($validated);
            } else {
                $product = Product::create($validated);
            }

            if (isset($validated['images'])) {
                $product->images()->delete();
                $product->images()->createMany($validated['images']);
            }

            if (isset($validated['variants'])) {
                $product->variants()->delete();
                $product->variants()->createMany($validated['variants']);
            }

            if (isset($validated['keywords'])) {
                $product->productKeywords()->delete();
                $product->productKeywords()->createMany($validated['keywords']);
            }

            return response()->json([
                'message' => 'Product saved successfully.',
                'product' => $product->load('images', 'variants', 'productKeywords'),
            ]);
        });
    }
}
