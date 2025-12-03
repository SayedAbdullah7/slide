<?php

namespace App\Http\Middleware;

use App\Support\CurrentProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectCurrentProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user) {
            // Check if user is Admin model (doesn't have profile methods)
            if ($user instanceof \App\Models\Admin) {
                $current = new CurrentProfile(
                    type: 'admin',
                    model: null,
                    user: $user
                );
            } else {
                // Handle User model with profiles
                $current = new CurrentProfile(
                    type: $user->active_profile_type,
                    model: $user->activeProfile(),
                    user: $user
                );
            }
            app()->instance(CurrentProfile::class, $current);
            // متاح في أي مكان via type-hint CurrentProfile
            $request->attributes->set('currentProfile', $current);
        }
        return $next($request);
    }
}
