<?php

namespace App\Providers;

use App\Services\SettingsService;
use App\Services\AuthorizationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class, function () {
            return new SettingsService();
        });

        $this->app->singleton(AuthorizationService::class, function () {
            return new AuthorizationService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share settings with all views
        View::composer('*', function ($view) {
            $settingsService = app(SettingsService::class);

            // Share public settings with all views (for frontend)
            $view->with('publicSettings', $settingsService->getPublicSettings());

            // Share all settings for admin views (but not for settings management page)
            if (request()->is('admin/*') && !request()->is('admin/settings*')) {
                $view->with('allSettings', $settingsService->getAllSettings());
            }

            // Share specific commonly used settings
            $view->with('siteName', site_name());
            $view->with('siteDescription', site_description());
            $view->with('contactEmail', contact_email());
        });

        // Share dynamic settings groups with admin sidebar
        View::composer('admin.layout.partials.sidebar', function ($view) {
            $settingsService = app(SettingsService::class);
            $view->with('settingsGroups', $settingsService->getDynamicGroups());
        });
    }
}
