<?php

namespace App\Http\Controllers\Api\Saves;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Models\SavedLabel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaveLabelController extends Controller
{
    public function __invoke(Request $request, int $label_id): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $label = Label::find($label_id);
        abort_if($label === null, 404);

        $like = SavedLabel::query()->where([
            'user_id' => $user->id,
            'label_id' => $label->id,
        ])->first();

        if ($like !== null) {
            $like->delete();

            return response()->json([
                'message' => 'Label unliked successfully.',
                'is_liked' => false,
                'nb_likes' => SavedLabel::where('label_id', $label->id)->count(),
            ]);
        }

        SavedLabel::query()->create([
            'user_id' => $user->id,
            'label_id' => $label->id,
        ]);

        return response()->json([
            'message' => 'Label liked successfully.',
            'is_liked' => true,
            'nb_likes' => SavedLabel::where('label_id', $label->id)->count(),
        ]);
    }
}
