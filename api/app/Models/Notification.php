<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory;

    public const TYPE_SALE = 'sale';
    public const TYPE_WITHDRAW = 'withdraw';
    public const TYPE_ORDER = 'order';
    public const TYPE_FRIEND_REQUEST = 'friend-request';
    public const TYPE_FOLLOWER = 'follower';

    public const TYPES = [
        self::TYPE_SALE,
        self::TYPE_WITHDRAW,
        self::TYPE_ORDER,
        self::TYPE_FRIEND_REQUEST,
        self::TYPE_FOLLOWER,
    ];

    protected $fillable = [
        'type',
        'user_id',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Create a Sale notification (e.g. drop sale with profit).
     */
    public static function createSaleNotification(
        int $userId,
        Model $notifiable,
        string $dropName,
        string $price,
        string $imageUrl = '',
        string $target = 'drop',
        string $direction = 'up'
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_SALE,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->getKey(),
            'data' => [
                'target' => $target,
                'text1' => $dropName,
                'price' => $price,
                'direction' => $direction,
                'image_url' => $imageUrl,
            ],
        ]);
    }

    /**
     * Create a Withdraw notification.
     */
    public static function createWithdrawNotification(
        int $userId,
        Model $notifiable,
        string $price,
        string $text1 = 'Withdrawal processed',
        string $target = 'edahabia',
        string $direction = 'down',
        string $imageUrl = ''
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_WITHDRAW,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->getKey(),
            'data' => [
                'target' => $target,
                'text1' => $text1,
                'price' => $price,
                'direction' => $direction,
                'image_url' => $imageUrl,
            ],
        ]);
    }

    /**
     * Create an Order status notification.
     */
    public static function createOrderNotification(
        int $userId,
        Model $notifiable,
        string $text1,
        string $text2,
        string $imageUrl = ''
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_ORDER,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->getKey(),
            'data' => [
                'text1' => $text1,
                'text2' => $text2,
                'image_url' => $imageUrl,
            ],
        ]);
    }

    /**
     * Create a Friend Request notification.
     */
    public static function createFriendRequestNotification(
        int $userId,
        Model $notifiable,
        string $senderName,
        string $imageUrl = '',
        string $target = 'received',
        ?string $text2 = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_FRIEND_REQUEST,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->getKey(),
            'data' => [
                'target' => $target,
                'text1' => $senderName,
                'text2' => $text2,
                'image_url' => $imageUrl,
            ],
        ]);
    }

    /**
     * Create a Follower notification.
     */
    public static function createFollowerNotification(
        int $userId,
        Model $notifiable,
        string $followerName,
        string $imageUrl = '',
        string $text2 = 'started following you'
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_FOLLOWER,
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->getKey(),
            'data' => [
                'text1' => $followerName,
                'text2' => $text2,
                'image_url' => $imageUrl,
            ],
        ]);
    }
}
