<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'url',
        'description',
        'status',
        'sort_order',
        'starts_at',
        'ends_at',
        'nb_liked',
        'nb_saved',
        'nb_shared',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'nb_liked' => 'integer',
            'nb_saved' => 'integer',
            'nb_shared' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Format advertisement into AdType schema.
     *
     * @param int|null $userId
     * @return array<string, mixed>
     */
    public function toAdType(?int $userId = null): array
    {
        $rawImages = $this->image;
        $imageUrls = [];

        if (is_array($rawImages)) {
            $imageUrls = $rawImages;
        } elseif (is_string($rawImages) && ! empty($rawImages)) {
            // Check if stored as JSON string
            $decoded = json_decode($rawImages, true);
            if (is_array($decoded)) {
                $imageUrls = $decoded;
            } else {
                $imageUrls = [$rawImages];
            }
        }

        // Convert any relative paths to full absolute URLs
        $imageUrls = array_values(array_map(function ($url) {
            if ($url && ! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
                return url($url);
            }

            return (string) $url;
        }, $imageUrls));

        $isLiked = false;
        $isSaved = false;

        if ($userId) {
            $isLiked = UserInteraction::where('user_id', $userId)
                ->where('type', UserInteraction::TYPE_LIKE)
                ->where('target_type', UserInteraction::TARGET_ADVERTISEMENT)
                ->where('target_id', $this->id)
                ->exists();

            $isSaved = UserInteraction::where('user_id', $userId)
                ->where('type', UserInteraction::TYPE_SAVE)
                ->where('target_type', UserInteraction::TARGET_ADVERTISEMENT)
                ->where('target_id', $this->id)
                ->exists();
        }

        return [
            'id' => (int) $this->id,
            'text1' => (string) ($this->title ?? ''),
            'text2' => (string) ($this->description ?? 'sponsored'),
            'image_url' => $imageUrls,
            'url' => (string) ($this->url ?? ''),
            'is_liked' => (bool) $isLiked,
            'is_saved' => (bool) $isSaved,
            'stats' => [
                'nb_liked' => (int) ($this->nb_liked ?? 0),
                'nb_saved' => (int) ($this->nb_saved ?? 0),
                'nb_shared' => (int) ($this->nb_shared ?? 0),
            ],
        ];
    }

    /**
     * Format an Advertisement model into explore/products feed AdType format.
     *
     * @return array<string, mixed>
     */
    public function toFeedAd(): array
    {
        $rawImages = $this->image;
        $imageUrl = '';

        if (is_array($rawImages)) {
            $imageUrl = $rawImages[0] ?? '';
        } elseif (is_string($rawImages) && ! empty($rawImages)) {
            $decoded = json_decode($rawImages, true);
            if (is_array($decoded)) {
                $imageUrl = $decoded[0] ?? '';
            } else {
                $imageUrl = $rawImages;
            }
        }

        if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
            $imageUrl = url($imageUrl);
        }

        return [
            'id' => (int) $this->id,
            'image_url' => (string) $imageUrl,
            'text1' => (string) ($this->title ?? 'Sponsored'),
            'text2' => (string) ($this->description ?? ''),
            'url' => (string) ($this->url ?? ''),
        ];
    }

    /**
     * Retrieve active advertisements ensuring at least $minAds items.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getActiveFeedAds(int $limit = 4, int $minAds = 4): array
    {
        $query = static::query()
            ->where(function ($q) {
                $q->where('status', 'active')
                    ->orWhereNull('status');
            })
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order', 'asc')
            ->latest('id')
            ->limit(max($limit, $minAds));

        $ads = $query->get();
        $formattedAds = $ads->map(fn (self $ad) => $ad->toFeedAd())->values()->all();

        // If fewer than minAds, pad by repeating existing ads or adding defaults
        if (count($formattedAds) > 0 && count($formattedAds) < $minAds) {
            $original = $formattedAds;
            $i = 0;
            while (count($formattedAds) < $minAds) {
                $copy = $original[$i % count($original)];
                $copy['id'] = $copy['id'] + (1000 * (int) (count($formattedAds) / count($original)));
                $formattedAds[] = $copy;
                $i++;
            }
        } elseif (empty($formattedAds)) {
            // Default placeholder ads if database has no ads
            for ($i = 1; $i <= $minAds; $i++) {
                $formattedAds[] = [
                    'id' => $i,
                    'image_url' => 'https://picsum.photos/seed/ad'.$i.'/800/450',
                    'text1' => 'Special Sponsor #'.$i,
                    'text2' => 'Discover the latest collection on DropJdid',
                    'url' => 'https://dropjdid.com',
                ];
            }
        }

        return array_slice($formattedAds, 0, max($limit, $minAds));
    }

    /**
     * Create an ads section schema with section_type => "ads".
     *
     * @param  int  $minAds  Minimum number of ads to include (defaults to 4)
     * @param  string  $label  Section display label
     * @param  string  $id  Section identifier
     * @return array<string, mixed>
     */
    public static function makeAdsSection(int $minAds = 4, string $label = 'Sponsored', string $id = 'ads-section'): array
    {
        return [
            'id' => $id,
            'label' => $label,
            'section_type' => 'ads',
            'ads' => static::getActiveFeedAds($minAds, $minAds),
        ];
    }

    /**
     * Inject ads sections (with section_type => "ads") into an array of sections every N product sections.
     *
     * @param  array  $sections  Target array of feed sections or items
     * @param  int  $every  Inject ads after every N product sections (default: 2)
     * @param  int  $minAds  Minimum number of ads to inject per section (default: 4)
     * @param  string  $label  Label for the ads section
     */
    public static function injectAds(array $sections, int $every = 2, int $minAds = 4, string $label = 'Sponsored'): array
    {
        if (empty($sections)) {
            return [static::makeAdsSection($minAds, $label, 'ads-section-1')];
        }

        $result = [];
        $productSectionCount = 0;
        $adSectionIndex = 1;

        foreach ($sections as $section) {
            $result[] = $section;

            // Check if this is a product section
            $isProductSection = is_array($section) && (($section['section_type'] ?? 'products') === 'products');
            if ($isProductSection) {
                $productSectionCount++;

                if ($every > 0 && ($productSectionCount % $every === 0)) {
                    $result[] = static::makeAdsSection(
                        $minAds,
                        $label,
                        'ads-section-'.$adSectionIndex++
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Inject ads sections into a JSON response payload or JsonResponse object every N product sections.
     *
     * @param  mixed  $response  JsonResponse or array
     * @param  int  $every  Inject ads after every N product sections (default: 2)
     * @param  int  $minAds  Minimum number of ads (default: 4)
     * @param  string  $label  Section title
     */
    public static function injectIntoResponse(mixed $response, int $every = 2, int $minAds = 4, string $label = 'Sponsored'): mixed
    {
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            if (isset($data['data']) && is_array($data['data'])) {
                $data['data'] = static::injectAds($data['data'], $every, $minAds, $label);
                $response->setData($data);
            }

            return $response;
        }

        if (is_array($response)) {
            if (isset($response['data']) && is_array($response['data'])) {
                $response['data'] = static::injectAds($response['data'], $every, $minAds, $label);

                return $response;
            }

            return static::injectAds($response, $every, $minAds, $label);
        }

        return $response;
    }
}
