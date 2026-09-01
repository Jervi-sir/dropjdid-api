<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keyword extends Model
{
    protected $guarded = [];

    public function label(): BelongsTo
    {
        return $this->belongsTo(Label::class);
    }
}
