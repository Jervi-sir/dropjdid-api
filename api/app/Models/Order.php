<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function wilayaModel(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'wilaya_id');
    }

    public function statusModel(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_code', 'code');
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_code', 'code');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * All supply requests tied to this order's items.
     */
    public function supplyRequests(): HasManyThrough
    {
        return $this->hasManyThrough(
            SupplyRequest::class,
            OrderItem::class,
            'order_id',
            'id',
            'id',
            'supply_request_id'
        )->distinct();
    }

    /**
     * Check if all items in this order have arrived at the hub.
     */
    public function isReadyToBox(): bool
    {
        return $this->items()->where('fulfillment_status', '!=', 'in_hub')->doesntExist();
    }
}
