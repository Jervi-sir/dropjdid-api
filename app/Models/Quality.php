<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quality extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['code', 'en', 'fr', 'ar'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected function formatterRelations(): array
    {
        return ['products'];
    }
}
