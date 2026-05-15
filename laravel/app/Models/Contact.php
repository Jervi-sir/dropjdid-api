<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['user_id', 'social_platform_id', 'url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function socialPlatform(): BelongsTo
    {
        return $this->belongsTo(SocialPlatform::class);
    }

    protected function formatterRelations(): array
    {
        return ['user', 'socialPlatform'];
    }
}
