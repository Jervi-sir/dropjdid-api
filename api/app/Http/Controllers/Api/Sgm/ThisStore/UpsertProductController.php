<?php

namespace App\Http\Controllers\Api\Sgm\ThisStore;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Gender;
use App\Models\Keyword;
use App\Models\Label;
use App\Models\LabelCategory;
use App\Models\Product;
use App\Models\Quality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpsertProductController extends Controller
{
    /**
     * Get all labels grouped by label_categories.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function labels(Request $request): JsonResponse
    {
        $categories = LabelCategory::query()
            ->select('id', 'code', 'en', 'fr', 'ar', 'icon')
            ->with(['labels' => function ($query) {
                $query->select('id', 'label_category_id', 'code', 'en', 'fr', 'ar', 'image_url');
            }])
            ->get();

        return response()->json([
            'data' => $categories,
        ], 200);
    }

    /**
     * Get keywords belonging to a specific label with search support.
     *
     * @param Request $request
     * @param int|null $labelId
     * @return JsonResponse
     */
    public function keywords(Request $request, ?int $labelId = null): JsonResponse
    {
        $targetLabelId = $labelId ?? $request->query('label_id') ?? $request->query('label');
        $search = $request->query('search') ?? $request->query('q');

        if (! $targetLabelId) {
            return response()->json([
                'message' => 'Label ID is required.',
            ], 400);
        }

        $query = Keyword::query()
            ->where('label_id', $targetLabelId);

        if (! empty($search)) {
            $query->where('code', 'LIKE', '%' . trim($search) . '%');
        }

        $keywords = $query->select('id', 'label_id', 'code')->get();

        return response()->json([
            'data' => $keywords,
        ], 200);
    }

    /**
     * Get available product types and sizes catalog options:
     * - Qualities
     * - Genders
     * - Categories and their sizes
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function typesAndSizes(Request $request): JsonResponse
    {
        $qualities = Quality::query()
            ->select('id', 'code', 'en', 'fr', 'ar')
            ->get();

        $genders = Gender::query()
            ->select('id', 'code', 'en', 'fr', 'ar')
            ->get();

        $categories = Category::query()
            ->select('id', 'code', 'en', 'fr', 'ar')
            ->with(['sizes' => function ($query) {
                $query->select('id', 'category_id', 'code', 'type', 'en', 'fr', 'ar');
            }])
            ->get();

        $data = [
            'qualities' => $qualities,
            'genders' => $genders,
            'categories' => $categories,
        ];

        return response()->json([
            'data' => $data,
        ], 200);
    }
    /**
     * Handle invokable request to get product info for upsert form matching ProductType.
     *
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function __invoke(Request $request, ?int $id = null): JsonResponse
    {
        return $this->show($request, $id);
    }

    /**
     * Get product details formatted matching the Upsert ProductType interface.
     *
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function show(Request $request, ?int $id = null): JsonResponse
    {
        $productId = $id ?? $request->query('id') ?? $request->query('product_id');

        if (! $productId) {
            return response()->json([
                'message' => 'Product ID is required.',
            ], 400);
        }

        $product = Product::query()
            ->where('id', $productId)
            ->with([
                'images',
                'quality',
                'gender',
                'category',
                'variants.size',
                'productKeywords.keyword',
                'productKeywords.label.category',
            ])
            ->first();

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        // 1. Admin Feedback & Rejection Reason
        $formattedRejectionReason = Product::formatRejectionReason($product->rejection_reason);

        // Derive top message for legacy adminFeedback banner if available
        $feedbackMessage = '';
        if (!empty($formattedRejectionReason)) {
            $firstInst = $formattedRejectionReason[0]['instruction'] ?? [];
            $feedbackMessage = (string) ($firstInst['en'] ?? $firstInst['fr'] ?? $firstInst['ar'] ?? '');
        }

        $adminFeedback = [
            'message' => $feedbackMessage,
            'type' => $product->product_status === 'rejected' ? 'rejection-reason' : 'tips',
            'instructions' => $formattedRejectionReason,
        ];

        // 2. Images array
        $images = $product->images->map(function ($img) {
            $url = $img->image_url;
            if ($url && ! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
                $url = url($url);
            }
            return (string) $url;
        })->filter()->values()->all();

        // 3. Prices
        $prices = [
            'price1' => $product->price_shown !== null ? (string) $product->price_shown : '',
            'price2' => $product->price_original !== null ? (string) $product->price_original : '',
            'price3' => $product->price_store !== null ? (string) $product->price_store : '',
        ];

        // 4. Selected Catalog (quality, genders, type_sizes)
        $quality = [];
        if ($product->quality && $product->quality->code) {
            $quality[] = [
                'code' => (string) $product->quality->code,
            ];
        }

        $genders = [];
        if ($product->gender && $product->gender->code) {
            $genders[] = [
                'code' => (string) $product->gender->code,
            ];
        }

        $typeSizes = [];
        $groupedVariants = $product->variants->groupBy(function ($variant) {
            return $variant->size?->type ?? 'universal';
        });

        foreach ($groupedVariants as $typeCode => $variantsGroup) {
            $sizesList = [];
            foreach ($variantsGroup as $variant) {
                if ($variant->size && $variant->size->code) {
                    $sizesList[] = [
                        'code' => (string) $variant->size->code,
                        'quantity' => (int) ($variant->quantity ?? 0),
                    ];
                }
            }

            if (! empty($sizesList)) {
                $typeSizes[] = [
                    'code' => (string) $typeCode,
                    'sizes' => $sizesList,
                ];
            }
        }

        $selectedCatalog = [
            'quality' => $quality,
            'genders' => $genders,
            'type_sizes' => $typeSizes,
        ];

        // 5. Labels (grouped by category and label)
        $labels = [];
        $productKeywords = $product->productKeywords ?? collect();

        $groupedByLabel = $productKeywords->groupBy('label_id');

        foreach ($groupedByLabel as $labelId => $pkItems) {
            $firstItem = $pkItems->first();
            $labelModel = $firstItem?->label;
            $categoryCode = $labelModel?->category?->code ?? 'general';
            $labelCode = $labelModel?->code ?? '';

            $keywordCodes = $pkItems->map(function ($pk) {
                return $pk->keyword?->code;
            })->filter()->values()->all();

            $labels[] = [
                'category_code' => (string) $categoryCode,
                'label_code' => (string) $labelCode,
                'labels_codes' => $keywordCodes,
                'labels_count' => count($keywordCodes),
            ];
        }

        $data = [
            'admin_feedback' => $adminFeedback,
            'rejection_reason' => $formattedRejectionReason,
            'product_status' => (string) ($product->product_status ?? 'draft'),
            'name' => (string) ($product->name ?? ''),
            'description' => (string) ($product->description ?? ''),
            'expires_at' => $product->expires_at ? $product->expires_at->toIso8601String() : null,
            'id' => (int) $product->id,
            'images' => $images,
            'prices' => $prices,
            'selected_catalog' => $selectedCatalog,
            'labels' => $labels,
        ];

        return response()->json([
            'data' => $data,
        ], 200);
    }

    /**
     * Create a new product.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        return $this->upsert($request, null);
    }

    /**
     * Update an existing product.
     *
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function update(Request $request, ?int $id = null): JsonResponse
    {
        $targetId = $id ?? $request->input('product_id') ?? $request->input('id');
        return $this->upsert($request, $targetId);
    }

    /**
     * Core upsert handler for both create (POST) and update (PATCH/PUT).
     *
     * @param Request $request
     * @param int|null $productId
     * @return JsonResponse
     */
    protected function upsert(Request $request, ?int $productId = null): JsonResponse
    {
        // Support payload both as JSON body and as Multipart Form Data (e.g. data field or direct fields)
        $input = $request->all();

        // If JSON payload string was sent in a 'data' field or 'payload' field of multipart
        if ($request->has('data') && is_string($request->input('data'))) {
            $decoded = json_decode($request->input('data'), true);
            if (is_array($decoded)) {
                $input = array_merge($input, $decoded);
            }
        } elseif ($request->has('payload') && is_string($request->input('payload'))) {
            $decoded = json_decode($request->input('payload'), true);
            if (is_array($decoded)) {
                $input = array_merge($input, $decoded);
            }
        }

        // Parse nested JSON strings if passed via standard multipart form fields
        foreach (['prices', 'selected_catalog', 'labels', 'images'] as $field) {
            if (isset($input[$field]) && is_string($input[$field])) {
                $decoded = json_decode($input[$field], true);
                if (is_array($decoded)) {
                    $input[$field] = $decoded;
                }
            }
        }

        $isDraft = filter_var($input['is_draft'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $storeId = $input['store_id'] ?? null;
        $prices = is_array($input['prices'] ?? null) ? $input['prices'] : [];
        $selectedCatalog = is_array($input['selected_catalog'] ?? null) ? $input['selected_catalog'] : [];
        $labelsInput = is_array($input['labels'] ?? null) ? $input['labels'] : [];
        $imagesInput = is_array($input['images'] ?? null) ? $input['images'] : [];

        return \Illuminate\Support\Facades\DB::transaction(function () use (
            $request,
            $input,
            $isDraft,
            $storeId,
            $prices,
            $selectedCatalog,
            $labelsInput,
            $imagesInput,
            $productId
        ) {
            // Find or create product
            $product = null;
            if ($productId) {
                $product = Product::find($productId);
            }

            if (! $product) {
                $product = new Product();
                $product->product_status = $isDraft ? Product::STATUS_DRAFT : Product::STATUS_PUBLISHED;
            } else {
                if ($isDraft && $product->product_status !== Product::STATUS_DRAFT) {
                    $product->product_status = Product::STATUS_DRAFT;
                } elseif (! $isDraft && $product->product_status === Product::STATUS_DRAFT) {
                    $product->product_status = Product::STATUS_PUBLISHED;
                }
            }

            if ($storeId) {
                $product->store_id = $storeId;
            }

            // 1. Basic Fields & Prices
            $product->name = $input['name'] ?? $product->name ?? '';
            $product->description = $input['description'] ?? $product->description ?? '';

            if (isset($prices['price1']) && $prices['price1'] !== '' && $prices['price1'] !== null) {
                $product->price_shown = is_numeric($prices['price1']) ? $prices['price1'] : null;
            }
            if (isset($prices['price2']) && $prices['price2'] !== '' && $prices['price2'] !== null) {
                $product->price_original = is_numeric($prices['price2']) ? $prices['price2'] : null;
            }
            if (isset($prices['price3']) && $prices['price3'] !== '' && $prices['price3'] !== null) {
                $product->price_store = is_numeric($prices['price3']) ? $prices['price3'] : null;
            }

            // 2. Resolve Quality & Gender
            $qualityCode = $selectedCatalog['quality'][0]['code'] ?? null;
            if ($qualityCode) {
                $qualityModel = Quality::where('code', $qualityCode)->first();
                $product->quality_id = $qualityModel?->id;
            }

            $genderCode = $selectedCatalog['genders'][0]['code'] ?? null;
            if ($genderCode) {
                $genderModel = Gender::where('code', $genderCode)->first();
                $product->gender_id = $genderModel?->id;
            }

            // 3. Resolve Category
            $typeSizes = $selectedCatalog['type_sizes'] ?? [];
            $firstTypeCode = $typeSizes[0]['code'] ?? null;
            if ($firstTypeCode) {
                $categoryModel = Category::where('code', $firstTypeCode)->first();
                if ($categoryModel) {
                    $product->category_id = $categoryModel->id;
                }
            }

            if (! empty($input['expires_at'])) {
                $product->expires_at = $input['expires_at'];
            }

            $product->save();

            // 4. Persist Product Images (handle both multipart uploaded files and existing URLs)
            $uploadedFiles = $request->file('image_files') ?? $request->file('images') ?? [];
            if (! is_array($uploadedFiles) && $request->hasFile('image_files')) {
                $uploadedFiles = [$request->file('image_files')];
            }

            if (! empty($imagesInput) || ! empty($uploadedFiles)) {
                $product->images()->delete();

                $totalImages = max(count($imagesInput), count($uploadedFiles));

                for ($idx = 0; $idx < $totalImages; $idx++) {
                    $meta = $imagesInput[$idx] ?? [];
                    $isMain = (bool) ($meta['isMain'] ?? ($idx === 0));
                    $rawUri = $meta['uri'] ?? null;

                    $storedUrl = null;

                    // Check if file was sent as named multipart file (e.g. image_0, image_1 or array image_files[0])
                    $file = $request->file("image_{$idx}")
                        ?? ($uploadedFiles[$idx] ?? null)
                        ?? ($request->file("images.{$idx}.file") ?? null);

                    if ($file && $file->isValid()) {
                        $path = $file->store('products', 'public');
                        $storedUrl = '/storage/' . $path;
                    } elseif ($rawUri) {
                        // If it's already a public HTTP(S) URL or relative /storage/ URL
                        if (str_starts_with($rawUri, 'http://') || str_starts_with($rawUri, 'https://') || str_starts_with($rawUri, '/storage/')) {
                            $storedUrl = $rawUri;
                        } elseif (file_exists($rawUri)) {
                            // If accessible on local server path
                            try {
                                $ext = pathinfo($rawUri, PATHINFO_EXTENSION) ?: 'jpg';
                                $filename = \Illuminate\Support\Str::random(40) . '.' . $ext;
                                $contents = file_get_contents($rawUri);
                                \Illuminate\Support\Facades\Storage::disk('public')->put('products/' . $filename, $contents);
                                $storedUrl = '/storage/products/' . $filename;
                            } catch (\Throwable $e) {
                                $storedUrl = null;
                            }
                        }
                    }

                    if ($storedUrl) {
                        \App\Models\ProductImage::create([
                            'product_id' => $product->id,
                            'image_url' => $storedUrl,
                            'sort_order' => $idx,
                            'is_main' => $isMain,
                        ]);
                    }
                }
            }

            // 5. Persist Product Variants & Sizes
            if (! empty($typeSizes)) {
                $product->variants()->delete();
                foreach ($typeSizes as $ts) {
                    $typeCode = $ts['code'] ?? null;
                    $sizesList = $ts['sizes'] ?? [];

                    foreach ($sizesList as $s) {
                        $sizeCode = $s['code'] ?? null;
                        $quantity = (int) ($s['quantity'] ?? 0);
                        if (! $sizeCode) continue;

                        $sizeQuery = \App\Models\Size::where('code', $sizeCode);
                        if ($typeCode) {
                            $sizeQuery->where(function ($q) use ($typeCode) {
                                $q->where('type', $typeCode)
                                    ->orWhereHas('category', fn($c) => $c->where('code', $typeCode));
                            });
                        }
                        $sizeModel = $sizeQuery->first() ?? \App\Models\Size::where('code', $sizeCode)->first();

                        if ($sizeModel) {
                            \App\Models\ProductVariant::create([
                                'product_id' => $product->id,
                                'size_id' => $sizeModel->id,
                                'quantity' => $quantity,
                            ]);
                        }
                    }
                }
            }

            // 6. Persist Product Keywords & Labels
            if (! empty($labelsInput)) {
                \App\Models\ProductKeyword::where('product_id', $product->id)->delete();

                foreach ($labelsInput as $lbl) {
                    $categoryCode = $lbl['category_code'] ?? null;
                    $labelCode = $lbl['label_code'] ?? null;
                    $keywordCodes = $lbl['labels_codes'] ?? [];

                    $labelModel = null;
                    if ($labelCode) {
                        $labelModel = Label::where('code', $labelCode)->first();
                    }

                    foreach ($keywordCodes as $kCode) {
                        $kwQuery = Keyword::where('code', $kCode);
                        if ($labelModel) {
                            $kwQuery->where('label_id', $labelModel->id);
                        }
                        $keywordModel = $kwQuery->first();

                        if ($keywordModel) {
                            $targetLabelId = $labelModel ? $labelModel->id : $keywordModel->label_id;

                            \App\Models\ProductKeyword::create([
                                'product_id' => $product->id,
                                'label_id' => $targetLabelId,
                                'keyword_id' => $keywordModel->id,
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'message' => $productId ? 'Product updated successfully.' : 'Product created successfully.',
                'product_id' => $product->id,
                'data' => [
                    'id' => $product->id,
                    'product_status' => $product->product_status,
                ],
            ], $productId ? 200 : 201);
        });
    }

    /**
     * Refresh product expiration date (e.g. +15 days).
     *
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function refresh(Request $request, ?int $id = null): JsonResponse
    {
        $productId = $id ?? $request->input('product_id') ?? $request->input('id');

        if (! $productId) {
            return response()->json([
                'message' => 'Product ID is required.',
            ], 400);
        }

        $product = Product::find($productId);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $now = now();
        $product->refreshed_at = $now;
        $product->expires_at = $now->copy()->addDays(15);
        $product->save();

        return response()->json([
            'message' => 'Product refreshed successfully.',
            'data' => [
                'id' => $product->id,
                'refreshed_at' => $product->refreshed_at->toIso8601String(),
                'expires_at' => $product->expires_at->toIso8601String(),
            ],
        ], 200);
    }

    /**
     * Soft delete product.
     *
     * @param Request $request
     * @param int|null $id
     * @return JsonResponse
     */
    public function destroy(Request $request, ?int $id = null): JsonResponse
    {
        $productId = $id ?? $request->input('product_id') ?? $request->input('id');

        if (! $productId) {
            return response()->json([
                'message' => 'Product ID is required.',
            ], 400);
        }

        $product = Product::find($productId);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
            'product_id' => $productId,
        ], 200);
    }
}
