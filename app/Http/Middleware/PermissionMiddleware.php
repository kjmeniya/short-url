<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $permission Optional specific permission to check
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        if (!Auth::check()) {
            return redirect()->route('auth.login');
        }

        /** @var User $user */
        $user = Auth::user();

        // Check if user account is active
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('auth.login')->with('error', 'Your account has been deactivated.');
        }

        // Super admin has all permissions
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // If specific permission provided, check it
        if ($permission) {
            if (!$user->hasPermission($permission)) {
                abort(403, 'Access denied. You do not have the required permission: ' . $permission);
            }
            return $next($request);
        }

        // Auto-detect permission from route
        $routePermission = $this->getRoutePermission($request);
        if ($routePermission && !$user->hasPermission($routePermission)) {
            abort(403, 'Access denied. You do not have the required permission: ' . $routePermission);
        }

        return $next($request);
    }

    /**
     * Get permission based on current route.
     */
    private function getRoutePermission(Request $request): ?string
    {
        $route = $request->route();
        if (!$route) {
            return null;
        }

        $routeName = $route->getName();
        if (!$routeName) {
            return null;
        }

        // For admin routes, we perform strict checking
        // Format: route.name + . + lowercase http method
        if (str_starts_with($routeName, 'admin.')) {
            $method = strtolower($request->getMethod());
            return $routeName . '.' . $method;
        }

        return null;
    }
}
