<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Prize extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['creator_id', 'title', 'image', 'description', 'starts_at', 'ends_at', 'joining_price', 'status'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'joining_price' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function joinings(): HasMany
    {
        return $this->hasMany(PrizeJoining::class);
    }

    public function saves(): MorphMany
    {
        return $this->morphMany(Save::class, 'saveable');
    }

    protected function formatterRelations(): array
    {
        return ['creator', 'joinings', 'saves'];
    }
}
