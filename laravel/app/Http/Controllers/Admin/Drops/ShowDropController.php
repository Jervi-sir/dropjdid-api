<?php

namespace App\Http\Controllers\Admin\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowDropController extends Controller
{
    /**
     * Display the drop details and management view.
     */
    public function show(Request $request, Drop $drop): Response|JsonResponse
    {
        // Load relationships needed: creator, products, products.images, images
        $drop->load([
            'creator',
            'images',
            'products' => function ($query) {
                $query->with(['images', 'store.user']);
            },
        ]);

        // Formatted drop details
        $formattedDrop = [
            'id' => $drop->id,
            'title' => $drop->title,
            'description' => $drop->description,
            'status' => Drop::STATUSES[$drop->status] ?? 'unknown',
            'starts_at' => $drop->starts_at?->toIso8601String(),
            'ends_at' => $drop->ends_at?->toIso8601String(),
            'creator' => $drop->creator ? [
                'id' => $drop->creator->id,
                'username' => $drop->creator->username,
            ] : null,
            'images' => $drop->images->map(fn ($img) => [
                'id' => $img->id,
                'image' => $img->image,
                'sort_order' => $img->sort_order,
                'is_main' => $img->is_main,
            ])->toArray(),
            'rejection_reasons' => $drop->rejection_reason ?? [],
            'created_at' => $drop->created_at?->toIso8601String(),
        ];

        // Formatted products list
        $formattedProducts = $drop->products->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'original_price' => (float) $product->original_price,
            'drop_price' => (float) ($product->pivot->drop_price ?? $product->original_price),
            'status' => $product->status_text,
            'store' => $product->store ? [
                'id' => $product->store->id,
                'name' => $product->store->name,
                'username' => $product->store->user?->username,
            ] : null,
            'image' => $product->images->sortBy('sort_order')->first()?->image,
        ])->toArray();

        if ($request->wantsJson()) {
            return response()->json([
                'drop' => $formattedDrop,
                'products' => $formattedProducts,
            ]);
        }

        return Inertia::render('admin/drops/show', [
            'drop' => $formattedDrop,
            'products' => $formattedProducts,
            'statuses' => Drop::STATUSES,
        ]);
    }

    /**
     * Update the drop's status and optionally log a rejection reason.
     */
    public function update(Request $request, Drop $drop): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'rejection_reason_en' => 'required_if:status,rejected|nullable|string',
            'rejection_reason_fr' => 'required_if:status,rejected|nullable|string',
            'rejection_reason_ar' => 'required_if:status,rejected|nullable|string',
        ]);

        $statusInput = $validated['status'];

        // Map string status back to integer if needed
        if (is_string($statusInput) && ! is_numeric($statusInput)) {
            $statusMap = array_flip(Drop::STATUSES);
            $statusVal = $statusMap[$statusInput] ?? Drop::STATUS_DRAFT;
        } else {
            $statusVal = (int) $statusInput;
        }

        $drop->status = $statusVal;

        if ($statusInput === 'rejected' || $statusVal === Drop::STATUS_REJECTED) {
            $drop->addRejectionReason(
                $validated['rejection_reason_en'] ?? '',
                $validated['rejection_reason_fr'] ?? '',
                $validated['rejection_reason_ar'] ?? ''
            );
        } else {
            $drop->save();
        }

        return redirect()->back()->with('success', 'Drop status updated successfully.');
    }
}
