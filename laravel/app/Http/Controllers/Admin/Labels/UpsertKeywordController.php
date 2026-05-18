<?php

namespace App\Http\Controllers\Admin\Labels;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use App\Models\Label;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpsertKeywordController extends Controller
{
    /**
     * Store a newly created keyword for a label.
     */
    public function store(Request $request, Label $label): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                Rule::unique('keywords')
                    ->where('label_id', $label->id),
            ],
        ]);

        $label->keywords()->create([
            'code' => $validated['code'],
        ]);

        return back()->with('success', 'Keyword created successfully.');
    }

    /**
     * Update the specified keyword.
     */
    public function update(Request $request, Label $label, Keyword $keyword): RedirectResponse
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                Rule::unique('keywords')
                    ->where('label_id', $label->id)
                    ->ignore($keyword->id),
            ],
        ]);

        $keyword->update([
            'code' => $validated['code'],
        ]);

        return back()->with('success', 'Keyword updated successfully.');
    }

    /**
     * Remove the specified keyword.
     */
    public function destroy(Label $label, Keyword $keyword): RedirectResponse
    {
        // Detach pivot associations first
        $keyword->products()->detach();
        $keyword->delete();

        return back()->with('success', 'Keyword deleted successfully.');
    }
}
