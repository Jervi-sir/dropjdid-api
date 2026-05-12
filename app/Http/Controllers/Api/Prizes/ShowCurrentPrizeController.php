<?php

namespace App\Http\Controllers\Api\Prizes;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowCurrentPrizeController extends Controller
{
    public function preview(Request $request): JsonResponse
    {
        $prize = $this->resolveCurrentPrize($request);

        return response()->json([
            'data' => $prize?->formatForApi($request->user()),
        ]);
    }

    public function showFully(Request $request): JsonResponse
    {
        $user = $request->user();
        $prize = $this->resolveCurrentPrize($request);

        return response()->json([
            'data' => $prize?->formatForApi($user),
            'viewer_phone_number' => $user?->phone_number,
        ]);
    }

    private function resolveCurrentPrize(Request $request): ?Prize
    {
        $user = $request->user();

        return Prize::query()
            ->where('status', 'active')
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
            ->withCount('joinings')
            ->when($user !== null, function ($query) use ($user): void {
                $query->withExists([
                    'joinings as is_joined' => fn ($query) => $query->where('user_id', $user->id),
                ]);
            })
            ->with([
                'creator',
                'joinings' => function ($query) use ($user): void {
                    if ($user !== null) {
                        $query->where('user_id', $user->id);
                    } else {
                        $query->whereRaw('1 = 0');
                    }
                },
            ])
            ->orderByDesc('starts_at')
            ->first();
    }
}
