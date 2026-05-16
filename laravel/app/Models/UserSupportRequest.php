<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSupportRequest extends Model
{
    public const TYPE_PHONE_NUMBER = 0;

    public const TYPE_USERNAME = 1;

    public const TYPE_EMAIL = 2;

    public const TYPES = [
        self::TYPE_PHONE_NUMBER => 'phone_number',
        self::TYPE_USERNAME => 'username',
        self::TYPE_EMAIL => 'email',
    ];

    public const STATUS_PENDING = 0;

    public const STATUS_APPROVED = 1;

    public const STATUS_REJECTED = 2;

    public const STATUS = [
        self::STATUS_PENDING => 'pending',
        self::STATUS_APPROVED => 'approved',
        self::STATUS_REJECTED => 'rejected',
    ];

    public const TARGET_FORGOT_PASSWORD = 0;

    public const TARGET_BECOME_CREATOR = 1;

    public const TARGET_BECOME_SGM = 2;

    public const TARGET_CONTACT_SUPPORT = 3;

    public const TARGETS = [
        self::TARGET_FORGOT_PASSWORD => 'forgot-password',
        self::TARGET_BECOME_CREATOR => 'become-creator',
        self::TARGET_BECOME_SGM => 'become-sgm',
        self::TARGET_CONTACT_SUPPORT => 'contact-support',
    ];

    protected $fillable = [
        'user_id',
        'contact',
        'type',
        'status',
        'note',
        'reviewed_at',
        'target',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }
}
