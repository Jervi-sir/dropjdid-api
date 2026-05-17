<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StoreWalletTransaction extends Model
{
    public const DIRECTION_IN = 0;

    public const DIRECTION_OUT = 1;

    public const DIRECTION = [
        self::DIRECTION_IN => 'in',
        self::DIRECTION_OUT => 'out',
    ];

    public const STATUS_PENDING = 0;

    public const STATUS_COMPLETED = 1;

    public const STATUS_FAILED = 2;

    public const STATUS_CANCELLED = 3;

    public const STATUS = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_COMPLETED => 'completed',
        self::STATUS_FAILED => 'failed',
        self::STATUS_CANCELLED => 'cancelled',
    ];

    public const TYPE_DROPS = 0;

    public const TYPE_REFUND = 1;

    public const TYPE_BONUS = 2;

    public const TYPE_REQUEST_WITHDRAWAL = 3;

    public const TYPES = [
        self::TYPE_DROPS => 'drops',
        self::TYPE_REFUND => 'refund',
        self::TYPE_BONUS => 'bonus',
        self::TYPE_REQUEST_WITHDRAWAL => 'request-withdrawal',
    ];

    protected $fillable = [
        'wallet_id',
        'store_id',
        'direction',
        'type',
        'status',
        'amount',
        'balance_before',
        'balance_after',
        'title',
        'reference',
        'source_type',
        'source_id',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function getTypeAttribute(int $value): string
    {
        return self::TYPES[$value] ?? (is_string($value) ? $value : 'unknown');
    }

    public function setTypeAttribute(int $value): void
    {
        if (is_numeric($value)) {
            $this->attributes['type'] = (int) $value;
        } else {
            $flipped = array_flip(self::TYPES);
            $this->attributes['type'] = $flipped[$value] ?? self::TYPE_DROPS;
        }
    }

    public function getDirectionAttribute(int $value): string
    {
        return self::DIRECTION[$value] ?? (is_string($value) ? $value : 'unknown');
    }

    public function setDirectionAttribute(int $value): void
    {
        if (is_numeric($value)) {
            $this->attributes['direction'] = (int) $value;
        } else {
            $flipped = array_flip(self::DIRECTION);
            $this->attributes['direction'] = $flipped[$value] ?? self::DIRECTION_IN;
        }
    }

    public function getStatusAttribute(int $value): string
    {
        return self::STATUS[$value] ?? (is_string($value) ? $value : 'unknown');
    }

    public function setStatusAttribute(int $value): void
    {
        if (is_numeric($value)) {
            $this->attributes['status'] = (int) $value;
        } else {
            $flipped = array_flip(self::STATUS);
            $this->attributes['status'] = $flipped[$value] ?? self::STATUS_PENDING;
        }
    }

    public function storeWallet(): BelongsTo
    {
        return $this->belongsTo(StoreWallet::class, 'wallet_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function storeWithdrawalRequest(): HasOne
    {
        return $this->hasOne(StoreWithdrawalRequest::class);
    }
}
