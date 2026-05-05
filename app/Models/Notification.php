<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['notification_type_id', 'notifiable_type', 'notifiable_id', 'data', 'read_at'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function formatterRelations(): array
    {
        return ['notificationType', 'notifiable'];
    }
}
