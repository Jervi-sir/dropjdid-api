<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DropImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'drop_id',
        'image',
        'sort_order',
        'is_main',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function drop(): BelongsTo
    {
        return $this->belongsTo(Drop::class);
    }
}
