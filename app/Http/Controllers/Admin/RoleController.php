<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Role;
use App\Models\Permission;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $roles = Role::with('permissions:id,name')
                ->withCount('users')
                ->select(['id', 'name', 'display_name', 'description', 'is_active', 'created_at']);

            // Apply filters
            if ($request->filled('status')) {
                $roles->where('is_active', $request->status === 'active');
            }

            // Apply date filter
            $roles = $this->applyDateFilter($roles, $request);

            return DataTables::of($roles)
                ->addColumn('status', function ($role) {
                    if ($role->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('permissions_count', function ($role) {
                    return '<span class="badge bg-info">' . $role->permissions->count() . ' permissions</span>';
                })
                ->addColumn('users_count', function ($role) {
                    return '<span class="badge bg-primary">' . $role->users_count . ' users</span>';
                })
                ->addColumn('action', function ($role) {
                    return '<div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="icon-sm" data-lucide="more-horizontal"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="' . route('admin.roles.show', $role->id) . '">
                                        <i data-lucide="eye" class="icon-sm me-2 text-success"></i>View
                                    </a></li>
                                    <li><a class="dropdown-item" href="' . route('admin.roles.edit', $role->id) . '">
                                        <i data-lucide="edit" class="icon-sm me-2 text-primary"></i>Edit
                                    </a></li>
                                    <li><a class="dropdown-item delete-role" href="#" data-id="' . $role->id . '">
                                        <i data-lucide="trash-2" class="icon-sm me-2 text-danger"></i>Delete
                                    </a></li>
                                </ul>
                            </div>';
                })
                ->editColumn('created_at', function ($role) {
                    return formatUserDateTime($role->created_at);
                })
                ->rawColumns(['status', 'permissions_count', 'users_count', 'action'])
                ->make(true);
        }
        // Get statistics
        $stats = $this->getRoleStats();

        $viewData = $this->withSeo(
            [],
            'Roles',
            'Manage user roles and their associated permissions in the system.',
            'roles, user roles, role management, access control, permissions'
        );
        $viewData['stats'] = $stats;

        return view('admin.roles.index', $viewData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::orderBy('category')->orderBy('display_name')->get()->groupBy('category');

        $viewData = $this->withSeo(
            compact('permissions'),
            'Create Role',
            'Create new user roles with custom permission assignments.',
            'create role, new role, role creation, add role, permissions'
        );

        return view('admin.roles.create', $viewData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();
        $permissionIds = $validated['permissions'] ?? [];
        unset($validated['permissions']);

        $role = Role::create($validated);

        if (!empty($permissionIds)) {
            $role->permissions()->sync($permissionIds);
        }

        // Send notification to super admins
        $this->notificationService->sendToSuperAdmins(
            'role_created',
            'New Role Created',
            "Role '{$role->display_name}' has been created by " . \Illuminate\Support\Facades\Auth::user()->name,
            [
                'role_id' => $role->id,
                'role_name' => $role->display_name,
                'created_by' => \Illuminate\Support\Facades\Auth::user()->name,
                'url' => route('admin.roles.show', $role->id)
            ]
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');

        $viewData = $this->withSeo(
            compact('role'),
            'Role Details',
            "View details for {$role->display_name} role, including permissions and assigned users.",
            'role details, role information, role permissions, role view'
        );

        return view('admin.roles.show', $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('category')->orderBy('display_name')->get()->groupBy('category');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        $viewData = $this->withSeo(
            compact('role', 'permissions', 'rolePermissions'),
            'Edit Role',
            "Edit {$role->display_name} role details and modify permission assignments.",
            'edit role, modify role, update role, role settings, permissions'
        );

        return view('admin.roles.edit', $viewData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();
        $permissionIds = $validated['permissions'] ?? [];
        unset($validated['permissions']);

        // Store original permissions for comparison
        $originalPermissions = $role->permissions()->pluck('permissions.id')->toArray();

        // For system roles, preserve certain fields
        if ($role->isSystemRole()) {
            // Keep original name and is_active status for system roles
            unset($validated['name'], $validated['is_active']);
        }

        $role->update($validated);
        $role->permissions()->sync($permissionIds);

        // Send notification to super admins
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        $permissionsChanged = array_diff($originalPermissions, $permissionIds) || array_diff($permissionIds, $originalPermissions);

        if ($permissionsChanged) {
            $this->notificationService->sendToSuperAdmins(
                'permission_updated',
                'Role Permissions Updated',
                "Permissions for role '{$role->display_name}' have been updated by {$currentUser->name}",
                [
                    'role_id' => $role->id,
                    'role_name' => $role->display_name,
                    'updated_by' => $currentUser->name,
                    'url' => route('admin.roles.show', $role->id)
                ]
            );
        } else {
            $this->notificationService->sendToSuperAdmins(
                'role_updated',
                'Role Updated',
                "Role '{$role->display_name}' has been updated by {$currentUser->name}",
                [
                    'role_id' => $role->id,
                    'role_name' => $role->display_name,
                    'updated_by' => $currentUser->name,
                    'url' => route('admin.roles.show', $role->id)
                ]
            );
        }

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Check if role has users assigned
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role that has users assigned to it.'
            ], 422);
        }

        // Prevent deletion of system roles
        if ($role->isSystemRole()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete system roles. System roles are protected and cannot be removed.'
            ], 422);
        }

        // Store role info before deletion
        $roleName = $role->display_name;
        $roleId = $role->id;

        $role->delete();

        // Send notification to super admins
        $currentUser = \Illuminate\Support\Facades\Auth::user();
        $this->notificationService->sendToSuperAdmins(
            'role_deleted',
            'Role Deleted',
            "Role '{$roleName}' has been deleted by {$currentUser->name}",
            [
                'role_id' => $roleId,
                'role_name' => $roleName,
                'deleted_by' => $currentUser->name,
                'url' => route('admin.roles.index')
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully.'
        ]);
    }

    /**
     * Get role statistics.
     */
    private function getRoleStats(): array
    {
        $total = Role::count();
        $active = Role::where('is_active', true)->count();
        $inactive = Role::where('is_active', false)->count();
        $withUsers = Role::has('users')->count();
        $withoutUsers = Role::doesntHave('users')->count();
        $totalUsers = \App\Models\User::count();
        $usersWithRoles = \App\Models\User::whereNotNull('role_id')->count();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'with_users' => $withUsers,
            'without_users' => $withoutUsers,
            'total_users' => $totalUsers,
            'users_with_roles' => $usersWithRoles,
            'active_rate' => $total > 0 ? round($active / $total * 100, 2) : 0,
        ];
    }
}
