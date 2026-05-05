<?php

namespace App\Models;

use App\Models\Concerns\FormatsModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Advertisement extends Model
{
    use FormatsModel, HasFactory;

    private const FEED_INSERT_INTERVAL = 10;

    protected $fillable = [
        'title',
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

    public function scopeActiveForFeed(Builder $query, ?Carbon $now = null): Builder
    {
        $now ??= now();

        return $query
            ->where('status', 'active')
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

    public static function injectIntoFeed(Collection $items, int $interval = self::FEED_INSERT_INTERVAL): Collection
    {
        $advertisements = self::query()
            ->activeForFeed()
            ->get()
            ->map(fn (Advertisement $advertisement): array => $advertisement->toFeedArray())
            ->values();

        if ($items->isEmpty() || $advertisements->isEmpty()) {
            return $items;
        }

        $feed = collect();
        $advertisementIndex = 0;

        foreach ($items->values() as $index => $item) {
            $feed->push($item);

            if (($index + 1) % $interval !== 0) {
                continue;
            }

            $feed->push($advertisements[$advertisementIndex % $advertisements->count()]);
            $advertisementIndex++;
        }

        return $feed;
    }

    public function toFeedArray(): array
    {
        return [
            'type' => 'advertisement',
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->image,
            'url' => $this->url,
        ];
    }
}
