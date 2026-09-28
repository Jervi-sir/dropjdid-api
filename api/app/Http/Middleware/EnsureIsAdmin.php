<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if authenticated via the 'admin' session guard
        if (auth('admin')->check()) {
            $admin = auth('admin')->user();
            if ($admin && ! $admin->is_approved) {
                auth('admin')->logout();
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['message' => 'Your admin account is pending approval or deactivated.'], 403);
                }
                return redirect()->guest(route('admin.login'))->withErrors(['login' => 'Your admin account is not approved.']);
            }
            return $next($request);
        }

        // 2. Check if current authenticated user (web or sanctum) is an instance of Admin
        $user = $request->user('admin') ?? $request->user('sanctum') ?? $request->user();

        if ($user instanceof Admin) {
            if (! $user->is_approved) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json(['message' => 'Your admin account is pending approval or deactivated.'], 403);
                }
                return redirect()->guest(route('admin.login'))->withErrors(['login' => 'Your admin account is not approved.']);
            }
            return $next($request);
        }

        // 3. Unauthorized response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

        return redirect()->guest(route('login'));
    }
}
