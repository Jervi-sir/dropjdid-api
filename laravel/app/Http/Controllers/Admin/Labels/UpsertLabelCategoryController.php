<?php

namespace App\Http\Controllers\Admin\Labels;

use App\Http\Controllers\Controller;
use App\Models\LabelCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpsertLabelCategoryController extends Controller
{
    /**
     * Store a newly created label category.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:label_categories,code',
            'en' => 'required|string',
            'fr' => 'required|string',
            'ar' => 'required|string',
        ]);

        LabelCategory::create($validated);

        return back()->with('success', 'Label Category created successfully.');
    }

    /**
     * Update the specified label category.
     */
    public function update(Request $request, LabelCategory $labelCategory): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:label_categories,code,'.$labelCategory->id,
            'en' => 'required|string',
            'fr' => 'required|string',
            'ar' => 'required|string',
        ]);

        $labelCategory->update($validated);

        return back()->with('success', 'Label Category updated successfully.');
    }

    /**
     * Remove the specified label category from storage.
     */
    public function destroy(LabelCategory $labelCategory): RedirectResponse
    {
        $labelCategory->delete();

        return back()->with('success', 'Label Category deleted successfully.');
    }
}
