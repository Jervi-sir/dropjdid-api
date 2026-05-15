<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSupportRequest extends Model
{
    protected $fillable = [
        'user_id',
        'contact',
        'type',
        'status',
        'note',
        'reviewed_at',
        'target',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }
}
