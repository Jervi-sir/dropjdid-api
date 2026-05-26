<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Label extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['code', 'label_category_id', 'en', 'fr', 'ar'];

    public function labelCategory(): BelongsTo
    {
        return $this->belongsTo(LabelCategory::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(Keyword::class);
    }

    public function savedLabels(): HasMany
    {
        return $this->hasMany(SavedLabel::class);
    }

    protected function formatterRelations(): array
    {
        return ['keywords'];
    }

    public function feedName(): string
    {
        return $this->en ?? $this->code;
    }

    /**
     * @param  array{data: Collection<int, array>, next_page: ?int}  $productsPayload
     */
    public function formatFeedSection(array $productsPayload, int $nbLikes, bool $isLiked): array
    {
        return [
            'type' => 'label',
            'label' => [
                'id' => $this->id,
                'code' => $this->code,
                'en' => $this->en,
                'fr' => $this->fr,
                'ar' => $this->ar,
                'is_liked' => $isLiked,
            ],
            'products' => $productsPayload['data']->values()->all(),
            'nb_likes' => $nbLikes,
            'next_page' => $productsPayload['next_page'],
        ];
    }
}
