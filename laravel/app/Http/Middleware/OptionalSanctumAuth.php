<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken() !== null) {
            Auth::shouldUse('sanctum');

            if (Auth::guard('sanctum')->check()) {
                $request->setUserResolver(static fn () => Auth::guard('sanctum')->user());
            }
        }

        return $next($request);
    }
}
