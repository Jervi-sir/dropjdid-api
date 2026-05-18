<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use FormatsModel, HasFactory, SoftDeletes;

    public const STATUS_PENDING = 0;

    public const STATUS_CONFIRMED = 1;

    public const STATUS_PROCESSING = 2;

    public const STATUS_SHIPPED = 3;

    public const STATUS_DELIVERED = 4;

    public const STATUS_CANCELLED = 5;

    public const STATUS_RETURNED = 6;

    public const STATUS = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_CONFIRMED => 'confirmed',
        self::STATUS_PROCESSING => 'processing',
        self::STATUS_SHIPPED => 'shipped',
        self::STATUS_DELIVERED => 'delivered',
        self::STATUS_CANCELLED => 'cancelled',
        self::STATUS_RETURNED => 'returned',
    ];

    public const DELIVERY_METHOD_HOME = 0;

    public const DELIVERY_METHOD_DESK = 1;

    public const DELIVERY_METHOD = [
        self::DELIVERY_METHOD_HOME => 'home',
        self::DELIVERY_METHOD_DESK => 'desk',
    ];

    protected $fillable = ['user_id', 'wilaya_id', 'store_id', 'order_number', 'payment_method_id', 'full_name', 'phone_number', 'wilaya', 'baladiya', 'home_address', 'delivery_method', 'delivery_fees', 'subtotal', 'total', 'status', 'has_claim_issue', 'claim_issue'];

    protected function casts(): array
    {
        return [
            'delivery_fees' => 'integer',
            'subtotal' => 'integer',
            'total' => 'integer',
            'has_claim_issue' => 'boolean',
            'status' => 'integer',
            'delivery_method' => 'integer',
        ];
    }

    public function setStatusAttribute(mixed $value): void
    {
        if (is_string($value)) {
            $key = array_search($value, self::STATUS, true);
            $this->attributes['status'] = $key !== false ? $key : self::STATUS_PENDING;
        } else {
            $this->attributes['status'] = $value;
        }
    }

    public function getStatusAttribute(mixed $value): string
    {
        return self::STATUS[$value] ?? 'pending';
    }

    public function setDeliveryMethodAttribute(mixed $value): void
    {
        if (is_string($value)) {
            $key = array_search($value, self::DELIVERY_METHOD, true);
            $this->attributes['delivery_method'] = $key !== false ? $key : self::DELIVERY_METHOD_HOME;
        } else {
            $this->attributes['delivery_method'] = $value;
        }
    }

    public function getDeliveryMethodAttribute(mixed $value): string
    {
        return self::DELIVERY_METHOD[$value] ?? 'home';
    }

    public function isOnline(): bool
    {
        return (bool) $this->paymentMethod?->is_online;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    protected function formatterRelations(): array
    {
        return ['user', 'store', 'paymentMethod', 'items', 'conversations'];
    }

    public function formatForList(): array
    {
        $firstItem = $this->items->first();
        $firstProduct = $firstItem?->product;

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'type' => $this->paymentMethod?->code === 'online' ? 'online' : 'cod',
            'is_online' => $this->isOnline(),
            'status' => $this->formatStatusForMobile(),
            'image' => $firstProduct?->images->sortBy('sort_order')->first()?->image,
            'product_name' => $firstItem?->product_name,
            'total' => (float) $this->total,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }

    public function formatForDetail(): array
    {
        $firstItem = $this->items->first();
        $firstProduct = $firstItem?->product;

        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->formatStatusForMobile(),
            'image' => $firstProduct?->images->sortBy('sort_order')->first()?->image,
            'product_name' => $firstItem?->product_name,
            'total' => (float) $this->total,
            'payment_method' => $this->paymentMethod?->en,
            'is_online' => $this->isOnline(),
            'created_at' => $this->created_at?->toISOString(),
            'full_name' => $this->full_name,
            'phone_number' => $this->phone_number,
            'wilaya' => $this->wilaya,
            'baladiya' => $this->baladiya,
            'home_address' => $this->home_address,
            'delivery_method' => $this->delivery_method,
            'delivery_fees' => (float) $this->delivery_fees,
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                    'is_online' => $this->isOnline(),
                    'size' => $item->size?->code ?? $item->size?->en ?? $item->size?->fr ?? $item->size?->ar,
                ];
            }),
        ];
    }

    private function formatStatusForMobile(): string
    {
        return $this->status;
    }
}
