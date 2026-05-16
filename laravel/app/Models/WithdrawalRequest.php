<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WithdrawalRequest extends Model
{
    public const METHOD_BARIDIMOB = 0;

    public const METHOD_CCP = 1;

    public const METHOD_BANK_TRANSFER = 2;

    public const METHOD_CASH = 3;

    public const METHOD = [
        self::METHOD_BARIDIMOB => 'baridimob',
        self::METHOD_CCP => 'ccp',
        self::METHOD_BANK_TRANSFER => 'bank_transfer',
        self::METHOD_CASH => 'cash',
    ];

    public const STATUS_PENDING_IDENTITY_CHECK = 0;

    public const STATUS_PENDING = 1;

    public const STATUS_APPROVED = 2;

    public const STATUS_REJECTED = 3;

    public const STATUS_PAID = 4;

    public const STATUS_CANCELLED = 5;

    public const STATUS_FAILED = 6;

    public const STATUS = [
        self::STATUS_PENDING_IDENTITY_CHECK => 'pending_identity_check',
        self::STATUS_PENDING => 'pending',
        self::STATUS_APPROVED => 'approved',
        self::STATUS_REJECTED => 'rejected',
        self::STATUS_PAID => 'paid',
        self::STATUS_CANCELLED => 'cancelled',
        self::STATUS_FAILED => 'failed',
    ];

    protected $fillable = [
        'wallet_id',
        'user_id',
        'amount',
        'method',
        'status',
        'transaction_id',
        'payment_details',
        'admin_note',
        'identity_checked_at',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'identity_checked_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'transaction_id');
    }
}
