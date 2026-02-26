<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     * Allows only regular (non-admin) authenticated users to proceed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('user.login')
                ->with('error', 'Please log in to access your dashboard.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // If user is not active, log them out
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('user.login')
                ->with('error', 'Your account has been deactivated. Please contact support.');
        }

        // Admins / super-admins should use the admin panel, not the user panel
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('info', 'You are logged in as an administrator. Use the admin panel.');
        }

        return $next($request);
    }
}
