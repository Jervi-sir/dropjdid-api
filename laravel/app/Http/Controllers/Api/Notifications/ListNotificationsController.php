<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListNotificationsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'types' => ['nullable', 'array'],
            'types.*' => ['string', 'in:sales,withdraw,tracking_order,friend_request,followers'],
        ]);

        $user = $request->user();
        $perPage = $validated['per_page'] ?? 10;
        $types = $validated['types'] ?? [];

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->with('notificationType')
            ->when($types !== [], function ($query) use ($types): void {
                $query->whereHas('notificationType', function ($notificationTypeQuery) use ($types): void {
                    $notificationTypeQuery->whereIn('code', $types);
                });
            })
            ->latest()
            ->simplePaginate($perPage);

        return response()->json([
            'data' => collect($notifications->items())
                ->map(fn (Notification $notification): array => $notification->formatForList())
                ->values(),
            'next_page' => $notifications->hasMorePages() ? $notifications->currentPage() + 1 : null,
        ]);
    }
}
