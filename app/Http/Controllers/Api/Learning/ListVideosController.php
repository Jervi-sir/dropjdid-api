<?php

namespace App\Http\Controllers\Api\Learning;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ListVideosController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                [
                    'id' => 1,
                    'title' => 'I\'m SGM, No way I\'m gonna lose',
                    'published_at' => '01 mai 2025',
                    'video_url' => 'https://www.youtube.com/watch?v=sgm-1',
                ],
                [
                    'id' => 2,
                    'title' => 'How to shoot better product videos',
                    'published_at' => '03 mai 2025',
                    'video_url' => 'https://www.youtube.com/watch?v=shoot-2',
                ],
                [
                    'id' => 3,
                    'title' => 'Creator tips for your next drop',
                    'published_at' => '07 mai 2025',
                    'video_url' => 'https://www.youtube.com/watch?v=drop-3',
                ],
                [
                    'id' => 4,
                    'title' => 'Editing tricks for reels that convert',
                    'published_at' => '10 mai 2025',
                    'video_url' => 'https://www.youtube.com/watch?v=reel-4',
                ],
            ],
        ]);
    }
}
