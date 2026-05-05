<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Label extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['code', 'en', 'fr', 'ar'];

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    protected function formatterRelations(): array
    {
        return ['keywords'];
    }
}
