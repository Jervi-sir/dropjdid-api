<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    protected $guarded = [];

    public const DIRECTION_IN = 1;
    public const DIRECTION_OUT = 0;

    public const TYPE_DROPS = 'drops';
    public const TYPE_WITHDRAW_DAHABIA = 'withdraw_dahabia';
    public const TYPE_WITHDRAWAL = 'withdrawal';
    public const TYPE_REQUEST_WITHDRAWAL = 'request-withdrawal';
    public const TYPE_SALE = 'sale';
    public const TYPE_STORE_SALES = 'store_sales';
    public const TYPE_REFUND = 'refund';
    public const TYPE_BONUS = 'bonus';

    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $casts = [
        'metadata' => 'array',
        'direction' => 'integer',
        'amount' => 'float',
        'balance_before' => 'float',
        'balance_after' => 'float',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Resolve associated Drop model if available.
     */
    public function getDropAttribute(): ?Drop
    {
        if ($this->source_type === Drop::class || $this->source_type === 'drop') {
            return $this->source;
        }

        $dropId = $this->metadata['drop_id'] ?? null;
        if ($dropId) {
            return Drop::find($dropId);
        }

        return null;
    }

    /**
     * Resolve associated Order model if available.
     */
    public function getOrderAttribute(): ?Order
    {
        if ($this->source_type === Order::class || $this->source_type === 'order') {
            return $this->source;
        }

        $orderId = $this->metadata['order_id'] ?? null;
        if ($orderId) {
            return Order::find($orderId);
        }

        return null;
    }

    /**
     * Resolve associated WithdrawalRequest model if available.
     */
    public function getWithdrawalRequestAttribute(): ?WithdrawalRequest
    {
        if ($this->source_type === WithdrawalRequest::class || $this->source_type === 'withdrawal_request') {
            return $this->source;
        }

        $withdrawalId = $this->metadata['withdrawal_request_id'] ?? $this->metadata['withdrawal_id'] ?? null;
        if ($withdrawalId) {
            return WithdrawalRequest::find($withdrawalId);
        }

        return null;
    }

    /**
     * Helper to normalize image url to an absolute url.
     */
    protected function normalizeImageUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url(ltrim($url, '/'));
    }

    /**
     * Format transaction for Edahabia / BaridiMob / Card withdrawal.
     */
    public function formatDahabiaWithdrawal(): array
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];
        $cardNumber = $metadata['card_number']
            ?? $metadata['dahabia_card']
            ?? $metadata['ccp_number']
            ?? $metadata['phone_number']
            ?? null;

        // Mask card if provided (e.g. Card •••• 1234)
        $cardLabel = $cardNumber
            ? (strlen((string) $cardNumber) > 4 ? 'Edahabia •••• '.substr((string) $cardNumber, -4) : (string) $cardNumber)
            : 'Edahabia Card';

        $text1 = $this->title ?: 'Withdrawal';
        $text2 = $this->reference ?: $cardLabel;

        $imageUrl = $metadata['image_url']
            ?? $metadata['image']
            ?? asset('images/icons/edahabia.png');

        return [
            'id' => (int) $this->id,
            'type' => self::TYPE_WITHDRAW_DAHABIA,
            'text1' => (string) $text1,
            'text2' => (string) $text2,
            'image_url' => $this->normalizeImageUrl($imageUrl),
            'price' => [
                'amount' => (float) $this->amount,
                'direction' => 'minus',
            ],
            'status' => $this->status ?? self::STATUS_COMPLETED,
            'balance_before' => (float) ($this->balance_before ?? 0.00),
            'balance_after' => (float) ($this->balance_after ?? 0.00),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }

    /**
     * Format transaction for a Creator Drop (with drop_id and amount).
     */
    public function formatDrop(): array
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];
        $dropId = $metadata['drop_id'] ?? ($this->source_type === Drop::class ? $this->source_id : null);
        $drop = $this->drop;

        $dropTitle = $drop?->title
            ?? $metadata['drop_title']
            ?? $metadata['title']
            ?? ($this->title && $this->title !== 'Payment' ? $this->title : null)
            ?? ($dropId ? 'Drop #'.$dropId : 'Drop Earnings');

        // Reference format: #Drop_title_slug or reference
        $slug = preg_replace('/[^A-Za-z0-9_]/', '_', str_replace(' ', '_', $dropTitle));
        $defaultRef = '#'.trim($slug, '_');
        $text2 = $this->reference ?: ($dropId ? $defaultRef : '#Drop');

        // Resolve drop image
        $dropImage = $drop?->mainImage?->image
            ?? $drop?->images?->first()?->image
            ?? $metadata['image_url']
            ?? $metadata['image']
            ?? $metadata['cover']
            ?? null;

        return [
            'id' => (int) $this->id,
            'type' => self::TYPE_DROPS,
            'text1' => (string) $dropTitle,
            'text2' => (string) $text2,
            'image_url' => $this->normalizeImageUrl($dropImage),
            'price' => [
                'amount' => (float) $this->amount,
                'direction' => 'plus',
            ],
            'status' => $this->status ?? self::STATUS_COMPLETED,
            'balance_before' => (float) ($this->balance_before ?? 0.00),
            'balance_after' => (float) ($this->balance_after ?? 0.00),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }

    /**
     * Format transaction for a Sale (with order_id and amount).
     */
    public function formatSale(): array
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];
        $orderId = $metadata['order_id'] ?? ($this->source_type === Order::class ? $this->source_id : null);
        $order = $this->order;

        $text1 = $this->title ?: 'Sale';
        $text2 = $this->reference ?: ($orderId ? '#Order_'.$orderId : '#Sale');

        // Resolve order first product image if available
        $orderImage = null;
        if ($order) {
            $firstItem = $order->items()->with(['product.mainImage', 'product.images'])->first();
            $orderImage = $firstItem?->product?->mainImage?->image_url
                ?? $firstItem?->product?->images?->first()?->image_url;
        }

        if (! $orderImage) {
            $orderImage = $metadata['image_url'] ?? $metadata['image'] ?? null;
        }

        return [
            'id' => (int) $this->id,
            'type' => self::TYPE_SALE,
            'text1' => (string) $text1,
            'text2' => (string) $text2,
            'image_url' => $this->normalizeImageUrl($orderImage),
            'price' => [
                'amount' => (float) $this->amount,
                'direction' => 'plus',
            ],
            'status' => $this->status ?? self::STATUS_COMPLETED,
            'balance_before' => (float) ($this->balance_before ?? 0.00),
            'balance_after' => (float) ($this->balance_after ?? 0.00),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }

    /**
     * Format transaction for a Refund (with order_id and amount).
     */
    public function formatRefund(): array
    {
        $metadata = is_array($this->metadata) ? $this->metadata : [];
        $orderId = $metadata['order_id'] ?? ($this->source_type === Order::class ? $this->source_id : null);
        $order = $this->order;

        $text1 = $this->title ?: 'Refund';
        $text2 = $this->reference ?: ($orderId ? '#Order_'.$orderId : '#Refund');

        $direction = ((int) $this->direction === 1 || in_array(strtolower((string) $this->direction), ['1', 'in', 'plus'], true))
            ? 'plus'
            : 'minus';

        // Resolve order item product image if available
        $orderImage = null;
        if ($order) {
            $firstItem = $order->items()->with(['product.mainImage', 'product.images'])->first();
            $orderImage = $firstItem?->product?->mainImage?->image_url
                ?? $firstItem?->product?->images?->first()?->image_url;
        }

        if (! $orderImage) {
            $orderImage = $metadata['image_url'] ?? $metadata['image'] ?? null;
        }

        return [
            'id' => (int) $this->id,
            'type' => self::TYPE_REFUND,
            'text1' => (string) $text1,
            'text2' => (string) $text2,
            'image_url' => $this->normalizeImageUrl($orderImage),
            'price' => [
                'amount' => (float) $this->amount,
                'direction' => $direction,
            ],
            'status' => $this->status ?? self::STATUS_COMPLETED,
            'balance_before' => (float) ($this->balance_before ?? 0.00),
            'balance_after' => (float) ($this->balance_after ?? 0.00),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }

    /**
     * Format generic / default transaction.
     */
    public function formatDefault(): array
    {
        $direction = ((int) $this->direction === 1 || in_array(strtolower((string) $this->direction), ['1', 'in', 'plus'], true))
            ? 'plus'
            : 'minus';

        $metadata = is_array($this->metadata) ? $this->metadata : [];
        $imageUrl = $metadata['image_url'] ?? $metadata['image'] ?? $metadata['cover'] ?? null;

        $defaultTitle = $direction === 'plus' ? 'Earnings' : 'Payment';
        $text1 = $this->title ?? $metadata['title'] ?? $metadata['label'] ?? $defaultTitle;
        $text2 = $this->reference ?? $metadata['reference'] ?? $metadata['note'] ?? '';

        return [
            'id' => (int) $this->id,
            'type' => (string) ($this->type ?? 'transaction'),
            'text1' => (string) $text1,
            'text2' => (string) $text2,
            'image_url' => $this->normalizeImageUrl($imageUrl),
            'price' => [
                'amount' => (float) $this->amount,
                'direction' => $direction,
            ],
            'status' => $this->status ?? self::STATUS_COMPLETED,
            'balance_before' => (float) ($this->balance_before ?? 0.00),
            'balance_after' => (float) ($this->balance_after ?? 0.00),
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
        ];
    }

    /**
     * Automatically format the transaction based on its type / context.
     * Matches the TransactionType frontend interface.
     */
    public function toApiArray(): array
    {
        $type = strtolower((string) ($this->type ?? ''));
        $metadata = is_array($this->metadata) ? $this->metadata : [];

        // 1. Withdraw Dahabia / Withdrawal
        if (
            in_array($type, [self::TYPE_WITHDRAW_DAHABIA, self::TYPE_WITHDRAWAL, self::TYPE_REQUEST_WITHDRAWAL, 'withdraw'], true)
            || ($this->source_type === WithdrawalRequest::class)
            || ! empty($metadata['dahabia_card'])
            || ! empty($metadata['card_number'])
        ) {
            return $this->formatDahabiaWithdrawal();
        }

        // 2. Drop earning
        if (
            in_array($type, [self::TYPE_DROPS, 'drop', 'creator_drop'], true)
            || ($this->source_type === Drop::class)
            || ! empty($metadata['drop_id'])
        ) {
            return $this->formatDrop();
        }

        // 3. Refund
        if (
            in_array($type, [self::TYPE_REFUND, 'order_refund'], true)
            || ! empty($metadata['refund_id'])
        ) {
            return $this->formatRefund();
        }

        // 4. Sale / Store sales
        if (
            in_array($type, [self::TYPE_SALE, self::TYPE_STORE_SALES, 'order_sale'], true)
            || ($this->source_type === Order::class)
            || ! empty($metadata['order_id'])
        ) {
            return $this->formatSale();
        }

        return $this->formatDefault();
    }

    /**
     * Alias for toApiArray().
     */
    public function format(): array
    {
        return $this->toApiArray();
    }
}

