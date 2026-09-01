<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'en',
        'fr',
        'ar',
    ];

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}
