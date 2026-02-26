<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Apply settings to Laravel configuration
        if ($this->app->runningInConsole() === false) {
            try {
                $configService = $this->app->make(\App\Services\ConfigurationService::class);
                $configService->applySettings();
            } catch (\Exception $e) {
                // Silently fail during installation or when database is not available
                Log::debug('Settings could not be applied: ' . $e->getMessage());
            }
        }

        // Register custom Blade directives for authorization
        $this->registerBladeDirectives();
    }

    /**
     * Register custom Blade directives.
     */
    private function registerBladeDirectives(): void
    {
        // @canAccess directive for route access
        Blade::if('canAccess', function ($routeName) {
            return canAccessRoute($routeName);
        });

        // @hasPermission directive
        Blade::if('hasPermission', function ($permission) {
            return hasPermission($permission);
        });

        // @hasAnyPermission directive
        Blade::if('hasAnyPermission', function (...$permissions) {
            return hasAnyPermission($permissions);
        });

        // @hasAllPermissions directive
        Blade::if('hasAllPermissions', function (...$permissions) {
            return hasAllPermissions($permissions);
        });

        // @hasRole directive
        Blade::if('hasRole', function ($role) {
            return hasRole($role);
        });
    }
}
