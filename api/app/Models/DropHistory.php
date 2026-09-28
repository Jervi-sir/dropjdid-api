<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DropHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'drop_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'note',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function drop(): BelongsTo
    {
        return $this->belongsTo(Drop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a history event for a drop.
     */
    public static function record(
        int|Drop $drop,
        string $action,
        ?int $userId = null,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $note = null,
        ?array $metadata = null
    ): self {
        $dropId = $drop instanceof Drop ? $drop->id : $drop;

        return self::create([
            'drop_id' => $dropId,
            'user_id' => $userId,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
            'metadata' => $metadata,
        ]);
    }
}
