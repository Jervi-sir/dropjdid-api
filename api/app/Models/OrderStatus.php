<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const PROCESSING = 'processing';
    public const SHIPPED = 'shipped';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';
    public const RETURNED = 'returned';

    protected $fillable = [
        'code',
        'en',
        'fr',
        'ar',
        'color',
        'icon',
        'sort_order',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'order_status_code', 'code');
    }
}
