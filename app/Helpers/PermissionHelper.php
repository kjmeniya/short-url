<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('hasPermission')) {
    /**
     * Check if the current authenticated user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    function hasPermission(string $permission): bool
    {
        $authService = app(\App\Services\AuthorizationService::class);
        return $authService->hasPermission($permission);
    }
}

if (!function_exists('hasRole')) {
    /**
     * Check if the current authenticated user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    function hasRole(string $role): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Check new role system only
        if ($user->role_id && $user->role) {
            return $user->role->name === $role;
        }

        return false;
    }
}

if (!function_exists('hasAnyRole')) {
    /**
     * Check if the current authenticated user has any of the specified roles.
     *
     * @param array $roles
     * @return bool
     */
    function hasAnyRole(array $roles): bool
    {
        if (!Auth::check()) {
            return false;
        }

        foreach ($roles as $role) {
            if (hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('hasAllRoles')) {
    /**
     * Check if the current authenticated user has all of the specified roles.
     *
     * @param array $roles
     * @return bool
     */
    function hasAllRoles(array $roles): bool
    {
        if (!Auth::check()) {
            return false;
        }

        foreach ($roles as $role) {
            if (!hasRole($role)) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('hasAnyPermission')) {
    /**
     * Check if the current authenticated user has any of the specified permissions.
     *
     * @param array $permissions
     * @return bool
     */
    function hasAnyPermission(array $permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('hasAllPermissions')) {
    /**
     * Check if the current authenticated user has all of the specified permissions.
     *
     * @param array $permissions
     * @return bool
     */
    function hasAllPermissions(array $permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('isAdmin')) {
    /**
     * Check if the current authenticated user is an admin.
     *
     * @return bool
     */
    function isAdmin(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        return in_array($user->role_id, [1, 2]); // Super admin (1) or Admin (2)
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Check if the current authenticated user is a super admin.
     *
     * @return bool
     */
    function isSuperAdmin(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->role_id === 1;
    }
}

if (!function_exists('isUser')) {
    /**
     * Check if the current authenticated user is a regular user.
     *
     * @return bool
     */
    function isUser(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user()->role_id === 3; // User role
    }
}

if (!function_exists('getUserRole')) {
    /**
     * Get the current authenticated user's role.
     *
     * @return string|null
     */
    function getUserRole(): ?string
    {
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();

        // Return new role system name only
        if ($user->role_id && $user->role) {
            return $user->role->name;
        }

        return null;
    }
}

if (!function_exists('getUserRoleDisplayName')) {
    /**
     * Get the current authenticated user's role display name.
     *
     * @return string|null
     */
    function getUserRoleDisplayName(): ?string
    {
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();

        // Return new role system display name only
        if ($user->role_id && $user->role) {
            return $user->role->display_name;
        }

        return null;
    }
}

if (!function_exists('canAccessRoute')) {
    /**
     * Check if the current authenticated user can access a specific route.
     *
     * @param string $routeName
     * @return bool
     */
    function canAccessRoute(string $routeName): bool
    {
        $authService = app(\App\Services\AuthorizationService::class);
        return $authService->canAccessRoute($routeName);
    }
}

if (!function_exists('hasAnyPermission')) {
    /**
     * Check if the current user has any of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    function hasAnyPermission(array $permissions): bool
    {
        $authService = app(\App\Services\AuthorizationService::class);
        return $authService->hasAnyPermission($permissions);
    }
}

if (!function_exists('hasAllPermissions')) {
    /**
     * Check if the current user has all of the given permissions.
     *
     * @param array $permissions
     * @return bool
     */
    function hasAllPermissions(array $permissions): bool
    {
        $authService = app(\App\Services\AuthorizationService::class);
        return $authService->hasAllPermissions($permissions);
    }
}



if (!function_exists('hasRole')) {
    /**
     * Check if the current user has a specific role.
     *
     * @param string $roleName
     * @return bool
     */
    function hasRole(string $roleName): bool
    {
        $authService = app(\App\Services\AuthorizationService::class);
        return $authService->hasRole($roleName);
    }
}
