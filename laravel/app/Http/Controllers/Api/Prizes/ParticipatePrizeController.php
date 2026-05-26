<?php

namespace App\Http\Controllers\Api\Prizes;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use App\Models\PrizeJoining;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParticipatePrizeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'phone_number' => ['required', 'string', 'max:255'],
        ]);

        $prize = Prize::query()
            ->where('status', Prize::STATUS_ACTIVE)
            ->where(function ($query): void {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->first();

        abort_if($prize === null, 404, 'No active prize found.');

        DB::transaction(function () use ($user, $validated, $prize): void {
            if ($user->phone_number !== $validated['phone_number']) {
                $user->forceFill([
                    'phone_number' => $validated['phone_number'],
                ])->save();
            }

            PrizeJoining::query()->firstOrCreate(
                [
                    'prize_id' => $prize->id,
                    'user_id' => $user->id,
                ],
                [
                    'amount_paid' => $prize->joining_price,
                    'status' => PrizeJoining::STATUS_JOINED,
                ],
            );
        });

        $prize->loadCount('joinings');
        $prize->load([
            'creator',
            'joinings' => fn ($query) => $query->where('user_id', $user->id),
        ]);

        return response()->json([
            'message' => 'Prize participation saved successfully.',
            'data' => $prize->formatForApi($user),
            'viewer_phone_number' => $user->fresh()?->phone_number,
        ]);
    }
}
