<?php

namespace App\Http\Controllers\Api\Ads;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdController extends Controller
{
    /**
     * Get a list of advertisements.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $ads = Advertisement::query()
            ->where(function ($q) {
                $q->where('status', 'active')
                  ->orWhereNull('status');
            })
            ->orderBy('sort_order', 'asc')
            ->latest()
            ->get();

        $data = $ads->map(fn (Advertisement $ad) => $ad->toAdType())->values();

        return response()->json([
            'data' => $data,
        ], 200);
    }

    /**
     * Get a single advertisement by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $ad = Advertisement::find($id);

        if (! $ad) {
            return response()->json([
                'message' => 'Advertisement not found.',
            ], 404);
        }

        return response()->json([
            'data' => $ad->toAdType(),
        ], 200);
    }

    /**
     * Format an advertisement into AdType schema.
     *
     * @param Advertisement $ad
     * @return array
     */
    public function formatAd(Advertisement $ad): array
    {
        return $ad->toAdType();
    }
}
