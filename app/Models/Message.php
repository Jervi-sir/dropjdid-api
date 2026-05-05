<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Message extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['conversation_id', 'sender_id', 'type', 'body', 'attachable_type', 'attachable_id'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function formatterRelations(): array
    {
        return ['conversation', 'sender', 'attachable'];
    }
}
