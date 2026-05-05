<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use FormatsModel, HasFactory, SoftDeletes;

    protected $fillable = ['store_id', 'category_id', 'quality_id', 'name', 'description', 'payment_method_id', 'original_price', 'show_price', 'store_price', 'gender_id', 'status'];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'show_price' => 'decimal:2',
            'store_price' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function quality(): BelongsTo
    {
        return $this->belongsTo(Quality::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function productKeywords(): HasMany
    {
        return $this->hasMany(ProductKeyword::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'product_keywords')->withTimestamps();
    }

    public function drops(): BelongsToMany
    {
        return $this->belongsToMany(Drop::class, 'drop_product')
            ->withPivot('drop_price')
            ->withTimestamps();
    }

    public function saves(): MorphMany
    {
        return $this->morphMany(Save::class, 'saveable');
    }

    protected function formatterRelations(): array
    {
        return ['store', 'category', 'quality', 'paymentMethod', 'gender', 'images', 'variants', 'productKeywords', 'orderItems', 'keywords', 'drops', 'saves'];
    }
}
