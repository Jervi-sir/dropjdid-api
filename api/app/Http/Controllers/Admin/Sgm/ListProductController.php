<?php

namespace App\Http\Controllers\Admin\Sgm;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListProductController extends Controller
{
    /**
     * Display a paginated listing of products for admin review.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));
        $storeId = $request->query('store_id');

        $query = Product::query()->with([
            'store',
            'category',
            'gender',
            'quality',
            'images',
            'variants.size',
            'labels',
        ]);

        // Filter by status if specified
        if ($status && $status !== 'all') {
            $query->where('product_status', $status);
        }

        // Filter by store if specified
        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        // Search by product name, description, store name, or category name
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('store', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('category', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $products = $query->latest('id')->paginate(12)->withQueryString();

        // Calculate count metrics for tab badges
        $counts = [
            'all' => Product::count(),
            'draft' => Product::where('product_status', Product::STATUS_DRAFT)->count(),
            'published' => Product::where('product_status', Product::STATUS_PUBLISHED)->count(),
            'rejected' => Product::where('product_status', Product::STATUS_REJECTED)->count(),
            'archived' => Product::where('product_status', Product::STATUS_ARCHIVED)->count(),
        ];

        return Inertia::render('admin/sgms/list.products', [
            'products' => $products,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'store_id' => $storeId,
            ],
            'counts' => $counts,
        ]);
    }

    /**
     * Approve a product and publish it.
     */
    public function approve(Request $request, Product $product): RedirectResponse
    {
        $product->update([
            'product_status' => Product::STATUS_PUBLISHED,
            'rejection_reason' => null,
            'refreshed_at' => now(),
        ]);

        return back()->with('success', "Product '{$product->name}' approved and published successfully.");
    }

    /**
     * Reject a product with a reason note.
     */
    public function reject(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $product->update([
            'product_status' => Product::STATUS_REJECTED,
            'rejection_reason' => Product::formatRejectionReason($validated['reason']),
        ]);

        return back()->with('success', "Product '{$product->name}' rejected.");
    }

    /**
     * Archive a product with a reason note.
     */
    public function archive(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $product->update([
            'product_status' => Product::STATUS_ARCHIVED,
            'rejection_reason' => Product::formatRejectionReason($validated['reason']),
        ]);

        return back()->with('success', "Product '{$product->name}' archived.");
    }

    /**
     * Update product status directly (draft, published, archived, rejected).
     */
    public function updateStatus(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'product_status' => ['required', 'string', 'in:draft,published,archived,rejected'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = $validated['product_status'];
        $data = [
            'product_status' => $status,
            'refreshed_at' => $status === Product::STATUS_PUBLISHED ? now() : $product->refreshed_at,
        ];

        if (in_array($status, [Product::STATUS_REJECTED, Product::STATUS_ARCHIVED]) && !empty($validated['reason'])) {
            $data['rejection_reason'] = Product::formatRejectionReason($validated['reason']);
        } elseif ($status === Product::STATUS_PUBLISHED) {
            $data['rejection_reason'] = null;
        }

        $product->update($data);

        return back()->with('success', "Product '{$product->name}' status updated to {$status}.");
    }
}
