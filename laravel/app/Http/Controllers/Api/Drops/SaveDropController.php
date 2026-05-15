<?php

namespace App\Http\Controllers\Api\Drops;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\SavedDrop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaveDropController extends Controller
{
    public function __invoke(Request $request, $drop_id): JsonResponse
    {
        $user = $request->user();

        $drop = Drop::find($drop_id);

        abort_if($user === null, 401);

        $save = SavedDrop::query()->where([
            'user_id' => $user->id,
            'drop_id' => $drop->id,
        ])->first();

        if ($save !== null) {
            $save->delete();

            return response()->json([
                'message' => 'Drop unsaved successfully.',
                'is_saved' => false,
            ]);
        }

        SavedDrop::query()->create([
            'user_id' => $user->id,
            'drop_id' => $drop->id,
        ]);

        return response()->json([
            'message' => 'Drop saved successfully.',
            'is_saved' => true,
        ]);
    }
}
