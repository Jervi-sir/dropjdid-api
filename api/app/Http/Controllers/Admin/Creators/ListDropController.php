<?php

namespace App\Http\Controllers\Admin\Creators;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\DropHistory;
use App\Models\DropImage;
use App\Models\Product;
use App\Models\UserInteraction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListDropController extends Controller
{
    /**
     * Display a paginated listing of drops for admin management.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));

        $query = Drop::query()
            ->with(['creator', 'images', 'mainImage', 'products'])
            ->withCount(['likes as liked_users_count', 'saves as saved_users_count', 'products']);

        // Status Filter
        if ($status && $status !== 'all') {
            $query->where('drop_status', $status);
        }

        // Search Filter
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('creator', function ($cq) use ($search) {
                        $cq->where('username', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        $drops = $query->latest('id')->paginate(12)->withQueryString();

        // Calculate counts
        $counts = [
            'all' => Drop::count(),
            'new' => Drop::where('drop_status', Drop::STATUS_NEW)->count(),
            'under-review' => Drop::where('drop_status', Drop::STATUS_UNDER_REVIEW)->count(),
            'published' => Drop::where('drop_status', Drop::STATUS_PUBLISHED)->count(),
            'draft' => Drop::where('drop_status', Drop::STATUS_DRAFT)->count(),
            'paused' => Drop::where('drop_status', Drop::STATUS_PAUSED)->count(),
            'rejected' => Drop::where('drop_status', Drop::STATUS_REJECTED)->count(),
        ];

        // Fetch share and repost counts for the current page of drops
        $dropIds = $drops->pluck('id')->all();
        $interactions = UserInteraction::query()
            ->where('target_type', UserInteraction::TARGET_DROP)
            ->whereIn('target_id', $dropIds)
            ->whereIn('type', [UserInteraction::TYPE_SHARE, UserInteraction::TYPE_REPOST])
            ->selectRaw('target_id, type, COUNT(*) as count')
            ->groupBy('target_id', 'type')
            ->get()
            ->groupBy('target_id');

        // Format drops data with primary image url & interaction stats
        $drops->getCollection()->transform(function (Drop $drop) use ($interactions) {
            $imageUrl = '';
            $mainImg = $drop->mainImage?->image;
            if ($mainImg) {
                $imageUrl = $mainImg;
            } elseif ($drop->images->isNotEmpty()) {
                $imageUrl = $drop->images->first()->image;
            }

            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $dropInteractions = $interactions->get($drop->id, collect());
            $sharesCount = (int) ($dropInteractions->firstWhere('type', UserInteraction::TYPE_SHARE)?->count ?? 0);
            $repostsCount = (int) ($dropInteractions->firstWhere('type', UserInteraction::TYPE_REPOST)?->count ?? 0);

            $drop->primary_image_url = $imageUrl;
            $drop->shares_count = $sharesCount;
            $drop->reposts_count = $repostsCount;

            return $drop;
        });

        // Available products list for drop product selection if editing
        $allProducts = Product::select('id', 'name', 'price_shown', 'price_original')
            ->latest('id')
            ->limit(50)
            ->get()
            ->map(function (Product $p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => $p->price_shown ?? $p->price_original ?? 0,
                ];
            });

        return Inertia::render('admin/creators/list.drop', [
            'drops' => $drops,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'counts' => $counts,
            'availableProducts' => $allProducts,
        ]);
    }

    /**
     * Update drop details (title, description, status).
     */
    public function update(Request $request, Drop $drop): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'drop_status' => ['required', 'string', 'in:'.implode(',', Drop::STATUSES)],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $sanitizedTitle = strtolower(preg_replace('/_+/', '_', preg_replace('/\s+/', '_', trim($validated['title']))));
        $oldStatus = $drop->drop_status;

        $updateData = [
            'title' => $sanitizedTitle,
            'description' => $validated['description'] ?? '',
            'drop_status' => $validated['drop_status'],
        ];

        $rejectionNote = null;
        if ($validated['drop_status'] === Drop::STATUS_REJECTED && ! empty($validated['rejection_reason'])) {
            $rejectionNote = $validated['rejection_reason'];
            $updateData['rejection_reason'] = [
                'reason' => $validated['rejection_reason'],
                'rejected_at' => now()->toIso8601String(),
            ];
        } elseif ($validated['drop_status'] !== Drop::STATUS_REJECTED) {
            $updateData['rejection_reason'] = null;
        }

        $drop->update($updateData);

        DropHistory::record(
            drop: $drop,
            action: 'admin_updated',
            userId: $request->user()?->id,
            fromStatus: $oldStatus,
            toStatus: $drop->drop_status,
            note: $rejectionNote ?? 'Drop details updated by admin',
            metadata: [
                'title' => $sanitizedTitle,
                'drop_status' => $drop->drop_status,
            ]
        );

        return back()->with('success', "Drop #{$drop->id} updated successfully.");
    }

    /**
     * Quickly update only the drop status.
     */
    public function updateStatus(Request $request, Drop $drop): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', Drop::STATUSES)],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = $validated['status'];
        $oldStatus = $drop->drop_status;
        $updateData = [
            'drop_status' => $status,
        ];

        $note = null;
        if ($status === Drop::STATUS_REJECTED) {
            $note = $validated['rejection_reason'] ?? 'Rejected by administrator';
            $updateData['rejection_reason'] = [
                'reason' => $note,
                'rejected_at' => now()->toIso8601String(),
            ];
        } else {
            $updateData['rejection_reason'] = null;
            $note = "Status changed to {$status}";
        }

        $drop->update($updateData);

        DropHistory::record(
            drop: $drop,
            action: 'admin_status_changed',
            userId: $request->user()?->id,
            fromStatus: $oldStatus,
            toStatus: $status,
            note: $note,
            metadata: [
                'quick_action' => true,
            ]
        );

        return back()->with('success', "Drop status updated to {$status}.");
    }

    /**
     * Delete a drop.
     */
    public function destroy(Drop $drop): RedirectResponse
    {
        $drop->delete();

        return back()->with('success', "Drop #{$drop->id} deleted successfully.");
    }
}
