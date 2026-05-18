<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowUserController extends Controller
{
    /**
     * Display the user details and management view (XHR or page view).
     */
    public function show(Request $request, User $user): Response|JsonResponse
    {
        $user->load(['roles', 'stores', 'wallets']);

        $formattedUser = [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'username' => $user->username,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'image' => $user->image,
            'is_active' => (bool) $user->is_active,
            'roles' => $user->roles->map(fn (Role $r) => [
                'id' => $r->id,
                'code' => $r->code,
                'en' => $r->en,
            ])->toArray(),
            'stores' => $user->stores->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
            ])->toArray(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'user' => $formattedUser,
                'all_roles' => Role::all()->map(fn (Role $r) => [
                    'id' => $r->id,
                    'code' => $r->code,
                    'en' => $r->en,
                ])->toArray(),
            ]);
        }

        return Inertia::render('admin/users/show', [
            'user' => $formattedUser,
            'all_roles' => Role::all()->map(fn (Role $r) => [
                'id' => $r->id,
                'code' => $r->code,
                'en' => $r->en,
            ])->toArray(),
        ]);
    }

    /**
     * Update the user roles and active status.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'is_active' => 'required|boolean',
        ]);

        $user->is_active = $validated['is_active'];
        $user->save();

        // Sync the roles (Many-to-Many sync)
        $user->roles()->sync($validated['role_ids']);

        return back()->with('success', 'User roles and status updated successfully.');
    }
}
