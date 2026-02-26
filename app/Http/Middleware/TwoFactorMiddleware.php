<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if 2FA is globally disabled
        if (!two_factor_auth_enabled()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip if user is not authenticated
        if (!$user) {
            return $next($request);
        }

        // Skip if user doesn't have 2FA enabled
        if (!$user->two_factor_enabled) {
            return $next($request);
        }

        // Skip if already on 2FA verification routes
        if ($request->routeIs('auth.two-factor.*')) {
            return $next($request);
        }

        // Skip if on 2FA setup/management routes (allow users to manage their 2FA settings)
        if ($request->routeIs('admin.profile.two-factor.*') || $request->routeIs('admin.users.two-factor.*')) {
            return $next($request);
        }

        // Skip if already verified in this session
        if (session('two_factor_verified')) {
            return $next($request);
        }

        // Redirect to 2FA verification
        return redirect()->route('auth.two-factor.verify');
    }
}
