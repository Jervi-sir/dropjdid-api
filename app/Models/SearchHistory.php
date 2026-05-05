<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['user_id', 'query', 'type'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function formatterRelations(): array
    {
        return ['user'];
    }
}
