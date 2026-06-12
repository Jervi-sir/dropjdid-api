<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Advertisement extends Model
{
    use FormatsModel, HasFactory;

    private const FEED_INSERT_INTERVAL = 1;

    public const STATUS_DRAFT = 0;

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 2;

    public const STATUS = [
        self::STATUS_DRAFT => 'draft',
        self::STATUS_ACTIVE => 'active',
        self::STATUS_INACTIVE => 'inactive',
    ];

    protected $fillable = [
        'title',
        'description',
        'image',
        'url',
        'status',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (is_string($value) && ! is_numeric($value)) {
                    $statusMap = array_flip(self::STATUS);

                    return $statusMap[$value] ?? self::STATUS_DRAFT;
                }

                return (int) $value;
            }
        );
    }

    public function scopeActiveForFeed(Builder $query, ?Carbon $now = null): Builder
    {
        $now ??= now();

        return $query
            ->where('status', self::STATUS_ACTIVE)
            ->where(function (Builder $query) use ($now): void {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    public static function injectIntoFeed(Collection $items, int $interval = self::FEED_INSERT_INTERVAL, int $adsCount = 1, bool $startWithAds = false): Collection
    {
        $advertisements = self::query()
            ->activeForFeed()
            ->get()
            ->map(fn(Advertisement $advertisement): array => $advertisement->toFeedArray())
            ->values();

        if ($items->isEmpty() || $advertisements->isEmpty() || $adsCount < 1) {
            return $items;
        }

        $feed = collect();
        $advertisementIndex = 0;

        if ($startWithAds) {
            $feed->push([
                'type' => 'advertisements',
                'data' => collect(range(0, $adsCount - 1))
                    ->map(fn (int $offset): array => $advertisements[($advertisementIndex + $offset) % $advertisements->count()])
                    ->values()
                    ->all(),
            ]);

            $advertisementIndex = ($advertisementIndex + $adsCount) % $advertisements->count();
        }

        foreach ($items->values() as $index => $item) {
            $feed->push($item);

            if (($index + 1) % $interval !== 0) {
                continue;
            }

            $feed->push([
                'type' => 'advertisements',
                'data' => collect(range(0, $adsCount - 1))
                    ->map(fn(int $offset): array => $advertisements[($advertisementIndex + $offset) % $advertisements->count()])
                    ->values()
                    ->all(),
            ]);

            $advertisementIndex = ($advertisementIndex + $adsCount) % $advertisements->count();
        }

        return $feed;
    }

    public function toFeedArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->image,
            'url' => $this->url,
        ];
    }

    public function toDetailArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'url' => $this->url,
        ];
    }
}
