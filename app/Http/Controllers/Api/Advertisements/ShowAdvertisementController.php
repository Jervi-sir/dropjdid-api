<?php

namespace App\Http\Controllers\Api\Advertisements;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;

class ShowAdvertisementController extends Controller
{
    public function __invoke(int $id): JsonResponse
    {
        $advertisement = Advertisement::find($id);
        abort_unless(
            $advertisement !== null,
            404,
        );

        return response()->json([
            'data' => $advertisement->toDetailArray(),
        ]);
    }
}
