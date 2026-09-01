<?php

namespace App\Http\Controllers\Admin\Sgm;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SupportRequest;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListRequestController extends Controller
{
    /**
     * Display a paginated listing of SGM (store owner) join requests.
     */
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));

        $query = SupportRequest::query()
            ->where('target', 'become-sgm')
            ->with(['user.roles']);

        // Filter by status if specified
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Search by user full_name, username, email, or request contact/note
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('contact', 'like', "%{$search}%")
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
            'all' => SupportRequest::where('target', 'become-sgm')->count(),
            'pending' => SupportRequest::where('target', 'become-sgm')->where('status', 'pending')->count(),
            'approved' => SupportRequest::where('target', 'become-sgm')->where('status', 'approved')->count(),
            'rejected' => SupportRequest::where('target', 'become-sgm')->where('status', 'rejected')->count(),
        ];

        return Inertia::render('admin/sgms/list.request', [
            'requests' => $requests,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'counts' => $counts,
        ]);
    }

    /**
     * Approve an SGM request and grant the SGM role to the user.
     */
    public function approve(Request $request, SupportRequest $sgmRequest): RedirectResponse
    {
        $sgmRequest->update([
            'status' => 'approved',
        ]);

        $sgmRole = Role::firstOrCreate(
            ['code' => 'sgm'],
            [
                'en' => 'Store General Manager',
                'fr' => 'Gérant de magasin',
                'ar' => 'مدير متجر',
            ]
        );

        if ($sgmRequest->user_id) {
            UserRole::firstOrCreate([
                'user_id' => $sgmRequest->user_id,
                'role_id' => $sgmRole->id,
            ]);
        }

        return back()->with('success', 'Store request approved successfully.');
    }

    /**
     * Reject an SGM request.
     */
    public function reject(Request $request, SupportRequest $sgmRequest): RedirectResponse
    {
        $sgmRequest->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Store request rejected.');
    }
}
