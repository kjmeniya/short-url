<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiThrottleMiddleware
{
    use \App\Traits\ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if API is enabled
        if (!$this->isApiEnabled()) {
            return $this->serviceUnavailableResponse('API is currently disabled.');
        }

        // Check if maintenance mode is enabled
        if ($this->isMaintenanceMode()) {
            return $this->serviceUnavailableResponse(maintenance_message());
        }

        // Verify Application Token (Package Name)
        if (!$this->isValidApplicationToken($request)) {
            return $this->unauthorizedResponse('Invalid application token. Please provide a valid application token.');
        }

        // Get rate limit from settings based on user authentication
        $maxAttempts = $this->getApiRateLimit($request);
        $decayMinutes = 1440; // 24 hours in minutes (daily limit)

        // Create unique key for rate limiting
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);

            return $this->tooManyRequestsResponse(
                'Daily rate limit exceeded. Please try again later.',
                $retryAfter,
                $maxAttempts,
                $request->user() ? 'authenticated' : 'guest'
            )->withHeaders([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => now()->addMinutes($decayMinutes)->timestamp,
            ]);
        }

        RateLimiter::hit($key, $decayMinutes); // 24 hour window

        $response = $next($request);

        // Add rate limit headers
        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $maxAttempts),
            'X-RateLimit-Reset' => now()->addMinutes($decayMinutes)->timestamp,
        ]);
    }

    /**
     * Check if API is enabled from settings
     */
    protected function isApiEnabled(): bool
    {
        return api_enabled();
    }

    /**
     * Check if maintenance mode is enabled from settings
     */
    protected function isMaintenanceMode(): bool
    {
        return maintenance_mode();
    }

    /**
     * Get API rate limit from settings based on authentication
     */
    protected function getApiRateLimit(Request $request): int
    {
        // If user is authenticated, use user rate limit
        if ($request->user()) {
            return api_user_rate_limit();
        }

        // Otherwise use guest rate limit
        return api_guest_rate_limit();
    }

    /**
     * Resolve request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        if ($user = $request->user()) {
            return 'api_throttle:user:' . $user->id;
        }

        return 'api_throttle:ip:' . $request->ip();
    }

    /**
     * Check if application token is valid
     */
    protected function isValidApplicationToken(Request $request): bool
    {
        $token = $request->header('X-Application-Token') ?? $request->input('application_token');

        if (!$token) {
            return false;
        }

        $androidToken = \App\Models\Setting::get('android_application_token');
        $iosToken = \App\Models\Setting::get('ios_application_token');

        // Allow if matching either configured package name
        // If settings are not configured, we might want to fail safe or allow dev mode
        // Here we assume strict validation if settings exist, strict fail if not matches

        return $token === $androidToken || $token === $iosToken;
    }
}
