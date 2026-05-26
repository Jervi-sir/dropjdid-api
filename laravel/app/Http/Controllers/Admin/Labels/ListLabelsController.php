<?php

namespace App\Http\Controllers\Admin\Labels;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListLabelsController extends Controller
{
    /**
     * Display a listing of the labels.
     */
    public function __invoke(Request $request): Response
    {
        $query = Label::query()
            ->with(['labelCategory'])
            ->withCount('keywords');

        // Apply keyword searching
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%'.$search.'%')
                    ->orWhere('en', 'like', '%'.$search.'%')
                    ->orWhere('fr', 'like', '%'.$search.'%')
                    ->orWhere('ar', 'like', '%'.$search.'%');
            });
        }

        // Apply category filtering
        if ($request->has('label_category_id') && $request->filled('label_category_id')) {
            $query->where('label_category_id', $request->input('label_category_id'));
        }

        $perPage = $request->input('per_page', 15);
        $labels = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Format labeled response
        $formattedLabels = $labels->through(function (Label $label) {
            return [
                'id' => $label->id,
                'code' => $label->code,
                'en' => $label->en,
                'fr' => $label->fr,
                'ar' => $label->ar,
                'label_category_id' => $label->label_category_id,
                'label_category' => $label->labelCategory ? [
                    'id' => $label->labelCategory->id,
                    'code' => $label->labelCategory->code,
                    'en' => $label->labelCategory->en,
                    'fr' => $label->labelCategory->fr,
                    'ar' => $label->labelCategory->ar,
                ] : null,
                'keywords_count' => $label->keywords_count,
                'created_at' => $label->created_at?->toIso8601String(),
            ];
        });

        $categories = \App\Models\LabelCategory::all()->map(function ($category) {
            return [
                'id' => $category->id,
                'code' => $category->code,
                'en' => $category->en,
                'fr' => $category->fr,
                'ar' => $category->ar,
            ];
        });

        return Inertia::render('admin/labels/list', [
            'labels' => $formattedLabels,
            'labelCategories' => $categories,
            'filters' => [
                'search' => $request->input('search', ''),
                'label_category_id' => $request->input('label_category_id', ''),
                'per_page' => (int) $perPage,
            ],
        ]);
    }
}
