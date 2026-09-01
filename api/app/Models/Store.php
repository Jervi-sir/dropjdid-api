<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Store extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    /**
     * Map of supported store status objects.
     *
     * @var array<string, array{code: string, en: string, fr: string, ar: string}>
     */
    public static array $statuses = [
        self::STATUS_PENDING => [
            'code' => 'pending',
            'en' => 'Pending',
            'fr' => 'En attente',
            'ar' => 'قيد الانتظار',
        ],
        self::STATUS_ACTIVE => [
            'code' => 'active',
            'en' => 'Active',
            'fr' => 'Actif',
            'ar' => 'نشط',
        ],
        self::STATUS_SUSPENDED => [
            'code' => 'suspended',
            'en' => 'Suspended',
            'fr' => 'Suspendu',
            'ar' => 'معلق',
        ],
    ];

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class);
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class)->where('level', 'store');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function deliveryCosts(): HasMany
    {
        return $this->hasMany(StoreToDeliveryCost::class);
    }

    /**
     * Format store status as a structured JSON object or null.
     *
     * @param string|null $status
     * @return array{code: string, en: string, fr: string, ar: string}|null
     */
    public static function formatStatus(?string $status): ?array
    {
        if ($status === null || $status === '') {
            return null;
        }

        $normalized = strtolower(trim($status));

        return static::$statuses[$normalized] ?? [
            'code' => $normalized,
            'en' => ucfirst($normalized),
            'fr' => ucfirst($normalized),
            'ar' => $normalized,
        ];
    }

    /**
     * Get formatted store status JSON object for this store.
     *
     * @return array{code: string, en: string, fr: string, ar: string}|null
     */
    public function getStoreStatusJsonAttribute(): ?array
    {
        return static::formatStatus($this->store_status);
    }

    public function supplyRequests(): HasMany
    {
        return $this->hasMany(SupplyRequest::class);
    }
}
