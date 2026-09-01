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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GivewayJoinController extends Controller
{
    /**
     * Join giveaway with phone number.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function join(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'string', 'min:8', 'max:20'],
            'prize_id' => ['nullable', 'integer', 'exists:prizes,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $phoneNumber = trim($request->input('phone_number'));
        $prizeId = $request->input('prize_id');

        // Resolve target prize
        $prize = null;
        if ($prizeId) {
            $prize = Prize::find($prizeId);
        }

        if (! $prize) {
            $prize = Prize::query()
                ->where('prize_status', 'active')
                ->where('ends_at', '>', now())
                ->latest()
                ->first()
                ?? Prize::latest()->first();
        }

        if (! $prize) {
            return response()->json([
                'message' => 'No active giveaway found at this time.',
            ], 404);
        }

        // Get authenticated user or find/create user by phone number
        $user = $request->user('sanctum') ?? $request->user();

        if ($user) {
            if (! $user->phone_number) {
                $user->phone_number = $phoneNumber;
                $user->save();
            }
        } else {
            $user = User::where('phone_number', $phoneNumber)->first();

            if (! $user) {
                $randomStr = Str::lower(Str::random(6));
                $user = User::create([
                    'phone_number' => $phoneNumber,
                    'username' => 'user_' . $randomStr,
                    'full_name' => 'Giveaway Participant',
                    'email' => 'phone_' . preg_replace('/[^0-9]/', '', $phoneNumber) . '@djapp.local',
                    'password' => bcrypt(Str::random(16)),
                    'is_active' => true,
                ]);
            }
        }

        // Record joining
        $joining = PrizeJoining::updateOrCreate(
            [
                'prize_id' => $prize->id,
                'user_id' => $user->id,
            ],
            [
                'phone_number' => $phoneNumber,
                'status' => 'joined',
            ]
        );

        // Check eligibility (orders placed between prize starts_at and ends_at)
        $ordersQuery = Order::query()
            ->where(function ($q) use ($user, $phoneNumber) {
                if ($user?->id) {
                    $q->where('user_id', $user->id);
                }
                if ($phoneNumber !== '') {
                    if ($user?->id) {
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
            'message' => 'You have joined the giveaway successfully!',
            'has_joined' => true,
            'phone_number' => (string) ($joining->phone_number ?? $user->phone_number ?? $phoneNumber),
            'prize_id' => (int) $prize->id,
            'is_eligible' => (bool) $isEligible,
            'orders_count' => (int) $ordersCount,
            'prize' => [
                'id' => (int) $prize->id,
                'text1' => (string) $prize->title,
                'date_range' => (string) $dateRange,
            ],
            'time_left' => (int) $timeLeft,
        ], 200);
    }

    /**
     * Check if user has already joined the giveaway and with what phone number.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function status(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');
        $phoneNumber = trim((string) $request->query('phone_number'));
        $prizeId = $request->query('prize_id');

        // Resolve target prize
        $prize = null;
        if ($prizeId) {
            $prize = Prize::find($prizeId);
        }

        if (! $prize) {
            $prize = Prize::query()
                ->where('prize_status', 'active')
                ->where('ends_at', '>', now())
                ->latest()
                ->first()
                ?? Prize::latest()->first();
        }

        if (! $prize) {
            return response()->json([
                'has_joined' => false,
                'phone_number' => null,
                'prize_id' => 0,
                'joined_at' => null,
                'is_eligible' => false,
                'orders_count' => 0,
                'message' => 'No active giveaway found.',
            ], 200);
        }

        // Find user by auth ID or phone number
        $user = null;
        if ($userId) {
            $user = User::find($userId);
        } elseif ($phoneNumber !== '') {
            $user = User::where('phone_number', $phoneNumber)->first();
        }

        $joining = null;
        if ($user) {
            $joining = PrizeJoining::where('prize_id', $prize->id)
                ->where('user_id', $user->id)
                ->latest()
                ->first();
        }

        if (! $joining && $phoneNumber !== '') {
            $joining = PrizeJoining::where('prize_id', $prize->id)
                ->where('phone_number', $phoneNumber)
                ->latest()
                ->first();
        }

        $registeredPhone = $joining?->phone_number ?? $user?->phone_number ?? ($phoneNumber !== '' && $joining ? $phoneNumber : null);

        // Check eligibility (orders placed between prize starts_at and ends_at)
        $ordersCount = 0;
        $isEligible = false;

        if ($user || $phoneNumber !== '') {
            $ordersQuery = Order::query()
                ->where(function ($q) use ($user, $phoneNumber) {
                    if ($user?->id) {
                        $q->where('user_id', $user->id);
                    }
                    if ($phoneNumber !== '') {
                        if ($user?->id) {
                            $q->orWhere('phone_number', $phoneNumber);
                        } else {
                            $q->where('phone_number', $phoneNumber);
                        }
                    }
                })
                ->where('order_status_code', '!=', 'cancelled');

            if ($prize->starts_at) {
                $ordersQuery->where('created_at', '>=', $prize->starts_at);
            }
            if ($prize->ends_at) {
                $ordersQuery->where('created_at', '<=', $prize->ends_at);
            }

            $ordersCount = (int) $ordersQuery->count();
            $isEligible = $ordersCount > 0;
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
            'has_joined' => (bool) ($joining !== null),
            'phone_number' => $registeredPhone,
            'prize_id' => (int) $prize->id,
            'joined_at' => $joining?->created_at?->toISOString() ?? null,
            'is_eligible' => (bool) $isEligible,
            'orders_count' => (int) $ordersCount,
            'prize' => [
                'id' => (int) $prize->id,
                'text1' => (string) $prize->title,
                'date_range' => (string) $dateRange,
            ],
            'time_left' => (int) $timeLeft,
        ], 200);
    }
}
