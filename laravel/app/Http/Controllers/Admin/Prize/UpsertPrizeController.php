<?php

namespace App\Http\Controllers\Admin\Prize;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class UpsertPrizeController extends Controller
{
    /**
     * Show the create form.
     */
    public function create(): Response
    {
        return Inertia::render('admin/prizes/upsert', [
            'prize' => null,
            'statuses' => Prize::STATUS,
        ]);
    }

    /**
     * Store a newly created prize in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:10240', // Max 10MB
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'status' => 'required|integer|in:0,1,2,3',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('prizes', 'public');
            $validated['image'] = $path;
        }

        $validated['creator_id'] = auth()->id();

        Prize::create($validated);

        return redirect()->route('admin.prizes.index')->with('success', 'Prize created successfully.');
    }

    /**
     * Show the edit form.
     */
    public function edit(Prize $prize): Response
    {
        return Inertia::render('admin/prizes/upsert', [
            'prize' => [
                'id' => $prize->id,
                'title' => $prize->title,
                'description' => $prize->description,
                'image_url' => $prize->image ? asset('storage/' . $prize->image) : null,
                'starts_at' => $prize->starts_at?->format('Y-m-d\TH:i'),
                'ends_at' => $prize->ends_at?->format('Y-m-d\TH:i'),
                'status' => $prize->status,
            ],
            'statuses' => Prize::STATUS,
        ]);
    }

    /**
     * Update the prize in the database.
     */
    public function update(Request $request, Prize $prize): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:10240', // Max 10MB
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'status' => 'required|integer|in:0,1,2,3',
        ]);

        // If there's a new image upload, handle storing it and deleting the old one
        if ($request->hasFile('image')) {
            if ($prize->image) {
                Storage::disk('public')->delete($prize->image);
            }
            $path = $request->file('image')->store('prizes', 'public');
            $validated['image'] = $path;
        } else {
            // Keep existing image if not uploading a new one
            unset($validated['image']);
        }

        $prize->update($validated);

        return redirect()->route('admin.prizes.index')->with('success', 'Prize updated successfully.');
    }
}
