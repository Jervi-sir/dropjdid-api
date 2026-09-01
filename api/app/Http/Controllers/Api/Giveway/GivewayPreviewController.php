<?php

namespace App\Http\Controllers\Api\Giveway;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use App\Models\PrizeJoining;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GivewayPreviewController extends Controller
{
    /**
     * Get preview metadata of the active (or requested) prize matching PrizeType interface:
     * - id: number
     * - title: string
     * - image_url: string
     * - date_range: string
     * - has_joined?: boolean
     *
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function __invoke(Request $request, ?int $id = null): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

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

        if (! $prize) {
            // Fallback placeholder if no prize is in the database yet
            return response()->json([
                'id' => 0,
                'title' => 'Giveaway on iPhone 17 pro max',
                'image_url' => '',
                'date_range' => now()->format('M 1') . ' - ' . now()->endOfMonth()->format('M j'),
                'has_joined' => false,
            ], 200);
        }

        $dateRange = '';
        if ($prize->starts_at && $prize->ends_at) {
            $dateRange = $prize->starts_at->format('M j') . ' - ' . $prize->ends_at->format('M j');
        } elseif ($prize->ends_at) {
            $dateRange = 'Ends ' . $prize->ends_at->format('M j');
        } else {
            $dateRange = 'Active this month';
        }

        $imageUrl = $prize->image ?? '';
        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        $phoneNumber = trim((string) ($request->user('sanctum')?->phone_number ?? $request->user()?->phone_number ?? $request->query('phone_number')));

        $hasJoined = false;
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

        return response()->json([
            'id' => (int) $prize->id,
            'title' => (string) $prize->title,
            'image_url' => (string) $imageUrl,
            'date_range' => (string) $dateRange,
            'has_joined' => (bool) $hasJoined,
        ], 200);
    }
}
