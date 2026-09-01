<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_REJECTED = 'rejected';

    /**
     * Map of supported product status objects.
     *
     * @var array<string, array{code: string, en: string, fr: string, ar: string}>
     */
    public static array $statuses = [
        self::STATUS_DRAFT => [
            'code' => 'draft',
            'en' => 'Draft',
            'fr' => 'Brouillon',
            'ar' => 'مسودة',
        ],
        self::STATUS_PUBLISHED => [
            'code' => 'published',
            'en' => 'Published',
            'fr' => 'Publié',
            'ar' => 'منشور',
        ],
        self::STATUS_ARCHIVED => [
            'code' => 'archived',
            'en' => 'Archived',
            'fr' => 'Archivé',
            'ar' => 'مؤرشف',
        ],
        self::STATUS_REJECTED => [
            'code' => 'rejected',
            'en' => 'Rejected',
            'fr' => 'Rejeté',
            'ar' => 'مرفوض',
        ],
    ];

    /**
     * Format product status as a structured JSON object or null.
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
     * Get formatted product status JSON object for this product.
     *
     * @return array{code: string, en: string, fr: string, ar: string}|null
     */
    public function getProductStatusJsonAttribute(): ?array
    {
        return static::formatStatus($this->product_status);
    }

    /**
     * Format rejection/archive reason or instruction list to a standardized list of instructions.
     * Each item in the array will have: ['instruction' => ['en' => '...', 'fr' => '...', 'ar' => '...']]
     *
     * @param mixed $reason
     * @return array<int, array{instruction: array{en: string, fr: string, ar: string}}>
     */
    public static function formatRejectionReason(mixed $reason): array
    {
        if (empty($reason)) {
            return [];
        }

        // If string passed
        if (is_string($reason)) {
            $reason = trim($reason);
            if ($reason === '') {
                return [];
            }
            return [
                [
                    'instruction' => [
                        'en' => $reason,
                        'fr' => $reason,
                        'ar' => $reason,
                    ],
                ],
            ];
        }

        if (!is_array($reason)) {
            return [];
        }

        // Check if it is already a list of instruction items: [['instruction' => [...]], ...]
        if (isset($reason[0]) && is_array($reason[0]) && isset($reason[0]['instruction'])) {
            return array_values(array_map(function ($item) {
                $inst = $item['instruction'] ?? [];
                if (is_string($inst)) {
                    $inst = ['en' => $inst, 'fr' => $inst, 'ar' => $inst];
                }
                return [
                    'instruction' => [
                        'en' => (string) ($inst['en'] ?? $inst['fr'] ?? $inst['ar'] ?? ''),
                        'fr' => (string) ($inst['fr'] ?? $inst['en'] ?? $inst['ar'] ?? ''),
                        'ar' => (string) ($inst['ar'] ?? $inst['en'] ?? $inst['fr'] ?? ''),
                    ],
                ];
            }, $reason));
        }

        // Check if it is a single multilingual associative array: ['en' => '...', 'fr' => '...', 'ar' => '...']
        if (isset($reason['en']) || isset($reason['fr']) || isset($reason['ar'])) {
            return [
                [
                    'instruction' => [
                        'en' => (string) ($reason['en'] ?? $reason['fr'] ?? $reason['ar'] ?? ''),
                        'fr' => (string) ($reason['fr'] ?? $reason['en'] ?? $reason['ar'] ?? ''),
                        'ar' => (string) ($reason['ar'] ?? $reason['en'] ?? $reason['fr'] ?? ''),
                    ],
                ],
            ];
        }

        // Check if wrapped in message or text key: ['message' => '...']
        if (isset($reason['message']) || isset($reason['text']) || isset($reason['reason'])) {
            $val = $reason['message'] ?? $reason['text'] ?? $reason['reason'];
            if (is_array($val)) {
                return static::formatRejectionReason($val);
            }
            $str = (string) $val;
            return [
                [
                    'instruction' => [
                        'en' => $str,
                        'fr' => $str,
                        'ar' => $str,
                    ],
                ],
            ];
        }

        // Check if indexed list of strings or multilingual objects: ['text 1', 'text 2']
        if (array_is_list($reason)) {
            $formatted = [];
            foreach ($reason as $item) {
                if (is_string($item)) {
                    $formatted[] = [
                        'instruction' => [
                            'en' => $item,
                            'fr' => $item,
                            'ar' => $item,
                        ],
                    ];
                } elseif (is_array($item)) {
                    if (isset($item['instruction'])) {
                        $inst = $item['instruction'];
                        $formatted[] = [
                            'instruction' => is_array($inst) ? [
                                'en' => (string) ($inst['en'] ?? $inst['fr'] ?? $inst['ar'] ?? ''),
                                'fr' => (string) ($inst['fr'] ?? $inst['en'] ?? $inst['ar'] ?? ''),
                                'ar' => (string) ($inst['ar'] ?? $inst['en'] ?? $inst['fr'] ?? ''),
                            ] : [
                                'en' => (string) $inst,
                                'fr' => (string) $inst,
                                'ar' => (string) $inst,
                            ],
                        ];
                    } else {
                        $formatted[] = [
                            'instruction' => [
                                'en' => (string) ($item['en'] ?? $item['fr'] ?? $item['ar'] ?? ''),
                                'fr' => (string) ($item['fr'] ?? $item['en'] ?? $item['ar'] ?? ''),
                                'ar' => (string) ($item['ar'] ?? $item['en'] ?? $item['fr'] ?? ''),
                            ],
                        ];
                    }
                }
            }
            return $formatted;
        }

        return [];
    }

    /**
     * Get formatted rejection reason as list of instructions.
     *
     * @return array<int, array{instruction: array{en: string, fr: string, ar: string}}>
     */
    public function getFormattedRejectionReasonAttribute(): array
    {
        return static::formatRejectionReason($this->rejection_reason);
    }

    protected $fillable = [
        'store_id',
        'category_id',
        'gender_id',
        'quality_id',
        'name',
        'description',
        'price_original',
        'price_shown',
        'price_store',
        'product_status',
        'rejection_reason',
        'is_affiliate',
        'refreshed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'price_original' => 'decimal:2',
            'price_shown' => 'decimal:2',
            'price_store' => 'decimal:2',
            'is_affiliate' => 'boolean',
            'rejection_reason' => 'array',
            'refreshed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }


    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order', 'asc');
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function savedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_products', 'product_id', 'user_id')->withTimestamps();
    }

    public function likedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'liked_products', 'product_id', 'user_id')->withTimestamps();
    }

    public function saves(): HasMany
    {
        return $this->hasMany(SavedProduct::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'product_keywords', 'product_id', 'label_id');
    }

    public function productKeywords(): HasMany
    {
        return $this->hasMany(ProductKeyword::class);
    }

    public function drops(): BelongsToMany
    {
        return $this->belongsToMany(Drop::class, 'drop_products', 'product_id', 'drop_id')
            ->withPivot(['drop_price'])
            ->withTimestamps();
    }

    public function quality(): BelongsTo
    {
        return $this->belongsTo(Quality::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'product_variants', 'product_id', 'size_id');
    }
}

