<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelCategory extends Model
{
    protected $guarded = [];

    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }
}
