<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if maintenance mode is enabled in settings
        if (maintenance_mode()) {
            // Allow admin users to bypass maintenance mode
            if ($request->user() && $request->user()->role && $request->user()->role->name === 'super_admin') {
                return $next($request);
            }

            // Get maintenance message from settings
            $message = maintenance_message();

            // Return maintenance mode view
            return response()->view('errors.maintenance', [
                'message' => $message
            ], 503);
        }

        return $next($request);
    }
}

