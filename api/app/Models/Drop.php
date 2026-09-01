<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Drop extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'title',
        'description',
        'drop_status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'rejection_reason' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(DropImage::class)->orderBy('sort_order', 'asc');
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(DropImage::class)->where('is_main', true);
    }

    public function likedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'liked_drops', 'drop_id', 'user_id')->withTimestamps();
    }

    public function savedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_drops', 'drop_id', 'user_id')->withTimestamps();
    }

    public function dropProducts(): HasMany
    {
        return $this->hasMany(DropProduct::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'drop_products', 'drop_id', 'product_id')
            ->withPivot('drop_price')
            ->withTimestamps();
    }
}
