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

class HistoryController extends Controller
{
    public function index(Request $request): JsonResponse
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
            'data' => $history->getCollection()->map(fn (SearchHistory $item): array => [
                'id' => $item->id,
                'query' => $item->query,
                'type' => $item->type,
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
}
