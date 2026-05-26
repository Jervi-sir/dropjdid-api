<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    protected $fillable = ['user_id', 'wilaya_id', 'store_name', 'phone_number', 'password', 'logo', 'description', 'balance', 'status', 'is_verified', 'password_plaintext'];

    protected $hidden = ['password', 'password_plaintext'];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'password' => 'hashed',
            'is_verified' => 'boolean',
        ];
    }

    public function getStatusDetailsAttribute(): array
    {
        $statusVal = $this->status;

        if (is_numeric($statusVal)) {
            $statusCode = self::STATUSES[(int) $statusVal] ?? 'pending';
        } else {
            $statusCode = is_string($statusVal) ? $statusVal : 'pending';
        }

        return [
            'code' => $statusCode,
            'en' => ucfirst($statusCode),
            'fr' => match ($statusCode) {
                'active' => 'Actif',
                'suspended' => 'Suspendu',
                default => 'En attente',
            },
            'ar' => match ($statusCode) {
                'active' => 'نشط',
                'suspended' => 'معلق',
                default => 'قيد الانتظار',
            },
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

    public function storeWallets(): HasMany
    {
        return $this->hasMany(StoreWallet::class);
    }

    public function balanceWallet(): HasOne
    {
        return $this->hasOne(StoreWallet::class)->where('type', StoreWallet::TYPE_BALANCE);
    }

    public function refundWallet(): HasOne
    {
        return $this->hasOne(StoreWallet::class)->where('type', StoreWallet::TYPE_REFUND);
    }

    protected function formatterRelations(): array
    {
        return ['user', 'wilaya', 'products', 'orders', 'storeWallets'];
    }
}
