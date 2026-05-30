<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreWallet extends Model
{
    public const TYPE_BALANCE = 0;

    public const TYPE_REFUND = 1;

    public const TYPES = [
        self::TYPE_BALANCE => 'balance',
        self::TYPE_REFUND => 'refund',
    ];

    public const STATUS_NEW = 0;

    public const STATUS_PENDING = 1;

    public const STATUS_VERIFIED = 2;

    public const STATUS_BLOCKED = 3;

    public const STATUS_REJECTED = 4;

    public const STATUSES = [
        self::STATUS_NEW => 'new',
        self::STATUS_PENDING => 'pending',
        self::STATUS_VERIFIED => 'verified',
        self::STATUS_BLOCKED => 'blocked',
        self::STATUS_REJECTED => 'rejected',
    ];

    protected $fillable = [
        'store_id',
        'type',
        'balance',
        'pending_balance',
        'is_identity_verified',
        'status',
        'currency',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'is_identity_verified' => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StoreWalletTransaction::class, 'store_wallet_id');
    }
}
