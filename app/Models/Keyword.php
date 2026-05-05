<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keyword extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['label_id', 'code'];

    public function label(): BelongsTo
    {
        return $this->belongsTo(Label::class);
    }

    public function productKeywords(): HasMany
    {
        return $this->hasMany(ProductKeyword::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_keywords')->withTimestamps();
    }

    protected function formatterRelations(): array
    {
        return ['label', 'productKeywords', 'products'];
    }
}
