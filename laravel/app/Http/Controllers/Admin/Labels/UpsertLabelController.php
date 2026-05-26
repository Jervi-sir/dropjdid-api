<?php

namespace App\Http\Controllers\Admin\Labels;

use App\Http\Controllers\Controller;
use App\Models\Label;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpsertLabelController extends Controller
{
    /**
     * Store a newly created label.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label_category_id' => 'required|exists:label_categories,id',
            'code' => 'required|string|unique:labels,code',
            'en' => 'required|string',
            'fr' => 'required|string',
            'ar' => 'required|string',
        ]);

        Label::create($validated);

        return back()->with('success', 'Label created successfully.');
    }

    /**
     * Update the specified label.
     */
    public function update(Request $request, Label $label): RedirectResponse
    {
        $validated = $request->validate([
            'label_category_id' => 'required|exists:label_categories,id',
            'code' => 'required|string|unique:labels,code,'.$label->id,
            'en' => 'required|string',
            'fr' => 'required|string',
            'ar' => 'required|string',
        ]);

        $label->update($validated);

        return back()->with('success', 'Label updated successfully.');
    }

    /**
     * Remove the specified label from storage.
     */
    public function destroy(Label $label): RedirectResponse
    {
        // Delete all associated keywords
        $label->keywords()->delete();
        $label->delete();

        return back()->with('success', 'Label and all its keywords deleted successfully.');
    }
}
