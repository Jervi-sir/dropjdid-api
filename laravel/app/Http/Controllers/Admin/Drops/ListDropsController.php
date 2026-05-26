<?php

namespace App\Http\Controllers\Admin\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListDropsController extends Controller
{
    /**
     * Display a listing of the drops with filters and pagination.
     */
    public function __invoke(Request $request): Response
    {
        $query = Drop::query()
            ->with(['creator'])
            ->withCount(['products', 'likedDrops', 'savedDrops']);

        // Apply search filter (by title, description, or creator username)
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('creator', function ($creatorQuery) use ($search) {
                        $creatorQuery->where('username', 'like', '%'.$search.'%');
                    });
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->input('status') !== '') {
            $statusInput = $request->input('status');

            // Map status string back to integer if it's string status
            if (is_string($statusInput) && ! is_numeric($statusInput)) {
                $statusMap = array_flip(Drop::STATUSES);
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
        $drops = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Format the paginated items for Inertia response
        $formattedDrops = $drops->through(function (Drop $drop) {
            return [
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
                'products_count' => $drop->products_count,
                'liked_drops_count' => $drop->liked_drops_count,
                'saved_drops_count' => $drop->saved_drops_count,
                'created_at' => $drop->created_at?->toIso8601String(),
            ];
        });

        // Compute high-level stats for the dashboard counters
        $stats = [
            'total' => Drop::count(),
            'draft' => Drop::where('status', Drop::STATUS_DRAFT)->count(),
            'published' => Drop::where('status', Drop::STATUS_PUBLISHED)->count(),
            'ended' => Drop::where('status', Drop::STATUS_ENDED)->count(),
            'cancelled' => Drop::where('status', Drop::STATUS_CANCELLED)->count(),
            'rejected' => Drop::where('status', Drop::STATUS_REJECTED)->count(),
            'pending' => Drop::where('status', Drop::STATUS_PENDING)->count(),
        ];

        return Inertia::render('admin/drops/list', [
            'drops' => $formattedDrops,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'per_page' => (int) $perPage,
            ],
            'statuses' => Drop::STATUSES,
            'stats' => $stats,
        ]);
    }
}
