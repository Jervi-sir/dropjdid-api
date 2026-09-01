<?php

namespace App\Http\Controllers\Api\Giveway;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Prize;
use App\Models\PrizeJoining;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GivewayDetailController extends Controller
{
    /**
     * Get giveaway details matching ResponseType schema.
     *
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function show(Request $request, ?int $id = null): JsonResponse
    {
        $prize = null;

        if ($id) {
            $prize = Prize::find($id);
        }

        if (! $prize) {
            // Find currently active prize, or latest prize
            $prize = Prize::query()
                ->where('prize_status', 'active')
                ->where('ends_at', '>', now())
                ->latest()
                ->first()
                ?? Prize::latest()->first();
        }

        // Resolve user & phone number
        $user = $request->user('sanctum') ?? $request->user();
        $userId = $user?->id ?? $request->query('user_id');
        $phoneNumber = trim((string) ($user?->phone_number ?? $request->query('phone_number')));

        if (! $user && $userId) {
            $user = User::find($userId);
            if ($user && ! $phoneNumber) {
                $phoneNumber = (string) $user->phone_number;
            }
        }

        $isEligible = false;
        $ordersCount = 0;
        $hasJoined = false;

        if (($userId || $phoneNumber !== '') && $prize) {
            $ordersQuery = Order::query()
                ->where(function ($q) use ($userId, $phoneNumber) {
                    if ($userId) {
                        $q->where('user_id', $userId);
                    }
                    if ($phoneNumber !== '') {
                        if ($userId) {
                            $q->orWhere('phone_number', $phoneNumber);
                        } else {
                            $q->where('phone_number', $phoneNumber);
                        }
                    }
                })
                ->whereNotIn('order_status_code', [OrderStatus::CANCELLED, OrderStatus::RETURNED]);

            if ($prize->starts_at) {
                $ordersQuery->where('created_at', '>=', $prize->starts_at);
            }
            if ($prize->ends_at) {
                $ordersQuery->where('created_at', '<=', $prize->ends_at);
            }

            $ordersCount = (int) $ordersQuery->count();
            $isEligible = $ordersCount > 0;
        }

        if (! $prize) {
            // Fallback placeholder if database is empty
            return response()->json([
                'prize' => [
                    'id' => 0,
                    'text1' => 'Giveaway on iPhone 17 pro max',
                    'date_range' => now()->format('M 1') . ' - ' . now()->endOfMonth()->format('M j'),
                ],
                'time_left' => max(0, (int) now()->diffInSeconds(now()->endOfMonth(), false)),
                'is_eligible' => (bool) $isEligible,
                'orders_count' => (int) $ordersCount,
                'has_joined' => false,
            ], 200);
        }

        if ($userId || $phoneNumber !== '') {
            $hasJoined = PrizeJoining::where('prize_id', $prize->id)
                ->where(function ($q) use ($userId, $phoneNumber) {
                    if ($userId) {
                        $q->where('user_id', $userId);
                    }
                    if ($phoneNumber !== '') {
                        if ($userId) {
                            $q->orWhere('phone_number', $phoneNumber);
                        } else {
                            $q->where('phone_number', $phoneNumber);
                        }
                    }
                })
                ->exists();
        }

        $dateRange = '';
        if ($prize->starts_at && $prize->ends_at) {
            $dateRange = $prize->starts_at->format('M j') . ' - ' . $prize->ends_at->format('M j');
        } elseif ($prize->ends_at) {
            $dateRange = 'Ends ' . $prize->ends_at->format('M j');
        } else {
            $dateRange = 'Active this month';
        }

        $timeLeft = $prize->ends_at
            ? max(0, (int) now()->diffInSeconds($prize->ends_at, false))
            : 0;

        return response()->json([
            'prize' => [
                'id' => (int) $prize->id,
                'text1' => (string) $prize->title,
                'date_range' => (string) $dateRange,
            ],
            'time_left' => (int) $timeLeft,
            'is_eligible' => (bool) $isEligible,
            'orders_count' => (int) $ordersCount,
            'has_joined' => (bool) $hasJoined,
        ], 200);
    }
}

