<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    use FormatsModel, HasFactory;

    public const TYPE_GENERAL = 0;

    public const TYPE_PRODUCT = 1;

    public const TYPE_STORE = 2;

    public const TYPE_CREATOR = 3;

    public const TYPES = [
        self::TYPE_GENERAL => 'general',
        self::TYPE_PRODUCT => 'product',
        self::TYPE_STORE => 'store',
        self::TYPE_CREATOR => 'creator',
    ];

    protected $fillable = ['user_id', 'query', 'type'];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
        ];
    }

    public function setTypeAttribute(mixed $value): void
    {
        if (is_string($value)) {
            $key = array_search($value, self::TYPES, true);
            $this->attributes['type'] = $key !== false ? $key : self::TYPE_GENERAL;
        } else {
            $this->attributes['type'] = $value;
        }
    }

    public function getTypeAttribute(mixed $value): string
    {
        return self::TYPES[$value] ?? 'general';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function formatterRelations(): array
    {
        return ['user'];
    }
}

