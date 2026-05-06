<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        $query = trim($validated['query']);

        $users = User::query()
            ->where('username', 'like', '%'.$query.'%')
            ->simplePaginate($perPage);

        return response()->json([
            'data' => $users->getCollection()->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->username,
                'username' => $user->username,
                'image' => $user->image,
            ])->values(),
            'next_page' => $users->hasMorePages() ? $users->currentPage() + 1 : null,
        ]);
    }
}
