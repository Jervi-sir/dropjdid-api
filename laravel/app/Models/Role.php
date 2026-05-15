<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use FormatsModel, HasFactory;

    public const USER = 'user';

    public const CREATOR = 'creator';

    public const SGM = 'sgm';

    protected $fillable = ['code', 'description', 'en', 'fr', 'ar'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    protected function formatterRelations(): array
    {
        return ['users'];
    }
}
