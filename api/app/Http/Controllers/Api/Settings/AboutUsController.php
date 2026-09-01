<?php

namespace App\Http\Controllers\Api\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    /**
     * Get About Us content and company overview sections.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'title' => 'Welcome to dropjdid!',
            'sections' => [
                [
                    'id' => 1,
                    'title' => 'Who are we?',
                    'content' => 'dropjdid is a fashion platform built in a way you’ve never seen before.',
                ],
                [
                    'id' => 2,
                    'title' => 'Imagine:',
                    'content' => 'you wear what you love, and you see who else is wearing it.',
                ],
                [
                    'id' => 3,
                    'title' => 'Creators on the platform do drops:',
                    'content' => 'They pick the pieces, wear them, shoot them in their own style. If you like something on one of them, you buy it directly.',
                ],
                [
                    'id' => 4,
                    'title' => 'In short:',
                    'content' => 'People buy clothes because they saw someone wearing them and liked what they saw. dropjdid built an entire platform around exactly that moment.',
                ],
                [
                    'id' => 5,
                    'title' => 'Get in Touch:',
                    'content' => 'We welcome any inquiries or feedback from you, so feel free to reach out to us at [Contact Support Team].',
                    'action_text' => 'Contact Support Team',
                    'action_route' => 'SupportScreen',
                ],
            ],
        ], 200);
    }
}
