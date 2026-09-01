<?php

namespace App\Http\Controllers\Admin\Creators;

use App\Http\Controllers\Controller;
use App\Models\CreatorRequest;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListRequestController extends Controller
{
    /**
     * Display a paginated listing of creator join requests.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));

        $query = CreatorRequest::query()
            ->with(['user.roles']);

        // Filter by status if specified
        if ($status && $status !== 'all') {
            $query->where('request_status', $status);
        }

        // Search by user full_name, username, email, or request phone_number
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('full_name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->latest('id')->paginate(10)->withQueryString();

        // Calculate count metrics for tab badges
        $counts = [
            'all' => CreatorRequest::count(),
            'pending' => CreatorRequest::where('request_status', 'pending')->count(),
            'approved' => CreatorRequest::where('request_status', 'approved')->count(),
            'rejected' => CreatorRequest::where('request_status', 'rejected')->count(),
        ];

        return Inertia::render('admin/creators/list.request', [
            'requests' => $requests,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'counts' => $counts,
        ]);
    }

    /**
     * Approve a creator request and grant the creator role.
     */
    public function approve(Request $request, CreatorRequest $creatorRequest): RedirectResponse
    {
        $creatorRequest->update([
            'request_status' => 'approved',
            'reviewed_at' => now(),
        ]);

        $creatorRole = Role::firstOrCreate(
            ['code' => 'creator'],
            [
                'en' => 'Creator',
                'fr' => 'Créateur',
                'ar' => 'صانع محتوى',
            ]
        );

        if ($creatorRequest->user_id) {
            UserRole::firstOrCreate([
                'user_id' => $creatorRequest->user_id,
                'role_id' => $creatorRole->id,
            ]);
        }

        return back()->with('success', 'Creator request approved successfully.');
    }

    /**
     * Reject a creator request.
     */
    public function reject(Request $request, CreatorRequest $creatorRequest): RedirectResponse
    {
        $creatorRequest->update([
            'request_status' => 'rejected',
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Creator request rejected.');
    }
}
