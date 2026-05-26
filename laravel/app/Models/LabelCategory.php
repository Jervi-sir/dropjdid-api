<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelCategory extends Model
{
    use FormatsModel;

    protected $fillable = ['code', 'en', 'fr', 'ar'];

    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    protected function formatterRelations(): array
    {
        return ['labels'];
    }
}
