<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all registered routes
        $routes = Route::getRoutes();

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

                    Permission::updateOrCreate(
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
                }
            }
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
}
