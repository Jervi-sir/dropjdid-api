<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    public const LEVEL_USER = 'user';
    public const LEVEL_STORE = 'store';
    public const LEVEL_CREATOR = 'creator';

    protected $guarded = [];

    /**
     * The owner user of this wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The store associated with this wallet (for store-level wallets).
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * All transactions linked to this wallet.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Determine if this wallet is at store level.
     */
    public function isStoreLevel(): bool
    {
        return $this->level === self::LEVEL_STORE || ! empty($this->store_id);
    }

    /**
     * Determine if this wallet is at user level.
     */
    public function isUserLevel(): bool
    {
        return $this->level === self::LEVEL_USER || empty($this->store_id);
    }
}
