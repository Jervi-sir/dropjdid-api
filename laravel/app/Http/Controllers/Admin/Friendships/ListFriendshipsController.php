<?php

namespace App\Http\Controllers\Admin\Friendships;

use App\Http\Controllers\Controller;
use App\Models\Friendship;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListFriendshipsController extends Controller
{
    /**
     * Display a listing of friendships with search and filtering.
     */
    public function __invoke(Request $request): Response
    {
        $query = Friendship::query()->with(['sender', 'receiver']);

        // Aggregate high-level KPI Metrics
        $totalCount = Friendship::count();
        $acceptedCount = Friendship::where('status', Friendship::STATUS_ACCEPTED)->count();
        $pendingCount = Friendship::where('status', Friendship::STATUS_PENDING)->count();
        $blockedCount = Friendship::where('status', Friendship::STATUS_BLOCKED)->count();

        // 1. Search filter (sender or receiver username, full_name, email)
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('sender', function ($sub) use ($search) {
                    $sub->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                })->orWhereHas('receiver', function ($sub) use ($search) {
                    $sub->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('username', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            });
        }

        // 2. Status filter
        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('status', $request->input('status'));
        }

        $perPage = $request->input('per_page', 15);
        $friendships = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Format friendships list for Inertia view
        $formattedFriendships = $friendships->through(function (Friendship $friendship) {
            return [
                'id' => $friendship->id,
                'sender' => $friendship->sender ? [
                    'id' => $friendship->sender->id,
                    'full_name' => $friendship->sender->full_name,
                    'username' => $friendship->sender->username,
                    'email' => $friendship->sender->email,
                    'image' => $friendship->sender->image,
                ] : null,
                'receiver' => $friendship->receiver ? [
                    'id' => $friendship->receiver->id,
                    'full_name' => $friendship->receiver->full_name,
                    'username' => $friendship->receiver->username,
                    'email' => $friendship->receiver->email,
                    'image' => $friendship->receiver->image,
                ] : null,
                'status' => Friendship::STATUSES[$friendship->status] ?? 'unknown',
                'status_raw' => $friendship->status,
                'accepted_at' => $friendship->accepted_at?->toIso8601String(),
                'rejected_at' => $friendship->rejected_at?->toIso8601String(),
                'blocked_at' => $friendship->blocked_at?->toIso8601String(),
                'created_at' => $friendship->created_at?->toIso8601String(),
            ];
        });

        return Inertia::render('admin/friendships/list', [
            'friendships' => $formattedFriendships,
            'kpis' => [
                'total_count' => $totalCount,
                'accepted_count' => $acceptedCount,
                'pending_count' => $pendingCount,
                'blocked_count' => $blockedCount,
            ],
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'per_page' => (int) $perPage,
            ],
        ]);
    }
}
