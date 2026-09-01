<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class SupplyRequest extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_SHIPPED_TO_HUB = 'shipped_to_hub';
    public const STATUS_RECEIVED_AT_HUB = 'received_at_hub';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
        'shipped_at' => 'datetime',
        'received_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplyRequestItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get all unique customer orders linked to this supply request batch
     */
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(
            Order::class,
            OrderItem::class,
            'supply_request_id',
            'id',
            'id',
            'order_id'
        )->distinct();
    }
}
