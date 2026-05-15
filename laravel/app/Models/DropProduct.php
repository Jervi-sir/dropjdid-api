<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DropProduct extends Model
{
    use FormatsModel, HasFactory;

    protected $table = 'drop_product';

    protected $fillable = ['drop_id', 'product_id', 'drop_price'];

    protected function casts(): array
    {
        return ['drop_price' => 'decimal:2'];
    }

    public function drop(): BelongsTo
    {
        return $this->belongsTo(Drop::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function formatterRelations(): array
    {
        return ['drop', 'product'];
    }
}
