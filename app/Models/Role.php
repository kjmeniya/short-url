<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    /**
     * Default system role constants.
     */
    public const SUPER_ADMIN_ID = 1;
    public const ADMIN_ID = 2;
    public const USER_ID = 3;

    public const SUPER_ADMIN_NAME = 'super_admin';
    public const ADMIN_NAME = 'admin';
    public const USER_NAME = 'user';

    /**
     * System role IDs that cannot be deleted.
     */
    public const SYSTEM_ROLE_IDS = [
        self::SUPER_ADMIN_ID,
        self::ADMIN_ID,
        self::USER_ID,
    ];

    /**
     * System role names that cannot be deleted.
     */
    public const SYSTEM_ROLE_NAMES = [
        self::SUPER_ADMIN_NAME,
        self::ADMIN_NAME,
        self::USER_NAME,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
        'is_system',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Get the permissions for the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Get the users for the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->id === self::SUPER_ADMIN_ID) {
            return true;
        }

        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Check if this role is a system role (cannot be deleted).
     */
    public function isSystemRole(): bool
    {
        return $this->is_system || in_array($this->id, self::SYSTEM_ROLE_IDS);
    }

    /**
     * Check if this role is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->id === self::SUPER_ADMIN_ID;
    }

    /**
     * Check if this role is admin.
     */
    public function isAdmin(): bool
    {
        return $this->id === self::ADMIN_ID;
    }

    /**
     * Check if this role is regular user.
     */
    public function isUser(): bool
    {
        return $this->id === self::USER_ID;
    }

    /**
     * Assign permission to role.
     */
    public function givePermission(Permission $permission): void
    {
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    /**
     * Remove permission from role.
     */
    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    /**
     * Sync permissions for role.
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    /**
     * Get active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get role hierarchy level (lower number = higher privilege).
     */
    public function getHierarchyLevel(): int
    {
        return match($this->id) {
            self::SUPER_ADMIN_ID => 1,
            self::ADMIN_ID => 2,
            self::USER_ID => 3,
            default => 4, // Custom roles have lowest privilege
        };
    }

    /**
     * Check if this role can manage another role.
     */
    public function canManage(Role $otherRole): bool
    {
        return $this->getHierarchyLevel() < $otherRole->getHierarchyLevel();
    }

    /**
     * Check if this role can view another role's data.
     */
    public function canView(Role $otherRole): bool
    {
        return $this->getHierarchyLevel() <= $otherRole->getHierarchyLevel();
    }

    /**
     * Get roles that this role can manage.
     */
    public function getManageableRoles()
    {
        $currentLevel = $this->getHierarchyLevel();

        return static::where(function($query) use ($currentLevel) {
            // Can manage roles with higher hierarchy level (lower privilege)
            $query->whereIn('id', function($subQuery) use ($currentLevel) {
                $subQuery->selectRaw('id')
                    ->from('roles')
                    ->whereRaw('CASE
                        WHEN id = ' . self::SUPER_ADMIN_ID . ' THEN 1
                        WHEN id = ' . self::ADMIN_ID . ' THEN 2
                        WHEN id = ' . self::USER_ID . ' THEN 3
                        ELSE 4
                    END > ?', [$currentLevel]);
            });
        });
    }

    /**
     * Get roles that this role can view.
     */
    public function getViewableRoles()
    {
        $currentLevel = $this->getHierarchyLevel();

        return static::where(function($query) use ($currentLevel) {
            // Can view roles with same or higher hierarchy level (same or lower privilege)
            $query->whereIn('id', function($subQuery) use ($currentLevel) {
                $subQuery->selectRaw('id')
                    ->from('roles')
                    ->whereRaw('CASE
                        WHEN id = ' . self::SUPER_ADMIN_ID . ' THEN 1
                        WHEN id = ' . self::ADMIN_ID . ' THEN 2
                        WHEN id = ' . self::USER_ID . ' THEN 3
                        ELSE 4
                    END >= ?', [$currentLevel]);
            });
        });
    }
}
