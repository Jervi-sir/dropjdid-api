<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Save extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['user_id', 'saveable_type', 'saveable_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function saveable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function formatterRelations(): array
    {
        return ['user', 'saveable'];
    }
}
