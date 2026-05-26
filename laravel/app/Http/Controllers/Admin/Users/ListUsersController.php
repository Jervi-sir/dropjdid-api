<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ListUsersController extends Controller
{
    /**
     * Display a listing of the users with search, role filters, and pagination.
     */
    public function __invoke(Request $request): Response
    {
        $query = User::query()
            ->with(['roles'])
            ->withCount(['drops', 'stores', 'orders']);

        // Apply search filter (by full_name, username, email, phone_number)
        if ($request->has('search') && $request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone_number', 'like', '%' . $search . '%');
            });
        }

        // Apply role filter (by role code)
        if ($request->has('role') && $request->filled('role') && $request->input('role') !== 'all') {
            $roleCode = $request->input('role');
            $query->whereHas('roles', function ($q) use ($roleCode) {
                $q->where('code', $roleCode);
            });
        }

        // Paginate results (default 10 per page)
        $perPage = $request->input('per_page', 10);
        $users = $query->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Format paginated users for Inertia response
        $formattedUsers = $users->through(function (User $user) {
            return [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'password_plaintext' => $user->password_plaintext,
                'image' => $user->image,
                'is_active' => (bool) $user->is_active,
                'roles' => $user->roles->map(fn(Role $role) => [
                    'id' => $role->id,
                    'code' => $role->code,
                    'en' => $role->en,
                ])->toArray(),
                'drops_count' => $user->drops_count,
                'stores_count' => $user->stores_count,
                'orders_count' => $user->orders_count,
                'created_at' => $user->created_at?->toIso8601String(),
            ];
        });

        // Compute high-level stats for the dashboard counters
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'creators' => User::whereHas('roles', fn($q) => $q->where('code', Role::CREATOR))->count(),
            'stores' => User::whereHas('roles', fn($q) => $q->where('code', Role::SGM))->count(),
        ];

        return Inertia::render('admin/users/list', [
            'users' => $formattedUsers,
            'filters' => [
                'search' => $request->input('search', ''),
                'role' => $request->input('role', 'all'),
                'per_page' => (int) $perPage,
            ],
            'roles' => Role::all()->map(fn(Role $r) => [
                'id' => $r->id,
                'code' => $r->code,
                'en' => $r->en,
            ])->toArray(),
            'stats' => $stats,
        ]);
    }
}
