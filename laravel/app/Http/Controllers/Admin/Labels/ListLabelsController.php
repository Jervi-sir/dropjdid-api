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
                'keywords_count' => $label->keywords_count,
                'created_at' => $label->created_at?->toIso8601String(),
            ];
        });

        return Inertia::render('admin/labels/list', [
            'labels' => $formattedLabels,
            'filters' => [
                'search' => $request->input('search', ''),
                'per_page' => (int) $perPage,
            ],
        ]);
    }
}
