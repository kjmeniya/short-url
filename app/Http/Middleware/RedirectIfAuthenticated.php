<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $guard
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        if (Auth::guard($guard)->check()) {
            $user = Auth::guard($guard)->user();

            // Redirect authenticated admin users to dashboard
            if ($user && $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            // For non-admin users, redirect to home or appropriate page
            return redirect('/');
        }

        return $next($request);
    }
}
