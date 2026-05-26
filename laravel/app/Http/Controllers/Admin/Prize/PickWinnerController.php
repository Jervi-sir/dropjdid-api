<?php

namespace App\Http\Controllers\Admin\Prize;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use App\Models\PrizeJoining;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PickWinnerController extends Controller
{
    /**
     * Show the interactive visualizer raffle view.
     */
    public function raffleView(Prize $prize): Response
    {
        $prize->load(['creator']);

        // Load all joined participants (eligible for draw)
        $participants = $prize->joinings()
            ->where('status', PrizeJoining::STATUS_JOINED)
            ->with('user')
            ->get()
            ->map(function (PrizeJoining $joining) {
                return [
                    'joining_id' => $joining->id,
                    'user_id' => $joining->user?->id,
                    'full_name' => $joining->user?->full_name,
                    'username' => $joining->user?->username,
                    'image' => $joining->user?->image,
                ];
            });

        // If there's already a winner, let's load them too
        $winnerJoining = $prize->joinings()
            ->where('status', PrizeJoining::STATUS_WINNER)
            ->with('user')
            ->first();

        $winner = $winnerJoining ? [
            'joining_id' => $winnerJoining->id,
            'user_id' => $winnerJoining->user?->id,
            'full_name' => $winnerJoining->user?->full_name,
            'username' => $winnerJoining->user?->username,
            'image' => $winnerJoining->user?->image,
        ] : null;

        return Inertia::render('admin/prizes/pick-winner', [
            'prize' => [
                'id' => $prize->id,
                'title' => $prize->title,
                'image' => $prize->image ? asset('storage/' . $prize->image) : null,
                'description' => $prize->description,
                'status' => Prize::STATUS[$prize->status] ?? 'unknown',
            ],
            'participants' => $participants,
            'winner' => $winner,
        ]);
    }

    /**
     * Draw a random winner from eligible participants.
     */
    public function draw(Request $request, Prize $prize): RedirectResponse
    {
        // DB Transaction to ensure atomic update of statuses
        $result = DB::transaction(function () use ($prize) {
            $eligibleJoinings = $prize->joinings()
                ->where('status', PrizeJoining::STATUS_JOINED)
                ->get();

            if ($eligibleJoinings->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No eligible participants found to draw a winner.',
                ];
            }

            // Select a random winner
            $winnerJoining = $eligibleJoinings->random();

            // Set the winner
            $winnerJoining->update(['status' => PrizeJoining::STATUS_WINNER]);

            // Set others to lost
            $prize->joinings()
                ->where('status', PrizeJoining::STATUS_JOINED)
                ->where('id', '!=', $winnerJoining->id)
                ->update(['status' => PrizeJoining::STATUS_LOST]);

            // Mark prize as ended
            $prize->update(['status' => Prize::STATUS_ENDED]);

            return [
                'success' => true,
                'winner_id' => $winnerJoining->user_id,
            ];
        });

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        return back()->with('success', 'Winner selected successfully.');
    }
}
