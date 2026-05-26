<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class ResolveSoftDeletedUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if ($token === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);
        if (! $accessToken) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = User::withTrashed()->find($accessToken->tokenable_id);
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if ($user->deleted_at !== null && $user->deleted_at->addDays(30)->isPast()) {
            return response()->json(['message' => 'Account permanently deleted.'], 401);
        }

        Auth::setUser($user);
        Auth::shouldUse('sanctum');
        $request->setUserResolver(static fn () => $user);

        return $next($request);
    }
}
