<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use FormatsModel, HasFactory;

    public const COD = 'cod';

    public const ONLINE = 'online';

    protected $fillable = ['code', 'en', 'fr', 'ar', 'is_online'];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    protected function formatterRelations(): array
    {
        return ['products', 'orders'];
    }
}
