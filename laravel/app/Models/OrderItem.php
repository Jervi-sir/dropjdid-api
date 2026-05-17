<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['order_id', 'product_id', 'drop_id', 'size_id', 'product_name', 'quantity', 'unit_price', 'total_price'];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'total_price' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function drop(): BelongsTo
    {
        return $this->belongsTo(Drop::class);
    }

    public function isOnline(): bool
    {
        return $this->order?->isOnline() ?? false;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    protected function formatterRelations(): array
    {
        return ['order', 'product'];
    }
}
