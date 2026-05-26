<?php

namespace App\Http\Controllers\Admin\UserSupportRequest;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\UserSupportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BecomeCreatorController extends Controller
{
    /**
     * Display a listing of become-creator requests.
     */
    public function index(Request $request): Response
    {
        $query = UserSupportRequest::query()
            ->with(['user.roles'])
            ->where('target', UserSupportRequest::TARGET_BECOME_CREATOR);

        // Filter by status
        if ($request->has('status') && $request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Apply search filter (by contact or user name/username/email)
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('contact', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('full_name', 'like', '%'.$search.'%')
                            ->orWhere('username', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        $perPage = $request->input('per_page', 10);
        $requests = $query->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        $formattedRequests = $requests->through(function (UserSupportRequest $sr) {
            return [
                'id' => $sr->id,
                'user_id' => $sr->user_id,
                'contact' => $sr->contact,
                'type' => $sr->type,
                'status' => $sr->status,
                'status_label' => UserSupportRequest::STATUS[$sr->status] ?? 'pending',
                'note' => $sr->note,
                'reviewed_at' => $sr->reviewed_at?->toIso8601String(),
                'created_at' => $sr->created_at?->toIso8601String(),
                'user' => $sr->user ? [
                    'id' => $sr->user->id,
                    'full_name' => $sr->user->full_name,
                    'username' => $sr->user->username,
                    'email' => $sr->user->email,
                    'phone_number' => $sr->user->phone_number,
                    'image' => $sr->user->image,
                    'is_active' => (bool) $sr->user->is_active,
                    'roles' => $sr->user->roles->map(fn (Role $r) => [
                        'id' => $r->id,
                        'code' => $r->code,
                        'en' => $r->en,
                    ])->toArray(),
                ] : null,
            ];
        });

        // Compute high-level stats for creator requests
        $stats = [
            'total' => UserSupportRequest::where('target', UserSupportRequest::TARGET_BECOME_CREATOR)->count(),
            'pending' => UserSupportRequest::where('target', UserSupportRequest::TARGET_BECOME_CREATOR)->where('status', UserSupportRequest::STATUS_PENDING)->count(),
            'approved' => UserSupportRequest::where('target', UserSupportRequest::TARGET_BECOME_CREATOR)->where('status', UserSupportRequest::STATUS_APPROVED)->count(),
            'rejected' => UserSupportRequest::where('target', UserSupportRequest::TARGET_BECOME_CREATOR)->where('status', UserSupportRequest::STATUS_REJECTED)->count(),
        ];

        return Inertia::render('admin/list-to-approve/approve-creators', [
            'requests' => $formattedRequests,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', 'all'),
                'per_page' => (int) $perPage,
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Show details of a single creator request.
     */
    public function show(UserSupportRequest $supportRequest): JsonResponse
    {
        $supportRequest->load(['user.roles', 'user.stores']);

        $formattedRequest = [
            'id' => $supportRequest->id,
            'user_id' => $supportRequest->user_id,
            'contact' => $supportRequest->contact,
            'type' => $supportRequest->type,
            'status' => $supportRequest->status,
            'status_label' => UserSupportRequest::STATUS[$supportRequest->status] ?? 'pending',
            'note' => $supportRequest->note,
            'reviewed_at' => $supportRequest->reviewed_at?->toIso8601String(),
            'created_at' => $supportRequest->created_at?->toIso8601String(),
            'user' => $supportRequest->user ? [
                'id' => $supportRequest->user->id,
                'full_name' => $supportRequest->user->full_name,
                'username' => $supportRequest->user->username,
                'email' => $supportRequest->user->email,
                'phone_number' => $supportRequest->user->phone_number,
                'image' => $supportRequest->user->image,
                'is_active' => (bool) $supportRequest->user->is_active,
                'roles' => $supportRequest->user->roles->map(fn (Role $r) => [
                    'id' => $r->id,
                    'code' => $r->code,
                    'en' => $r->en,
                ])->toArray(),
                'stores' => $supportRequest->user->stores->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                ])->toArray(),
                'created_at' => $supportRequest->user->created_at?->toIso8601String(),
            ] : null,
        ];

        return response()->json([
            'request' => $formattedRequest,
        ]);
    }

    /**
     * Approve the become-creator request.
     */
    public function approve(UserSupportRequest $supportRequest): RedirectResponse
    {
        $supportRequest->status = UserSupportRequest::STATUS_APPROVED;
        $supportRequest->reviewed_at = now();
        $supportRequest->save();

        $user = $supportRequest->user;
        if ($user) {
            $role = Role::where('code', Role::CREATOR)->first();
            if ($role) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }

        return back()->with('success', 'Creator request approved and role assigned.');
    }

    /**
     * Reject the become-creator request.
     */
    public function reject(Request $request, UserSupportRequest $supportRequest): RedirectResponse
    {
        $validated = $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        $supportRequest->status = UserSupportRequest::STATUS_REJECTED;
        $supportRequest->note = $validated['note'];
        $supportRequest->reviewed_at = now();
        $supportRequest->save();

        return back()->with('success', 'Creator request rejected.');
    }
}
