<?php

namespace App\Http\Controllers\Api\Advertisements;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;

class ShowController extends Controller
{
    public function __invoke(Advertisement $advertisement): JsonResponse
    {
        abort_unless(
            Advertisement::query()->whereKey($advertisement->id)->activeForFeed()->exists(),
            404,
        );

        return response()->json([
            'data' => $advertisement->toDetailArray(),
        ]);
    }
}
