<?php

namespace App\Http\Controllers\Api\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Get list of events (active by default, with filter support).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');
        $filter = $request->query('filter', 'active'); // 'active', 'upcoming', 'past', 'all'
        $perPage = min((int) $request->query('per_page', 20), 50);

        $query = Event::query()->with('user');

        if ($filter === 'upcoming') {
            $query->upcoming();
        } elseif ($filter === 'past') {
            $query->past();
        } elseif ($filter === 'all') {
            // all events (for admin or privileged query)
        } else {
            // Default: strictly active events within their period
            $query->active();
        }

        $events = $query->orderBy('sort_order', 'asc')
            ->latest('id')
            ->paginate($perPage);

        $formattedData = collect($events->items())->map(function (Event $event) use ($userId) {
            return $event->toEventType($userId);
        });

        return response()->json([
            'current_page' => $events->currentPage(),
            'data' => $formattedData,
            'total' => $events->total(),
            'per_page' => $events->perPage(),
            'last_page' => $events->lastPage(),
            'next_page' => $events->hasMorePages() ? $events->currentPage() + 1 : null,
        ], 200);
    }

    /**
     * Get a single event by ID.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function show(Request $request, int|string $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

        $query = Event::with('user');

        // Only return active events on public API unless explicit preview requested
        if (! $request->boolean('preview')) {
            $query->active();
        }

        $event = $query->find($id);

        if (! $event) {
            return response()->json([
                'message' => 'Event not found or is currently inactive.',
            ], 404);
        }

        $data = $event->toEventType($userId);

        return response()->json([
            'data' => $data,
            ...$data,
        ], 200);
    }

    /**
     * Create a new event.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->input('user_id');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:1000',
            'url' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:draft,active,inactive,completed',
            'sort_order' => 'nullable|integer',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'meta' => 'nullable|array',
        ]);

        $event = Event::create([
            'user_id' => $userId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'url' => $validated['url'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'sort_order' => $validated['sort_order'] ?? 0,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'meta' => $validated['meta'] ?? null,
        ]);

        return response()->json([
            'message' => 'Event created successfully.',
            'data' => $event->toEventType($userId),
        ], 201);
    }
}
