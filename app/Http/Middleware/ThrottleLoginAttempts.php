<?php

namespace App\Http\Middleware;

use App\Services\SettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleLoginAttempts
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
        $email = $request->input('email');
        $ip = $request->ip();
        
        if (!$email) {
            return $next($request);
        }

        // Get settings
        $maxAttempts = max_login_attempts();
        $lockoutDuration = lockout_duration(); // in minutes

        $key = $this->throttleKey($email, $ip);
        
        // Check if too many attempts
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);
            
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$minutes} minutes."
            ])->withInput($request->only('email'));
        }

        $response = $next($request);

        // If login failed (check for validation errors or redirect back)
        if ($response->isRedirection() && $response->getTargetUrl() === $request->url()) {
            RateLimiter::hit($key, $lockoutDuration * 60);
        }

        return $response;
    }

    /**
     * Get the throttle key for the given request.
     */
    protected function throttleKey(string $email, string $ip): string
    {
        return 'login_attempts:' . strtolower($email) . '|' . $ip;
    }

    /**
     * Clear login attempts for a user.
     */
    public static function clearLoginAttempts(string $email, string $ip): void
    {
        $key = 'login_attempts:' . strtolower($email) . '|' . $ip;
        RateLimiter::clear($key);
    }
}

