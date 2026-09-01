<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Models\CreatorFollower;
use App\Models\Drop;
use App\Models\Friendship;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListController extends Controller
{
    /**
     * List user notifications with metadata mapped to NotificationType frontend interface.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

        $query = Notification::query()
            ->with(['type', 'notifiable'])
            ->latest();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Tab filter support (e.g. 'all', 'orders', 'requests')
        $tab = $request->query('tab');
        if ($tab === 'orders') {
            $query->whereHas('type', function ($q) {
                $q->whereIn('code', ['order', 'sale', 'withdraw']);
            });
        } elseif ($tab === 'requests') {
            $query->whereHas('type', function ($q) {
                $q->whereIn('code', ['friend-request', 'follower']);
            });
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $page = max(1, (int) $request->query('page', 1));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (Notification $notification) {
            return $this->formatNotification($notification);
        })->filter()->values();

        $nextPage = $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null;

        return response()->json([
            'data' => $data,
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'next_page' => $nextPage,
        ], 200);
    }

    /**
     * Format a Notification model instance into the frontend NotificationType structure.
     *
     * @param Notification $notification
     * @return array
     */
    protected function formatNotification(Notification $notification): array
    {
        $typeCode = $notification->type?->code ?? 'order';
        $metaData = is_array($notification->data) ? $notification->data : (json_decode($notification->data ?? '[]', true) ?: []);
        $notifiable = $notification->notifiable;

        $imageUrl = $metaData['image_url'] ?? '';

        $saleMeta = null;
        $withdrawMeta = null;
        $orderMeta = null;
        $friendRequestMeta = null;
        $followerMeta = null;

        switch ($typeCode) {
            case 'sale':
                $target = $metaData['target'] ?? ($notifiable instanceof Drop ? 'drop' : 'product');
                $price = (string) ($metaData['price'] ?? '0.00 DZD');
                $direction = in_array($metaData['direction'] ?? '', ['up', 'down']) ? $metaData['direction'] : 'up';
                $text1 = $metaData['text1'] ?? ($notifiable?->title ?? $notifiable?->name ?? 'Sale generated');

                if (! $imageUrl) {
                    if ($notifiable instanceof Drop) {
                        $imageUrl = $notifiable->mainImage?->image_url ?? $notifiable->images->first()?->image_url ?? '';
                    } elseif ($notifiable instanceof Product) {
                        $imageUrl = $notifiable->mainImage?->image_url ?? $notifiable->images->first()?->image_url ?? '';
                    }
                }

                $saleMeta = [
                    'target' => $target,
                    'text1' => (string) $text1,
                    'price' => (string) $price,
                    'direction' => $direction,
                ];
                break;

            case 'withdraw':
                $target = 'edahabia';
                $price = (string) ($metaData['price'] ?? ($notifiable instanceof WithdrawalRequest ? number_format($notifiable->amount ?? 0, 2) . ' DZD' : '0.00 DZD'));
                $direction = in_array($metaData['direction'] ?? '', ['up', 'down']) ? $metaData['direction'] : 'down';
                $text1 = $metaData['text1'] ?? 'Withdrawal processed';

                $withdrawMeta = [
                    'target' => $target,
                    'text1' => (string) $text1,
                    'price' => (string) $price,
                    'direction' => $direction,
                ];
                break;

            case 'friend-request':
                $target = in_array($metaData['target'] ?? '', ['received', 'accepted', 'rejected', 'accepted-by-target'])
                    ? $metaData['target']
                    : 'received';

                $senderUser = null;
                if ($notifiable instanceof Friendship) {
                    $senderUser = $notifiable->sender;
                } elseif ($notifiable instanceof User) {
                    $senderUser = $notifiable;
                }

                $senderName = $senderUser?->full_name ?? $senderUser?->username ?? 'Someone';
                $text1 = $metaData['text1'] ?? $senderName;
                $text2 = $metaData['text2'] ?? match ($target) {
                    'accepted' => 'accepted your friend request',
                    'rejected' => 'declined your friend request',
                    'accepted-by-target' => 'is now your friend',
                    default => 'sent you a friend request',
                };

                if (! $imageUrl && $senderUser) {
                    $imageUrl = $senderUser->image_url ?? '';
                }

                $friendRequestMeta = [
                    'target' => $target,
                    'text1' => (string) $text1,
                    'text2' => (string) $text2,
                ];
                break;

            case 'follower':
                $followerUser = null;
                if ($notifiable instanceof CreatorFollower) {
                    $followerUser = $notifiable->user;
                } elseif ($notifiable instanceof User) {
                    $followerUser = $notifiable;
                }

                $followerName = $followerUser?->full_name ?? $followerUser?->username ?? 'Someone';
                $text1 = $metaData['text1'] ?? $followerName;
                $text2 = $metaData['text2'] ?? 'started following you';

                if (! $imageUrl && $followerUser) {
                    $imageUrl = $followerUser->image_url ?? '';
                }

                $followerMeta = [
                    'text1' => (string) $text1,
                    'text2' => (string) $text2,
                ];
                break;

            case 'order':
            default:
                $typeCode = 'order';
                $orderNumber = $notifiable instanceof Order ? '#' . $notifiable->id : ($metaData['order_id'] ?? '');
                $text1 = $metaData['text1'] ?? ($orderNumber ? "Order {$orderNumber}" : 'Order update');
                $text2 = $metaData['text2'] ?? ($notifiable?->status ?? 'Your order status has been updated');

                $orderMeta = [
                    'text1' => (string) $text1,
                    'text2' => (string) $text2,
                ];
                break;
        }

        // Format image url with domain if relative
        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        return [
            'id' => (int) $notification->id,
            'type' => $typeCode,
            'created_at' => $notification->created_at ? $notification->created_at->toISOString() : now()->toISOString(),
            'image_url' => (string) $imageUrl,
            'sale_meta' => $saleMeta,
            'withdraw_meta' => $withdrawMeta,
            'order_meta' => $orderMeta,
            'friend_request_meta' => $friendRequestMeta,
            'follower_meta' => $followerMeta,
        ];
    }
}
