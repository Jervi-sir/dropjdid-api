<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilaya extends Model
{
    protected $guarded = [];

    /**
     * Get the communes for this wilaya.
     */
    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class);
    }
}
