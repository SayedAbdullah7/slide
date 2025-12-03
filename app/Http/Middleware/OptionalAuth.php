<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OptionalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $guard = 'sanctum'): Response
    {
        // Check if a Bearer token is present
        if ($request->bearerToken()) {
            // Attempt to authenticate the user using the specified guard
            $user = Auth::guard($guard)->user();

            // If authentication is successful, set the user
            if ($user) {
                Auth::setUser($user);
            }
        }

        return $next($request);
    }
}
