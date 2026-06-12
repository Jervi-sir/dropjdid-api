<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DropImage extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['drop_id', 'image', 'sort_order', 'is_main'];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? str_replace('http://localhost:8001', 'http://192.168.100.116:8001', $value) : null,
        );
    }

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_main' => 'boolean',
        ];
    }

    public function drop(): BelongsTo
    {
        return $this->belongsTo(Drop::class);
    }

    protected function formatterRelations(): array
    {
        return ['drop'];
    }
}
