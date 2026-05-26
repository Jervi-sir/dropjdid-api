<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListProductsController extends Controller
{
    /**
     * Display a listing of the products with filters and pagination.
     */
    public function __invoke(Request $request): Response
    {
        $query = Product::query()
            ->with(['store.user', 'images', 'category', 'quality'])
            ->withCount(['likedProducts', 'savedProducts', 'orderItems']);

        // Apply search filter (by product name, description, seller/store name, or category en/fr/ar)
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('store', function ($storeQuery) use ($search) {
                        $storeQuery->where('name', 'like', '%'.$search.'%')
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('username', 'like', '%'.$search.'%');
                            });
                    })
                    ->orWhereHas('category', function ($catQuery) use ($search) {
                        $catQuery->where('en', 'like', '%'.$search.'%')
                            ->orWhere('fr', 'like', '%'.$search.'%')
                            ->orWhere('ar', 'like', '%'.$search.'%');
                    });
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->input('status') !== '') {
            $statusInput = $request->input('status');

            // Map status string back to integer if it's string status
            if (is_string($statusInput) && ! is_numeric($statusInput)) {
                $statusMap = array_flip(Product::STATUSES);
                $statusVal = $statusMap[$statusInput] ?? null;
            } else {
                $statusVal = (int) $statusInput;
            }

            if ($statusVal !== null) {
                $query->where('status', $statusVal);
            }
        }

        // Paginate results (default 10 per page)
        $perPage = $request->input('per_page', 10);
        $products = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Format paginated products for Inertia response
        $formattedProducts = $products->through(function (Product $product) {
            return [
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
                ] : null,
                'quality' => $product->quality ? [
                    'id' => $product->quality->id,
                    'en' => $product->quality->en,
                ] : null,
                'image' => $product->images->sortBy('sort_order')->first()?->image,
                'liked_count' => $product->liked_products_count,
                'saved_count' => $product->saved_products_count,
                'order_items_count' => $product->order_items_count,
                'created_at' => $product->created_at?->toIso8601String(),
            ];
        });

        // Compute high-level stats for the dashboard counters
        $stats = [
            'total' => Product::count(),
            'draft' => Product::where('status', Product::STATUS_DRAFT)->count(),
            'published' => Product::where('status', Product::STATUS_PUBLISHED)->count(),
            'archived' => Product::where('status', Product::STATUS_ARCHIVED)->count(),
            'rejected' => Product::where('status', Product::STATUS_REJECTED)->count(),
            'pending' => Product::where('status', Product::STATUS_PENDING)->count(),
        ];

        return Inertia::render('admin/products/list', [
            'products' => $formattedProducts,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'per_page' => (int) $perPage,
            ],
            'statuses' => Product::STATUSES,
            'stats' => $stats,
        ]);
    }
}
