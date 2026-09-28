<?php

namespace App\Http\Controllers\Api\Creator;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\DropHistory;
use App\Models\DropImage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UpsertDropController extends Controller
{
    /**
     * Check if a drop title / username is available.
     */
    public function checkTitleAvailability(Request $request): JsonResponse
    {
        $rawTitle = (string) ($request->input('title') ?? $request->input('drop_name') ?? '');
        $sanitizedTitle = strtolower(preg_replace('/_+/', '_', preg_replace('/\s+/', '_', trim($rawTitle))));

        $validator = Validator::make(['title' => $sanitizedTitle], [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:40',
                'regex:/^[a-z0-9_.-]+$/',
            ],
        ], [
            'title.required' => 'Drop name is required.',
            'title.min' => 'Drop name must be at least 3 characters.',
            'title.max' => 'Drop name cannot exceed 40 characters.',
            'title.regex' => 'Drop name can only contain lowercase letters, numbers, dots, dashes, and underscores (no spaces).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'available' => false,
                'message' => $validator->errors()->first('title'),
                'errors' => $validator->errors(),
                'suggestions' => [],
            ], 422);
        }

        $dropId = $request->input('drop_id');

        $query = Drop::whereRaw('LOWER(TRIM(title)) = ?', [$sanitizedTitle]);

        // Exclude the current drop if updating
        if ($dropId) {
            $query->where('id', '!=', $dropId);
        }

        $isTaken = $query->exists();

        if ($isTaken) {
            $faker = class_exists(\Faker\Factory::class) ? \Faker\Factory::create() : null;

            $words = $faker ? [
                strtolower($faker->word()),
                strtolower($faker->colorName()),
                $faker->numberBetween(10, 99),
            ] : [rand(1, 99), 'drop', 'official'];

            $candidates = [
                $sanitizedTitle . '_' . rand(10, 99),
                $sanitizedTitle . '.' . ($words[0] ?? 'drop'),
                $sanitizedTitle . '_' . ($words[1] ?? 'official'),
                'the_' . $sanitizedTitle,
                $sanitizedTitle . rand(1, 99),
                ($words[0] ?? 'real') . '_' . $sanitizedTitle,
                $sanitizedTitle . '_drop',
                $sanitizedTitle . rand(100, 999),
            ];

            $suggestions = [];
            foreach ($candidates as $rawCandidate) {
                $candidate = strtolower(preg_replace('/_+/', '_', preg_replace('/\s+/', '_', trim((string) $rawCandidate))));
                $exists = Drop::whereRaw('LOWER(TRIM(title)) = ?', [$candidate])->exists();
                if (! $exists && ! in_array($candidate, $suggestions, true)) {
                    $suggestions[] = $candidate;
                }
                if (count($suggestions) >= 4) {
                    break;
                }
            }

            return response()->json([
                'available' => false,
                'message' => 'Drop name is already taken.',
                'suggestions' => $suggestions,
            ], 200);
        }

        return response()->json([
            'available' => true,
            'message' => 'Drop name is available.',
            'suggestions' => [],
        ], 200);
    }

    /**
     * Get single Drop details for editing or viewing.
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $drop = Drop::with([
            'creator',
            'images',
            'products' => function ($q) {
                $q->with(['mainImage', 'images', 'savedUsers'])->withCount('savedUsers');
            },
        ])->find($id);

        if (! $drop) {
            return response()->json([
                'message' => 'Drop not found.',
            ], 404);
        }

        $images = $drop->images->map(function (DropImage $img) {
            $url = $img->image;
            if ($url && ! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
                $url = url($url);
            }

            return [
                'id' => (int) $img->id,
                'url' => (string) $url,
                'is_main' => (bool) $img->is_main,
                'sort_order' => (int) $img->sort_order,
            ];
        })->values()->all();

        $products = $drop->products->map(function (Product $p) {
            $imageUrl = $p->mainImage?->image_url
                ?? $p->images->first()?->image_url
                ?? '';

            if ($imageUrl && ! str_starts_with($imageUrl, 'http://') && ! str_starts_with($imageUrl, 'https://')) {
                $imageUrl = url($imageUrl);
            }

            $currentPrice = $p->price_shown ?? $p->price_original ?? 0;
            $originalPrice = $p->price_original ?? $currentPrice;

            return [
                'id' => (int) $p->id,
                'image_url' => (string) $imageUrl,
                'name' => (string) ($p->name ?? 'Product #'.$p->id),
                'prices' => [
                    'price1' => number_format($currentPrice, 0, '.', ' ').' DZD',
                    'price2' => ($originalPrice > $currentPrice) ? number_format($originalPrice, 0, '.', ' ').' DZD' : '',
                ],
            ];
        })->values()->all();

        $rejectionReasonData = $drop->rejection_reason;
        $rejectionReasonText = is_array($rejectionReasonData)
            ? ($rejectionReasonData['reason'] ?? '')
            : (is_string($rejectionReasonData) ? $rejectionReasonData : '');

        return response()->json([
            'data' => [
                'id' => (int) $drop->id,
                'drop_name' => (string) ($drop->title ?? ''),
                'title' => (string) ($drop->title ?? ''),
                'description' => (string) ($drop->description ?? ''),
                'drop_status' => (string) ($drop->drop_status ?? 'published'),
                'is_draft' => ($drop->drop_status === 'draft'),
                'rejection_reason' => $rejectionReasonData,
                'rejection_message' => $rejectionReasonText,
                'images' => $images,
                'product_ids' => $drop->products->pluck('id')->values()->all(),
                'products' => $products,
            ],
        ], 200);
    }

    /**
     * Create or update (upsert) a Creator Drop.
     */
    public function upsert(Request $request): JsonResponse
    {
        // 1. Identify Creator
        $user = $request->user('sanctum') ?? $request->user();
        if (! $user) {
            $user = User::whereHas('roles', fn ($q) => $q->where('roles.code', 'creator'))->first() ?? User::first();
        }

        $dropId = $request->input('drop_id') ?? $request->input('id');

        // 2. Validation
        $rules = [
            'drop_name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_draft' => ['nullable'],
            'product_ids' => ['nullable'],
            'images' => ['nullable'],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $rawTitle = (string) ($request->input('drop_name') ?? $request->input('title') ?? '');
        $title = strtolower(preg_replace('/_+/', '_', preg_replace('/\s+/', '_', trim($rawTitle))));
        if ($title === '') {
            $title = 'drop_'.($dropId ? $dropId : (Drop::max('id') + 1));
        }

        $description = trim((string) ($request->input('description') ?? ''));
        $isDraft = filter_var($request->input('is_draft', false), FILTER_VALIDATE_BOOLEAN);

        // Product IDs
        $productIds = $request->input('product_ids', []);
        if (is_string($productIds)) {
            $decoded = json_decode($productIds, true);
            $productIds = is_array($decoded) ? $decoded : explode(',', $productIds);
        }
        $productIds = array_values(array_filter(array_map('intval', (array) $productIds)));

        // Images metadata/payload
        $imagesInput = $request->input('images', []);
        if (is_string($imagesInput)) {
            $decoded = json_decode($imagesInput, true);
            if (is_array($decoded)) {
                $imagesInput = $decoded;
            }
        }

        return DB::transaction(function () use ($user, $dropId, $title, $description, $isDraft, $productIds, $imagesInput, $request) {
            // Find or create Drop
            $drop = null;
            if ($dropId) {
                $drop = Drop::find($dropId);
            }

            $isNewDrop = ! $drop;
            $oldStatus = $drop ? $drop->drop_status : null;

            if (! $drop) {
                $drop = new Drop;
                $drop->creator_id = $user->id;
            }

            $drop->title = $title;
            $drop->description = $description;

            if ($isDraft) {
                $drop->drop_status = Drop::STATUS_DRAFT;
            } else {
                if ($isNewDrop) {
                    $drop->drop_status = Drop::STATUS_NEW;
                } elseif ($oldStatus === Drop::STATUS_DRAFT) {
                    // Moving from draft to submitted
                    $drop->drop_status = Drop::STATUS_NEW;
                } else {
                    // Existing drop updated: move to under-review for admin check
                    $drop->drop_status = Drop::STATUS_UNDER_REVIEW;
                }
                // Clear rejection reason once resubmitted/updated by creator
                $drop->rejection_reason = null;
            }

            $drop->save();

            // Record history
            DropHistory::record(
                drop: $drop,
                action: $isNewDrop ? 'created' : ($isDraft ? 'saved_draft' : 'updated_submitted'),
                userId: $user->id,
                fromStatus: $oldStatus,
                toStatus: $drop->drop_status,
                note: $isNewDrop
                    ? 'Drop created by creator'
                    : ($isDraft ? 'Drop saved as draft' : 'Drop updated and resubmitted for review'),
                metadata: [
                    'is_draft' => $isDraft,
                    'title' => $title,
                    'product_ids_count' => count($productIds),
                ]
            );

            // 3. Process Images
            // Ensure storage directory exists
            $storageDir = public_path('storage/drops');
            if (! File::isDirectory($storageDir)) {
                File::makeDirectory($storageDir, 0755, true, true);
            }

            $savedImageUrls = [];

            // A. Check for multipart file uploads (image_0, image_1, or images[] files)
            $uploadedFiles = $request->file('images') ?? $request->file('image_files') ?? [];
            if (! is_array($uploadedFiles) && $request->hasFile('images')) {
                $uploadedFiles = [$request->file('images')];
            }

            // Loop through images input and files
            $totalSlots = max(count($imagesInput), count($uploadedFiles));

            for ($i = 0; $i < $totalSlots; $i++) {
                $item = $imagesInput[$i] ?? null;
                $file = $request->file("image_{$i}")
                    ?? ($uploadedFiles[$i] ?? null)
                    ?? (isset($item['file']) && $request->hasFile("images.{$i}.file") ? $request->file("images.{$i}.file") : null);

                $storedPath = null;

                if ($file && $file->isValid()) {
                    $filename = 'drop_'.$drop->id.'_'.Str::random(10).'.'.$file->getClientOriginalExtension();
                    $file->move($storageDir, $filename);
                    $storedPath = '/storage/drops/'.$filename;
                } elseif (is_string($item)) {
                    $storedPath = $this->handleImageString($item, $drop->id, $storageDir);
                } elseif (is_array($item) && ! empty($item['url'])) {
                    $storedPath = $this->handleImageString($item['url'], $drop->id, $storageDir);
                } elseif (is_array($item) && ! empty($item['uri'])) {
                    $storedPath = $this->handleImageString($item['uri'], $drop->id, $storageDir);
                }

                if ($storedPath) {
                    $savedImageUrls[] = [
                        'url' => $storedPath,
                        'is_main' => ($i === 0),
                        'sort_order' => $i,
                    ];
                }
            }

            // If we have saved images, update DropImage records
            if (! empty($savedImageUrls)) {
                $drop->images()->delete();
                foreach ($savedImageUrls as $index => $imgData) {
                    DropImage::create([
                        'drop_id' => $drop->id,
                        'image' => $imgData['url'],
                        'is_main' => ($index === 0),
                        'sort_order' => $index,
                    ]);
                }
            }

            // 4. Sync Drop Products
            $drop->products()->sync($productIds);

            return response()->json([
                'success' => true,
                'message' => $isDraft ? 'Drop saved as draft successfully.' : 'Drop published successfully.',
                'drop_id' => (int) $drop->id,
                'data' => [
                    'id' => (int) $drop->id,
                    'title' => (string) $drop->title,
                    'description' => (string) $drop->description,
                    'drop_status' => (string) $drop->drop_status,
                    'is_draft' => ($drop->drop_status === 'draft'),
                    'product_ids' => $productIds,
                ],
            ], 200);
        });
    }

    /**
     * Delete a creator drop.
     */
    public function destroy(Request $request, int|string $id): JsonResponse
    {
        $drop = Drop::with('images')->find($id);

        if (! $drop) {
            return response()->json([
                'message' => 'Drop not found.',
            ], 404);
        }

        // Delete drop images from storage if stored locally
        foreach ($drop->images as $img) {
            if ($img->image && str_starts_with($img->image, '/storage/drops/')) {
                $localPath = public_path(ltrim($img->image, '/'));
                if (File::exists($localPath)) {
                    File::delete($localPath);
                }
            }
        }

        $drop->images()->delete();
        $drop->products()->detach();
        $drop->likedUsers()->detach();
        $drop->savedUsers()->detach();
        $drop->delete();

        return response()->json([
            'success' => true,
            'message' => 'Drop deleted successfully.',
        ], 200);
    }

    /**
     * Helper to process image string (URL, local file, or base64).
     */
    private function handleImageString(string $input, int $dropId, string $storageDir): ?string
    {
        $trimmed = trim($input);

        // If it's a data URL / base64
        if (str_starts_with($trimmed, 'data:image/')) {
            try {
                $parts = explode(',', $trimmed);
                $meta = $parts[0];
                $data = $parts[1] ?? '';

                $extension = 'jpg';
                if (str_contains($meta, 'image/png')) {
                    $extension = 'png';
                } elseif (str_contains($meta, 'image/webp')) {
                    $extension = 'webp';
                }

                $filename = 'drop_'.$dropId.'_'.Str::random(10).'.'.$extension;
                File::put($storageDir.'/'.$filename, base64_decode($data));

                return '/storage/drops/'.$filename;
            } catch (\Throwable $e) {
                return null;
            }
        }

        // If it's already a relative /storage/ URL
        if (str_starts_with($trimmed, '/storage/')) {
            return $trimmed;
        }

        // If it's a full URL containing current app domain
        $appUrl = url('/');
        if (str_starts_with($trimmed, $appUrl)) {
            return str_replace($appUrl, '', $trimmed);
        }

        // If external HTTP/HTTPS URL
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            return $trimmed;
        }

        // If file exists on filesystem (e.g. temporary upload path)
        if (File::exists($trimmed)) {
            try {
                $filename = 'drop_'.$dropId.'_'.Str::random(10).'.'.(pathinfo($trimmed, PATHINFO_EXTENSION) ?: 'jpg');
                File::copy($trimmed, $storageDir.'/'.$filename);

                return '/storage/drops/'.$filename;
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }
}
