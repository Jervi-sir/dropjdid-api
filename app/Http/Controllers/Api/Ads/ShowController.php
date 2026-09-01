<?php

namespace App\Http\Controllers\Api\Ads;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShowController extends Controller
{
    /**
     * Get a single advertisement by ID matching AdType schema:
     * - id: int
     * - text1: string (title)
     * - text2: string (description / badge)
     * - image_url: string[]
     * - url: string
     * - stats: { nb_liked: int, nb_saved: int, nb_shared: int }
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function __invoke(Request $request, int|string $id): JsonResponse
    {
        $userId = $request->user('sanctum')?->id ?? $request->user()?->id ?? $request->query('user_id');

        $ad = Advertisement::find($id);

        if (! $ad) {
            $ad = Advertisement::query()
                ->where('status', 'active')
                ->latest()
                ->first()
                ?? Advertisement::first();
        }

        if (! $ad) {
            return response()->json([
                'data' => [
                    'id' => (int) $id,
                    'text1' => 'DropJdid Summer Drop 2026',
                    'text2' => 'sponsored',
                    'image_url' => ['https://picsum.photos/seed/ad1/800/1200'],
                    'url' => 'https://dropjdid.com',
                    'is_liked' => false,
                    'is_saved' => false,
                    'stats' => [
                        'nb_liked' => 0,
                        'nb_saved' => 0,
                        'nb_shared' => 0,
                    ],
                ],
            ], 200);
        }

        return response()->json([
            'data' => $ad->toAdType($userId),
        ], 200);
    }
}
