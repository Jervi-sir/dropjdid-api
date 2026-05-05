<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialPlatform extends Model
{
    use FormatsModel, HasFactory;

    protected $fillable = ['code'];

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    protected function formatterRelations(): array
    {
        return ['contacts'];
    }
}
