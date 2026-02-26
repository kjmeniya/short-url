<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Services\NotificationService;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
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
            $permissions = Permission::select(['id', 'name', 'display_name', 'description', 'route_name', 'method', 'category', 'created_at']);

            // Apply filters
            if ($request->filled('category')) {
                $permissions->where('category', $request->category);
            }

            if ($request->filled('method')) {
                $permissions->where('method', $request->method);
            }

            // Apply date filter
            $permissions = $this->applyDateFilter($permissions, $request);

            return DataTables::of($permissions)
                ->addColumn('method', function ($permission) {
                    $methodColors = [
                        'GET' => 'bg-success',
                        'POST' => 'bg-primary',
                        'PUT' => 'bg-warning',
                        'PATCH' => 'bg-info',
                        'DELETE' => 'bg-danger'
                    ];
                    $color = $methodColors[$permission->method] ?? 'bg-secondary';
                    return '<span class="badge ' . $color . '">' . $permission->method . '</span>';
                })
                ->addColumn('category', function ($permission) {
                    return '<span class="badge bg-light text-dark">' . ucfirst(str_replace('_', ' ', $permission->category)) . '</span>';
                })
                ->addColumn('route_info', function ($permission) {
                    if ($permission->route_name) {
                        return '<small class="text-muted">Route: ' . $permission->route_name . '</small>';
                    }
                    return '<small class="text-muted">No route</small>';
                })
                ->addColumn('action', function ($permission) {
                    return '<div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="icon-sm" data-lucide="more-horizontal"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="' . route('admin.permissions.show', $permission->id) . '">
                                        <i data-lucide="eye" class="icon-sm me-2 text-success"></i>View
                                    </a></li>
                                </ul>
                            </div>';
                })
                ->editColumn('created_at', function ($permission) {
                    return formatUserDateTime($permission->created_at);
                })
                ->rawColumns(['method', 'category', 'route_info', 'action'])
                ->make(true);
        }

        $categories = Permission::getCategories();
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

        // Get statistics
        $stats = $this->getPermissionStats();

        $viewData = $this->withSeo(
            compact('categories', 'methods', 'stats'),
            'Permissions',
            'Manage system permissions and their assignments to roles.',
            'permissions, access control, authorization, security, roles'
        );

        return view('admin.permissions.index', $viewData);
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        $permission->load('roles');

        $viewData = $this->withSeo(
            compact('permission'),
            'Permission Details',
            "View details for {$permission->display_name} permission and its role assignments.",
            'permission details, role assignments, access control, authorization'
        );

        return view('admin.permissions.show', $viewData);
    }

    /**
     * Show the sync permissions page.
     */
    public function sync()
    {
        $viewData = $this->withSeo(
            [],
            'Sync Permissions',
            'Synchronize system permissions with application routes and controllers.',
            'sync permissions, routes, controllers, system permissions'
        );

        return view('admin.permissions.sync', $viewData);
    }

    /**
     * Sync permissions with routes.
     */
    public function syncPermissions(Request $request)
    {
        try {
            // Get all registered routes
            $routes = Route::getRoutes();
            $syncedCount = 0;
            $newPermissions = [];

            foreach ($routes as $route) {
                $routeName = $route->getName();
                $methods = $route->methods();
                $uri = $route->uri();

                // Skip routes without names or specific routes we don't want to manage
                if (!$routeName || $this->shouldSkipRoute($routeName, $uri)) {
                    continue;
                }

                // Determine category based on route name
                $category = $this->determineCategory($routeName);

                // Create permission for each HTTP method
                foreach ($methods as $method) {
                    if (in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'])) {
                        $permissionName = $routeName . '.' . strtolower($method);
                        $displayName = $this->generateDisplayName($routeName, $method);

                        $permission = Permission::updateOrCreate(
                            [
                                'name' => $permissionName,
                                'route_name' => $routeName,
                                'method' => $method,
                            ],
                            [
                                'display_name' => $displayName,
                                'description' => "Access to {$displayName}",
                                'category' => $category,
                            ]
                        );

                        if ($permission->wasRecentlyCreated) {
                            $newPermissions[] = $permission;
                            $syncedCount++;
                        }
                    }
                }
            }

            // Send notification to super admins if new permissions were created
            if ($syncedCount > 0) {
                $currentUser = Auth::user();
                $this->notificationService->sendToSuperAdmins(
                    'permission_synced',
                    'Permissions Synchronized',
                    "System permissions have been synchronized by {$currentUser->name}. {$syncedCount} new permissions were created.",
                    [
                        'synced_count' => $syncedCount,
                        'synced_by' => $currentUser->name,
                        'url' => route('admin.permissions.index')
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$syncedCount} new permissions.",
                'synced_count' => $syncedCount,
                'new_permissions' => $newPermissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error syncing permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Determine if a route should be skipped.
     */
    private function shouldSkipRoute(string $routeName, string $uri): bool
    {
        $skipRoutes = [
            'storage.local',
            'clear-cache',
            'up',
        ];

        $skipPatterns = [
            '/^auth\./',
            '/^password\./',
            '/^verification\./',
            '/debugbar/',
            '/telescope/',
        ];

        // Skip specific routes
        if (in_array($routeName, $skipRoutes)) {
            return true;
        }

        // Skip routes matching patterns
        foreach ($skipPatterns as $pattern) {
            if (preg_match($pattern, $routeName)) {
                return true;
            }
        }

        // Skip routes with parameters in URI that are not admin routes
        if (strpos($uri, '{') !== false && !str_starts_with($routeName, 'admin.')) {
            return true;
        }

        return false;
    }

    /**
     * Determine category based on route name.
     */
    private function determineCategory(string $routeName): string
    {
        if (str_starts_with($routeName, 'admin.')) {
            $parts = explode('.', $routeName);
            if (count($parts) >= 2) {
                return 'admin_' . $parts[1];
            }
            return 'admin';
        }

        $parts = explode('.', $routeName);
        return $parts[0] ?? 'general';
    }

    /**
     * Generate display name for permission.
     */
    private function generateDisplayName(string $routeName, string $method): string
    {
        $parts = explode('.', $routeName);
        $resource = end($parts);

        $methodNames = [
            'GET' => 'View',
            'POST' => 'Create',
            'PUT' => 'Update',
            'PATCH' => 'Update',
            'DELETE' => 'Delete',
        ];

        $methodName = $methodNames[$method] ?? $method;
        $resourceName = ucfirst(str_replace('_', ' ', $resource));

        return "{$methodName} {$resourceName}";
    }

    /**
     * Get permission statistics.
     */
    private function getPermissionStats(): array
    {
        $total = Permission::count();
        $assigned = Permission::has('roles')->count();
        $unassigned = Permission::doesntHave('roles')->count();
        $categories = Permission::distinct('category')->count('category');
        $totalRoles = \App\Models\Role::count();
        $rolesWithPermissions = \App\Models\Role::has('permissions')->count();

        return [
            'total' => $total,
            'assigned' => $assigned,
            'unassigned' => $unassigned,
            'categories' => $categories,
            'total_roles' => $totalRoles,
            'roles_with_permissions' => $rolesWithPermissions,
            'assignment_rate' => $total > 0 ? round($assigned / $total * 100, 2) : 0,
        ];
    }
}
