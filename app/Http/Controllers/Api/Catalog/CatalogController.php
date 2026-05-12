<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Gender;
use App\Models\Keyword;
use App\Models\Label;
use App\Models\NotificationType;
use App\Models\PaymentMethod;
use App\Models\Quality;
use App\Models\Role;
use App\Models\Size;
use App\Models\SocialPlatform;
use App\Models\Wilaya;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CatalogController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $catalogs = [
            'categories' => Category::class,
            'genders' => Gender::class,
            'notification_types' => NotificationType::class,
            'payment_methods' => PaymentMethod::class,
            'qualities' => Quality::class,
            'roles' => Role::class,
            'sizes' => Size::class,
            'social_platforms' => SocialPlatform::class,
            'wilayas' => Wilaya::class,
            'labels' => Label::class,
            'keywords' => Keyword::class,
        ];

        $requestedTypes = $request->query('types');

        if ($requestedTypes) {
            $types = explode(',', $requestedTypes);

            // Filter the catalogs based on requested types (plural or singular)
            $catalogs = array_filter($catalogs, function ($model, $key) use ($types) {
                return in_array($key, $types) || in_array(Str::singular($key), $types);
            }, ARRAY_FILTER_USE_BOTH);
        }

        $response = [];

        foreach ($catalogs as $key => $model) {
            $query = $model::query();

            // Handle specific filters for sizes
            if ($key === 'sizes' && $request->filled('category_id')) {
                $query->where('category_id', $request->query('category_id'));
            }

            // Handle nested relations for categories
            if ($key === 'categories' && $request->boolean('with_sizes')) {
                $query->with('sizes');
            }

            if ($key === 'labels' && $request->boolean('with_keywords')) {
                $query->with('keywords');
            }

            if ($key === 'keywords') {
                if ($request->filled('label_id')) {
                    $query->where('label_id', $request->query('label_id'));
                }
                if ($request->filled('search')) {
                    $query->where('code', 'like', '%'.$request->query('search').'%');
                }

                $perPage = $request->integer('per_page', 50);
                $paginated = $query->paginate($perPage);

                $response[$key] = [
                    'data' => collect($paginated->items())->map(fn ($item) => method_exists($item, 'format') ? $item->format() : $item->toArray()),
                    'next_page' => $paginated->currentPage() < $paginated->lastPage() ? $paginated->currentPage() + 1 : null,
                ];

                continue;
            }

            $items = $query->get();

            $response[$key] = $items->map(function ($item) {
                return method_exists($item, 'format') ? $item->format() : $item->toArray();
            });
        }

        return response()->json($response);
    }
}
