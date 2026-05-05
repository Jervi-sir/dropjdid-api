<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drop extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['creator_id', 'title', 'description', 'starts_at', 'ends_at', 'status'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'drop_product')
            ->withPivot('drop_price')
            ->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(DropImage::class)->orderBy('sort_order');
    }

    public function savedDrops(): HasMany
    {
        return $this->hasMany(SavedDrop::class);
    }

    public function likedDrops(): HasMany
    {
        return $this->hasMany(LikedDrop::class);
    }
}
