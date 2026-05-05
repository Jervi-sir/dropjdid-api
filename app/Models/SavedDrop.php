<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedDrop extends Model
{
    protected $fillable = ['user_id', 'drop_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function drop(): BelongsTo
    {
        return $this->belongsTo(Drop::class);
    }
}
