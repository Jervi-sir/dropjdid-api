<?php

namespace App\Http\Controllers\Api\Search;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeopleController extends Controller
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
            ->where('username', 'like', '%'.$query.'%')
            ->latest('id')
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $people->getCollection()->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->username,
                'username' => $user->username,
                'image' => $user->image,
            ])->values(),
            'next_page' => $people->hasMorePages() ? $people->currentPage() + 1 : null,
        ]);
    }
}
