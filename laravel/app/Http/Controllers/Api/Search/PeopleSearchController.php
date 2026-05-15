<?php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeopleSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = trim($validated['query']);

        if ($query === '') {
            return response()->json([
                'data' => [],
                'next_page' => null,
            ]);
        }

        $perPage = $validated['per_page'] ?? 10;

        $people = User::query()
            ->where('username', 'ilike', '%'.$query.'%')
            ->latest('id')
            ->simplePaginate($perPage);

        $isSuggestion = false;

        if ($people->isEmpty() && $request->query('page', 1) == 1) {
            $people = User::query()
                ->whereHas('savedProducts.product', function ($q) use ($query) {
                    $q->where('status', 'published')
                        ->where(function ($sq) use ($query) {
                            $sq->where('name', 'ilike', "%$query%")
                                ->orWhereHas('productKeywords.label', function ($l) use ($query) {
                                    $l->where('en', 'ilike', "%$query%")
                                        ->orWhere('fr', 'ilike', "%$query%")
                                        ->orWhere('ar', 'ilike', "%$query%");
                                });
                        });
                })
                ->latest('id')
                ->simplePaginate($perPage);

            if ($people->isNotEmpty()) {
                $isSuggestion = true;
            }
        }

        return response()->json([
            'data' => collect($people->items())->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->username,
                'username' => $user->username,
                'image' => $user->image,
            ])->values(),
            'is_suggestion' => $isSuggestion,
            'next_page' => $people->hasMorePages() ? $people->currentPage() + 1 : null,
        ]);
    }
}
