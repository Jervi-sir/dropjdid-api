<?php

namespace App\Http\Controllers\Admin\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventListController extends Controller
{
    /**
     * Display a listing of events with search, filters, and statistics.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');
        $search = $request->query('search', '');
        $perPage = min((int) $request->query('per_page', 15), 50);

        $query = Event::query()->with('user');

        // Apply search filter
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('meta->city', 'like', "%{$search}%")
                    ->orWhere('meta->location', 'like', "%{$search}%")
                    ->orWhere('meta->organizer', 'like', "%{$search}%");
            });
        }

        // Apply status / period filter
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'upcoming') {
            $query->upcoming();
        } elseif ($status === 'past') {
            $query->past();
        } elseif ($status === 'draft') {
            $query->where('status', 'draft');
        } elseif ($status === 'inactive') {
            $query->where('status', 'inactive');
        }

        $events = $query->orderBy('sort_order', 'asc')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        // Calculate counts
        $counts = [
            'all' => Event::count(),
            'active' => Event::active()->count(),
            'upcoming' => Event::upcoming()->count(),
            'past' => Event::past()->count(),
            'draft' => Event::where('status', 'draft')->count(),
        ];

        return Inertia::render('admin/events/list.page', [
            'events' => $events,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'counts' => $counts,
        ]);
    }

    /**
     * Store a newly created event.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:1000',
            'url' => 'nullable|string|max:1000',
            'status' => 'required|string|in:draft,active,inactive,completed',
            'sort_order' => 'nullable|integer',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'meta' => 'nullable|array',
            'meta.location' => 'nullable|string|max:255',
            'meta.city' => 'nullable|string|max:100',
            'meta.badge' => 'nullable|string|max:50',
            'meta.cta_text' => 'nullable|string|max:50',
            'meta.organizer' => 'nullable|string|max:100',
            'meta.capacity' => 'nullable|integer',
            'meta.highlights' => 'nullable|array',
        ]);

        $userId = $request->user()?->id;

        Event::create([
            'user_id' => $userId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'url' => $validated['url'] ?? null,
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'meta' => $validated['meta'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Event created successfully.');
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:1000',
            'url' => 'nullable|string|max:1000',
            'status' => 'required|string|in:draft,active,inactive,completed',
            'sort_order' => 'nullable|integer',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'meta' => 'nullable|array',
            'meta.location' => 'nullable|string|max:255',
            'meta.city' => 'nullable|string|max:100',
            'meta.badge' => 'nullable|string|max:50',
            'meta.cta_text' => 'nullable|string|max:50',
            'meta.organizer' => 'nullable|string|max:100',
            'meta.capacity' => 'nullable|integer',
            'meta.highlights' => 'nullable|array',
        ]);

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'url' => $validated['url'] ?? null,
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'] ?? $event->sort_order,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'meta' => $validated['meta'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Event updated successfully.');
    }

    /**
     * Delete the specified event.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->back()->with('success', 'Event deleted successfully.');
    }

    /**
     * Toggle the active status of an event.
     */
    public function toggleStatus(Event $event): RedirectResponse
    {
        $newStatus = $event->status === 'active' ? 'inactive' : 'active';
        $event->update(['status' => $newStatus]);

        return redirect()->back()->with('success', "Event status changed to {$newStatus}.");
    }
}
