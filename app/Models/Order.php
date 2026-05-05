<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use FormatsModel, HasFactory;

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
}
