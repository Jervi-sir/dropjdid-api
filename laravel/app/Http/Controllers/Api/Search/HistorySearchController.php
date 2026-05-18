<?php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use App\Models\Label;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HistorySearchController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'query' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'in:' . implode(',', SearchHistory::TYPES)],
        ]);

        $query = trim($validated['query']);

        if ($query === '') {
            return response()->json([
                'message' => 'Search query saved successfully.',
            ]);
        }

        $type = match ($validated['type'] ?? 'general') {
            'product' => SearchHistory::TYPE_PRODUCT,
            'store' => SearchHistory::TYPE_STORE,
            'creator' => SearchHistory::TYPE_CREATOR,
            default => SearchHistory::TYPE_GENERAL,
        };

        $history = SearchHistory::query()
            ->where('user_id', $user->id)
            ->where('query', $query)
            ->first();

        if ($history !== null) {
            $history->type = $type;
            $history->save();
            $history->touch();
        } else {
            $history = SearchHistory::query()->create([
                'user_id' => $user->id,
                'query' => $query,
                'type' => $type,
            ]);
        }

        return response()->json([
            'data' => [
                'id' => $history->id,
                'query' => $history->query,
                'type' => SearchHistory::TYPES[$history->type] ?? $history->type,
            ],
            'message' => 'Search query saved successfully.',
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $history = SearchHistory::query()
            ->where('user_id', $user->id)
            ->latest()
            ->simplePaginate($perPage);

        return response()->json([
            'data' => collect($history->items())->map(fn (SearchHistory $item): array => [
                'id' => $item->id,
                'query' => $item->query,
                'type' => SearchHistory::TYPES[$item->type] ?? $item->type,
            ])->values(),
            'next_page' => $history->hasMorePages() ? $history->currentPage() + 1 : null,
        ]);
    }

    public function suggestions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:255'],
        ]);

        $query = trim($validated['query']);
        $normalizedQuery = mb_strtolower($query);

        if ($query === '') {
            return response()->json(['data' => []]);
        }

        $keywords = Keyword::query()
            ->with('label')
            ->whereRaw('LOWER(code) like ?', ['%'.$normalizedQuery.'%'])
            ->limit(5)
            ->get()
            ->map(fn (Keyword $keyword): array => [
                'type' => 'keyword',
                'id' => $keyword->id,
                'label' => $keyword->code,
                'subtitle' => $keyword->label?->en,
            ]);

        $labels = Label::query()
            ->where(function (Builder $builder) use ($normalizedQuery): void {
                $builder
                    ->whereRaw('LOWER(code) like ?', ['%'.$normalizedQuery.'%'])
                    ->orWhereRaw('LOWER(en) like ?', ['%'.$normalizedQuery.'%'])
                    ->orWhereRaw('LOWER(fr) like ?', ['%'.$normalizedQuery.'%'])
                    ->orWhereRaw('LOWER(ar) like ?', ['%'.$normalizedQuery.'%']);
            })
            ->limit(5)
            ->get()
            ->map(fn (Label $label): array => [
                'type' => 'label',
                'id' => $label->id,
                'label' => $label->en ?? $label->code,
                'subtitle' => $label->code,
            ]);

        $profiles = User::query()
            ->whereRaw('LOWER(username) like ?', ['%'.$normalizedQuery.'%'])
            ->limit(5)
            ->get()
            ->map(fn (User $user): array => [
                'type' => 'profile',
                'id' => $user->id,
                'label' => $user->username,
                'subtitle' => '@'.$user->username,
            ]);

        return response()->json([
            'data' => $keywords
                ->concat($labels)
                ->concat($profiles)
                ->values(),
        ]);
    }

    public function destroy(Request $request, SearchHistory $history): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);
        abort_if($history->user_id !== $user->id, 404);

        $history->delete();

        return response()->json([
            'message' => 'Search history entry deleted successfully.',
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_if($user === null, 401);

        SearchHistory::query()
            ->where('user_id', $user->id)
            ->delete();

        return response()->json([
            'message' => 'Search history cleared successfully.',
        ]);
    }
}
