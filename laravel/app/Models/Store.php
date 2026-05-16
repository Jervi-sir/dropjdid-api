<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use FormatsModel, HasFactory;

    public const STATUS_PENDING = 0;

    public const STATUS_ACTIVE = 1;

    public const STATUS_SUSPENED = 2;

    public const STATUSES = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_ACTIVE => 'active',
        self::STATUS_SUSPENED => 'suspended',
    ];

    protected $fillable = ['user_id', 'wilaya_id', 'store_name', 'phone_number', 'password', 'logo', 'description', 'balance', 'status'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'password' => 'hashed',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class);
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
        return ['user', 'wilaya', 'products', 'orders'];
    }
}
