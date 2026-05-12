<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use FormatsModel, HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'wilaya_id', 'store_id', 'order_number', 'payment_method_id', 'full_name', 'phone_number', 'wilaya', 'baladiya', 'home_address', 'delivery_method', 'delivery_fees', 'subtotal', 'total', 'status', 'has_claim_issue', 'claim_issue'];

    protected function casts(): array
    {
        return [
            'delivery_fees' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'has_claim_issue' => 'boolean',
        ];
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
            'created_at' => $this->created_at?->toISOString(),
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) $item->total_price,
                ];
            }),
        ];
    }


    private function formatStatusForMobile(): string
    {
        return in_array($this->status, [
            'pending',
            'confirmed',
            'processing',
            'shipped',
            'delivered',
            'cancelled',
            'returned',
        ]) ? $this->status : 'pending';
    }
}
