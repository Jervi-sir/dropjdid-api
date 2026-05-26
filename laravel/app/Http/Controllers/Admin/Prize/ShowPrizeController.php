<?php

namespace App\Http\Controllers\Admin\Prize;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use App\Models\PrizeJoining;
use Inertia\Inertia;
use Inertia\Response;

class ShowPrizeController extends Controller
{
    /**
     * Display the prize details and listing of participants.
     */
    public function show(Prize $prize): Response
    {
        $prize->load(['creator']);

        $joinings = $prize->joinings()
            ->with(['user'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $formattedJoinings = $joinings->through(function (PrizeJoining $joining) {
            return [
                'id' => $joining->id,
                'status' => PrizeJoining::STATUS[$joining->status] ?? 'unknown',
                'created_at' => $joining->created_at?->toIso8601String(),
                'user' => $joining->user ? [
                    'id' => $joining->user->id,
                    'full_name' => $joining->user->full_name,
                    'username' => $joining->user->username,
                    'email' => $joining->user->email,
                    'image' => $joining->user->image,
                ] : null,
            ];
        });

        return Inertia::render('admin/prizes/show', [
            'prize' => [
                'id' => $prize->id,
                'title' => $prize->title,
                'image' => $prize->image ? asset('storage/' . $prize->image) : null,
                'description' => $prize->description,
                'starts_at' => $prize->starts_at?->toIso8601String(),
                'ends_at' => $prize->ends_at?->toIso8601String(),
                'status' => Prize::STATUS[$prize->status] ?? 'unknown',
                'status_raw' => $prize->status,
                'created_at' => $prize->created_at?->toIso8601String(),
                'creator' => $prize->creator ? [
                    'id' => $prize->creator->id,
                    'username' => $prize->creator->username,
                    'full_name' => $prize->creator->full_name,
                ] : null,
            ],
            'joinings' => $formattedJoinings,
        ]);
    }
}
