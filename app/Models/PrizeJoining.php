<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrizeJoining extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['prize_id', 'user_id', 'amount_paid', 'status'];

    protected function casts(): array
    {
        return ['amount_paid' => 'decimal:2'];
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function formatterRelations(): array
    {
        return ['prize', 'user'];
    }
}
