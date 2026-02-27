<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'two-factor' => \App\Http\Middleware\TwoFactorMiddleware::class,
            'maintenance' => \App\Http\Middleware\CheckMaintenanceMode::class,
            'throttle.login' => \App\Http\Middleware\ThrottleLoginAttempts::class,
            'api.throttle' => \App\Http\Middleware\ApiThrottleMiddleware::class,
            'cache.headers' => \App\Http\Middleware\SetCacheHeaders::class,
            'user.role' => \App\Http\Middleware\EnsureUserRole::class,
        ]);

        // Apply cache headers to web routes
        $middleware->web(append: [
            \App\Http\Middleware\SetCacheHeaders::class,
        ]);

        // Configure authentication redirects
        $middleware->redirectGuestsTo('/auth/login');

        // Apply maintenance mode check to web routes (except admin)
        // $middleware->web(append: [
        //     \App\Http\Middleware\CheckMaintenanceMode::class,
        // ]);

        // Stateful API for Sanctum (if needed for SPA)
        $middleware->statefulApi();

        $middleware->validateCsrfTokens(except: [
            'admin/live/page-visit',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Register custom error pages for 404 and 500 errors
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            // if ($response->getStatusCode() === 404) {
            //     return response()->view('errors.404', [], 404);
            // }

            // if ($response->getStatusCode() === 500) {
            //     return response()->view('errors.500', [], 500);
            // }

            // if ($response->getStatusCode() === 403) {
            //     return response()->view('errors.403', [], 403);
            // }

            return $response;
        });
    })->create();
