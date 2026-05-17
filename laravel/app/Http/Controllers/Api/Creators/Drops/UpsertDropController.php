<?php

namespace App\Http\Controllers\Api\Creators\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpsertDropController extends Controller
{
    public function upsertDrop(Request $request, ?Drop $drop = null)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published,ended,cancelled',
            'images' => 'nullable|array',
            'images.*.image' => 'required|string',
            'images.*.sort_order' => 'nullable|integer',
            'images.*.is_main' => 'nullable|boolean',
            'products' => 'nullable|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.drop_price' => 'required|numeric',
        ]);

        $validated['status'] = match ($validated['status']) {
            'draft' => Drop::STATUS_DRAFT,
            'published' => Drop::STATUS_PUBLISHED,
            'ended' => Drop::STATUS_ENDED,
            'cancelled' => Drop::STATUS_CANCELLED,
        };

        return DB::transaction(function () use ($validated, $drop, $request) {
            if ($drop) {
                $drop->update($validated);
            } else {
                $validated['creator_id'] = $request->user()->id;
                $drop = Drop::create($validated);
            }

            if (isset($validated['images'])) {
                $drop->images()->delete();
                $drop->images()->createMany($validated['images']);
            }

            if (isset($validated['products'])) {
                $drop->products()->detach();
                foreach ($validated['products'] as $productData) {
                    $drop->products()->attach($productData['product_id'], [
                        'drop_price' => $productData['drop_price'],
                    ]);
                }
            }

            return response()->json([
                'message' => 'Drop saved successfully.',
                'drop' => $drop->load('images', 'products'),
            ]);
        });
    }
}
