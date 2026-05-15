<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaBackup extends Model
{
    protected $fillable = [
        'disk',
        'directory',
        'name',
        'original_name',
        'mime_type',
        'size',
        'path',
        'url',
        'collection',
        'mediable_type',
        'mediable_id',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }
}
