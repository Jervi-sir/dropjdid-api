<?php

namespace App\Http\Controllers\Admin\Prize;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListPrizesController extends Controller
{
    /**
     * Display a listing of prizes with search and status filters.
     */
    public function __invoke(Request $request): Response
    {
        $query = Prize::query()
            ->with(['creator'])
            ->withCount(['joinings']);

        // Search Filter
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Status Filter (Translates string status back to integer for DB)
        if ($request->has('status') && $request->filled('status') && $request->input('status') !== 'all') {
            $statusStr = $request->input('status');
            $statusCode = array_search($statusStr, Prize::STATUS);
            if ($statusCode !== false) {
                $query->where('status', $statusCode);
            }
        }

        $perPage = $request->input('per_page', 10);
        $prizes = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Format for Inertia
        $formattedPrizes = $prizes->through(function (Prize $prize) {
            return [
                'id' => $prize->id,
                'title' => $prize->title,
                'image' => $prize->image ? asset('storage/' . $prize->image) : null,
                'description' => $prize->description,
                'starts_at' => $prize->starts_at?->toIso8601String(),
                'ends_at' => $prize->ends_at?->toIso8601String(),
                'status' => Prize::STATUS[$prize->status] ?? 'unknown',
                'joinings_count' => $prize->joinings_count,
                'creator' => $prize->creator ? [
                    'id' => $prize->creator->id,
                    'username' => $prize->creator->username,
                    'full_name' => $prize->creator->full_name,
                ] : null,
                'created_at' => $prize->created_at?->toIso8601String(),
            ];
        });

        // Compute dashboard stats
        $stats = [
            'total' => Prize::count(),
            'draft' => Prize::where('status', Prize::STATUS_DRAFT)->count(),
            'active' => Prize::where('status', Prize::STATUS_ACTIVE)->count(),
            'ended' => Prize::where('status', Prize::STATUS_ENDED)->count(),
            'cancelled' => Prize::where('status', Prize::STATUS_CANCELLED)->count(),
        ];

        return Inertia::render('admin/prizes/list', [
            'prizes' => $formattedPrizes,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', 'all'),
                'per_page' => (int) $perPage,
            ],
            'statuses' => Prize::STATUS,
            'stats' => $stats,
        ]);
    }
}
