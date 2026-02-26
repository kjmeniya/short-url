<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthorizationService
{
    /**
     * Check if the current user can access a specific route.
     */
    public function canAccessRoute(string $routeName): bool
    {
        if (!Auth::check()) {
            return false;
        }

        /** @var User $user */
        $user = Auth::user();

        // Super admin has access to everything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Get required permission for this route
        $permission = $this->getRoutePermission($routeName);

        return $permission ? $user->hasPermission($permission) : true;
    }

    /**
     * Get permission based on route name.
     */
    private function getRoutePermission(string $routeName): ?string
    {
        // For admin routes, check if exact permission exists first (try common HTTP methods)
        if (str_starts_with($routeName, 'admin.')) {
            $commonMethods = ['get', 'post', 'put', 'patch', 'delete'];

            foreach ($commonMethods as $method) {
                $exactPermission = $routeName . '.' . $method;

                // Check if this exact permission exists in database
                $permissionExists = \App\Models\Permission::where('name', $exactPermission)->exists();
                if ($permissionExists) {
                    return $exactPermission;
                }
            }
        }

        // Default: return null (no permission check)
        return null;
    }

    /**
     * Check if the current user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (!Auth::check()) {
            return false;
        }

        /** @var User $user */
        $user = Auth::user();

        return $user->hasPermission($permission);
    }

    /**
     * Check if the current user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        /** @var User $user */
        $user = Auth::user();

        // Super admin has all permissions
        if ($user->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        /** @var User $user */
        $user = Auth::user();

        // Super admin has all permissions
        if ($user->isSuperAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }



    /**
     * Get user's role name.
     */
    public function getUserRole(): ?string
    {
        if (!Auth::check()) {
            return null;
        }

        /** @var User $user */
        $user = Auth::user();

        return $user->role?->name;
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleName): bool
    {
        if (!Auth::check()) {
            return false;
        }

        /** @var User $user */
        $user = Auth::user();

        return $user->role?->name === $roleName;
    }
}
