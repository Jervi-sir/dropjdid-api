<?php

namespace App\Http\Controllers\Api\Creators\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UpsertDropController extends Controller
{
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'exclude_id' => 'nullable|integer',
        ]);

        $titleLower = strtolower($request->title);
        $query = Drop::whereRaw('LOWER(title) = ?', [$titleLower]);

        if ($request->exclude_id) {
            $query->where('id', '!=', $request->exclude_id);
        }

        $isAvailable = !$query->exists();

        return response()->json([
            'available' => $isAvailable,
            'message' => $isAvailable ? 'Name is available.' : 'The drop name is already taken.',
        ]);
    }

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

        $titleLower = strtolower($request->title);
        $existsQuery = Drop::whereRaw('LOWER(title) = ?', [$titleLower]);
        if ($drop) {
            $existsQuery->where('id', '!=', $drop->id);
        }
        if ($existsQuery->exists()) {
            return response()->json([
                'message' => 'The drop name is already taken.',
                'errors' => [
                    'title' => ['The drop name is already taken.']
                ]
            ], 422);
        }

        $validated['status'] = match ($validated['status']) {
            'draft' => Drop::STATUS_DRAFT,
            'published' => Drop::STATUS_PENDING,
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
