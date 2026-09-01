<?php

namespace App\Http\Controllers\Api\Sgm;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TutorialsController extends Controller
{
    /**
     * Return hardcoded paginated tutorials list.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $allTutorials = [
            [
                'id' => 1,
                'title' => 'I’m SGM, No way I’m gonna lose',
                'date' => '01 mai 2025',
                'created_at' => '2025-05-01T10:00:00Z',
                'image_url' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?w=800&auto=format&fit=crop&q=60',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'duration' => '5:24',
                'description' => 'Master your dropshipping business strategy with SGM insider tips.',
            ],
            [
                'id' => 2,
                'title' => 'How to boost your sales with drops',
                'date' => '15 mai 2025',
                'created_at' => '2025-05-15T14:30:00Z',
                'image_url' => 'https://images.unsplash.com/photo-1556742049-0a67e557224f?w=800&auto=format&fit=crop&q=60',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'duration' => '3:45',
                'description' => 'Learn step by step how to configure and promote your drops effectively.',
            ],
            [
                'id' => 3,
                'title' => 'Setting up store payments & wallet payout',
                'date' => '28 mai 2025',
                'created_at' => '2025-05-28T09:15:00Z',
                'image_url' => 'https://images.unsplash.com/photo-1559526324-4b87b5e36e44?w=800&auto=format&fit=crop&q=60',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'duration' => '8:10',
                'description' => 'A complete overview of wallet withdrawals and identity verification.',
            ],
            [
                'id' => 4,
                'title' => 'Optimizing product margins & pricing rules',
                'date' => '05 juin 2025',
                'created_at' => '2025-06-05T11:00:00Z',
                'image_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&auto=format&fit=crop&q=60',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'duration' => '4:50',
                'description' => 'Calculate your profit margins correctly to maximize revenue.',
            ],
            [
                'id' => 5,
                'title' => 'Connecting with top creators and affiliates',
                'date' => '20 juin 2025',
                'created_at' => '2025-06-20T16:00:00Z',
                'image_url' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=800&auto=format&fit=crop&q=60',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'duration' => '6:30',
                'description' => 'Build partnerships with creators to sell your store products fast.',
            ],
            [
                'id' => 6,
                'title' => 'Handling customer returns & delivery tracking',
                'date' => '02 juillet 2025',
                'created_at' => '2025-07-02T13:45:00Z',
                'image_url' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&auto=format&fit=crop&q=60',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'duration' => '7:15',
                'description' => 'Best practices for managing customer satisfaction and order tracking.',
            ],
        ];

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(50, (int) $request->query('per_page', 5)));

        $total = count($allTutorials);
        $offset = ($page - 1) * $perPage;
        $items = array_values(array_slice($allTutorials, $offset, $perPage));
        $hasMore = ($offset + $perPage) < $total;

        return response()->json([
            'data' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'next_page' => $hasMore ? ($page + 1) : null,
        ], 200);
    }
}
