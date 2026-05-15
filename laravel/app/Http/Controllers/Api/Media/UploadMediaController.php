<?php

namespace App\Http\Controllers\Api\Media;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadMediaController extends Controller
{
    /**
     * Upload an image and return its URL and path.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,gif,svg,webp,heic,heif|max:10240', // 10MB max
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $url = asset('storage/'.$path);

            return response()->json([
                'url' => $url,
                'path' => $path,
            ]);
        }

        return response()->json(['message' => 'No image uploaded'], 400);
    }
}
