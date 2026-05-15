<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorFollower extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['user_id', 'creator_id'];

    protected $table = 'creator_followers';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    protected function formatterRelations(): array
    {
        return ['user', 'creator'];
    }
}
