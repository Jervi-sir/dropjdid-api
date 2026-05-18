<?php

namespace App\Http\Controllers\Admin\Stores;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowStoreController extends Controller
{
    /**
     * Display the store details and management view (XHR or page view).
     */
    public function show(Request $request, Store $store): Response|JsonResponse
    {
        $store->load([
            'user',
            'wilaya',
            'products' => function ($q) {
                $q->latest()->with(['images', 'category']);
            },
        ]);

        $formattedStore = [
            'id' => $store->id,
            'store_name' => $store->store_name,
            'phone_number' => $store->phone_number,
            'logo' => $store->logo,
            'description' => $store->description,
            'balance' => (float) $store->balance,
            'status' => Store::STATUSES[$store->status] ?? 'unknown',
            'is_verified' => (bool) $store->is_verified,
            'user' => $store->user ? [
                'id' => $store->user->id,
                'full_name' => $store->user->full_name,
                'username' => $store->user->username,
                'email' => $store->user->email,
            ] : null,
            'wilaya' => $store->wilaya ? [
                'id' => $store->wilaya->id,
                'name' => $store->wilaya->name,
            ] : null,
            'products' => $store->products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'original_price' => (float) $p->original_price,
                'show_price' => (float) $p->show_price,
                'status' => Product::STATUSES[$p->status] ?? 'unknown',
                'category' => $p->category ? [
                    'id' => $p->category->id,
                    'en' => $p->category->en,
                ] : null,
                'image' => $p->images->sortBy('sort_order')->first()?->image,
                'created_at' => $p->created_at?->toIso8601String(),
            ])->toArray(),
            'created_at' => $store->created_at?->toIso8601String(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'store' => $formattedStore,
            ]);
        }

        return Inertia::render('admin/stores/show', [
            'store' => $formattedStore,
            'statuses' => Store::STATUSES,
        ]);
    }

    /**
     * Update the store status and verification.
     */
    public function update(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'is_verified' => 'required|boolean',
        ]);

        $statusInput = $validated['status'];
        if (is_string($statusInput) && ! is_numeric($statusInput)) {
            $statusMap = array_flip(Store::STATUSES);
            $statusVal = $statusMap[$statusInput] ?? Store::STATUS_PENDING;
        } else {
            $statusVal = (int) $statusInput;
        }

        $store->status = $statusVal;
        $store->is_verified = $validated['is_verified'];
        $store->save();

        return back()->with('success', 'Store status and verification updated successfully.');
    }
}
