<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;

class Notification extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['notification_type_id', 'user_id', 'notifiable_type', 'notifiable_id', 'data', 'read_at'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function formatterRelations(): array
    {
        return ['notificationType', 'user', 'notifiable'];
    }

    public function formatForList(): array
    {
        $data = is_array($this->data) ? $this->data : [];
        $type = $this->notificationType?->code;

        return [
            'id' => $this->id,
            'type' => $type,
            'title' => $this->resolveTitle($type, $data),
            'message' => $this->resolveMessage($type, $data),
            'amount' => $this->resolveAmount($data),
            'image' => Arr::get($data, 'image'),
            'user' => [
                'id' => Arr::get($data, 'user.id', Arr::get($data, 'user_id', Arr::get($data, 'sender_id', Arr::get($data, 'creator_id')))),
                'name' => Arr::get($data, 'user.name', Arr::get($data, 'username', Arr::get($data, 'sender_name', Arr::get($data, 'creator_name')))),
                'image' => Arr::get($data, 'user.image', Arr::get($data, 'user_image', Arr::get($data, 'sender_image', Arr::get($data, 'creator_image')))),
            ],
            'order' => [
                'id' => Arr::get($data, 'order.id', Arr::get($data, 'order_id')),
                'number' => Arr::get($data, 'order.number', Arr::get($data, 'order_number')),
                'status' => Arr::get($data, 'order.status', Arr::get($data, 'status')),
            ],
            'friend_request' => [
                'id' => Arr::get($data, 'friend_request.id', Arr::get($data, 'friendship_id')),
                'status' => Arr::get($data, 'friend_request.status', Arr::get($data, 'status')),
            ],
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'time_ago' => $this->created_at?->diffForHumans(),
        ];
    }

    private function resolveTitle(?string $type, array $data): ?string
    {
        return match ($type) {
            'sales' => Arr::get($data, 'title', Arr::get($data, 'drop_title', Arr::get($data, 'product_name', 'Sale'))),
            'withdraw' => Arr::get($data, 'title', Arr::get($data, 'method', 'Withdraw')),
            'tracking_order' => Arr::get($data, 'title', Arr::get($data, 'order.number') !== null ? 'Order #'.Arr::get($data, 'order.number') : (Arr::get($data, 'order_number') !== null ? 'Order #'.Arr::get($data, 'order_number') : 'Order update')),
            'friend_request' => Arr::get($data, 'title', Arr::get($data, 'sender_name', 'Friend request')),
            'followers' => Arr::get($data, 'title', Arr::get($data, 'creator_name', Arr::get($data, 'username', 'New follower'))),
            default => Arr::get($data, 'title'),
        };
    }

    private function resolveMessage(?string $type, array $data): ?string
    {
        return match ($type) {
            'sales' => Arr::get($data, 'message', 'Sale completed'),
            'withdraw' => Arr::get($data, 'message', 'Withdrawal processed'),
            'tracking_order' => Arr::get($data, 'message', Arr::get($data, 'order.status', Arr::get($data, 'status', 'Order updated'))),
            'friend_request' => Arr::get($data, 'message', 'Sent you a friend request'),
            'followers' => Arr::get($data, 'message', 'Followed you'),
            default => Arr::get($data, 'message'),
        };
    }

    private function resolveAmount(array $data): ?float
    {
        $amount = Arr::get($data, 'amount');

        return is_numeric($amount) ? (float) $amount : null;
    }
}
