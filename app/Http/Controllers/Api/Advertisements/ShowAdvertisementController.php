<?php

namespace App\Http\Controllers\Api\Advertisements;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;

class ShowAdvertisementController extends Controller
{
    public function __invoke(int $advertisement_id): JsonResponse
    {
        $advertisement = Advertisement::findOrFail($advertisement_id);

        return response()->json([
            'data' => $advertisement->toDetailArray(),
        ]);
    }
}
