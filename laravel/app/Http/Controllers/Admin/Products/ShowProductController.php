<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowProductController extends Controller
{
    /**
     * Display the product details and management view (XHR or page view).
     */
    public function show(Request $request, Product $product): Response|JsonResponse
    {
        $product->load([
            'store.user',
            'images',
            'category',
            'quality',
            'paymentMethod',
            'gender',
            'variants',
        ]);

        $formattedProduct = [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'original_price' => (float) $product->original_price,
            'show_price' => (float) $product->show_price,
            'store_price' => (float) $product->store_price,
            'status' => Product::STATUSES[$product->status] ?? 'unknown',
            'store' => $product->store ? [
                'id' => $product->store->id,
                'name' => $product->store->name,
                'username' => $product->store->user?->username,
            ] : null,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'en' => $product->category->en,
                'fr' => $product->category->fr,
                'ar' => $product->category->ar,
            ] : null,
            'quality' => $product->quality ? [
                'id' => $product->quality->id,
                'en' => $product->quality->en,
            ] : null,
            'gender' => $product->gender ? [
                'id' => $product->gender->id,
                'en' => $product->gender->en,
            ] : null,
            'payment_method' => $product->paymentMethod ? [
                'id' => $product->paymentMethod->id,
                'en' => $product->paymentMethod->en,
            ] : null,
            'images' => $product->images->map(fn ($img) => [
                'id' => $img->id,
                'image' => $img->image,
                'sort_order' => $img->sort_order,
            ])->toArray(),
            'rejection_reasons' => $product->rejection_reason ?? [],
            'created_at' => $product->created_at?->toIso8601String(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'product' => $formattedProduct,
            ]);
        }

        return Inertia::render('admin/products/show', [
            'product' => $formattedProduct,
            'statuses' => Product::STATUSES,
        ]);
    }

    /**
     * Update the product status (and append localized rejection reasons if status is rejected).
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'rejection_reason_en' => 'required_if:status,rejected|nullable|string',
            'rejection_reason_fr' => 'required_if:status,rejected|nullable|string',
            'rejection_reason_ar' => 'required_if:status,rejected|nullable|string',
        ]);

        // Map status text back to integer representation
        $statusInput = $validated['status'];
        if (is_string($statusInput) && ! is_numeric($statusInput)) {
            $statusMap = array_flip(Product::STATUSES);
            $statusVal = $statusMap[$statusInput] ?? Product::STATUS_DRAFT;
        } else {
            $statusVal = (int) $statusInput;
        }

        $product->status = $statusVal;

        // If rejected, write localized rejection translation stack
        if ($statusVal === Product::STATUS_REJECTED) {
            $en = $validated['rejection_reason_en'] ?? '';
            $fr = $validated['rejection_reason_fr'] ?? '';
            $ar = $validated['rejection_reason_ar'] ?? '';
            $product->addRejectionReason($en, $fr, $ar);
        } else {
            $product->save();
        }

        return back()->with('success', 'Product status updated successfully.');
    }
}
