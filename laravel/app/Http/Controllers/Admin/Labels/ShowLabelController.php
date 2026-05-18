<?php

namespace App\Http\Controllers\Admin\Labels;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowLabelController extends Controller
{
    /**
     * Display the label details and keywords listing page.
     */
    public function __invoke(Request $request, Label $label): Response
    {
        $label->load([
            'keywords' => function ($q) {
                $q->latest()->withCount('products');
            },
        ]);

        $formattedLabel = [
            'id' => $label->id,
            'code' => $label->code,
            'en' => $label->en,
            'fr' => $label->fr,
            'ar' => $label->ar,
        ];

        $formattedKeywords = $label->keywords->map(fn ($k) => [
            'id' => $k->id,
            'code' => $k->code,
            'products_count' => $k->products_count,
            'created_at' => $k->created_at?->toIso8601String(),
        ])->toArray();

        return Inertia::render('admin/labels/keywords/list', [
            'label' => $formattedLabel,
            'keywords' => $formattedKeywords,
        ]);
    }
}
