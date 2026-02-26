<?php

namespace App\Services;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginTrackingService
{
    protected UserAgentParser $agent;

    public function __construct()
    {
        $this->agent = new UserAgentParser();
    }

    /**
     * Log a login attempt.
     */
    public function logLoginAttempt(
        string $email,
        string $status = 'failed',
        ?User $user = null,
        ?string $failureReason = null,
        array $additionalData = []
    ): LoginLog {
        $request = request();

        // Parse user agent for device information
        $this->agent->setUserAgent($request->header('User-Agent'));

        $loginData = [
            'user_id' => $user?->id,
            'email' => $email,
            'name' => $user?->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'device_type' => $this->agent->getDeviceType(),
            'browser' => $this->agent->browser(),
            'platform' => $this->agent->platform(),
            'status' => $status,
            'type' => 'login',
            'failure_reason' => $failureReason,
            'session_id' => session()->getId(),
            'is_suspicious' => $this->detectSuspiciousActivity($email, $request->ip()),
            'metadata' => array_merge([
                'user_agent_string' => $request->header('User-Agent'),
                'referer' => $request->header('Referer'),
                'accept_language' => $request->header('Accept-Language'),
            ], $additionalData),
        ];

        if ($status === 'success') {
            $loginData['login_at'] = now();
        }

        // Try to get location information (you can integrate with IP geolocation service)
        $locationData = $this->getLocationData($request->ip());
        $loginData = array_merge($loginData, $locationData);

        return LoginLog::create($loginData);
    }

    /**
     * Log a successful login.
     */
    public function logSuccessfulLogin(User $user, string $email): LoginLog
    {
        return $this->logLoginAttempt($email, 'success', $user);
    }

    /**
     * Log a failed login.
     */
    public function logFailedLogin(string $email, string $reason = 'Invalid credentials', ?User $user = null): LoginLog
    {
        return $this->logLoginAttempt($email, 'failed', $user, $reason);
    }

    /**
     * Log a blocked login.
     */
    public function logBlockedLogin(string $email, string $reason = 'Account blocked', ?User $user = null): LoginLog
    {
        return $this->logLoginAttempt($email, 'blocked', $user, $reason);
    }

    /**
     * Log an account locked event.
     */
    public function logAccountLocked(User $user, string $reason = 'Too many failed attempts'): LoginLog
    {
        $request = request();

        // Parse user agent for device information
        $this->agent->setUserAgent($request->header('User-Agent'));

        $loginData = [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'device_type' => $this->agent->getDeviceType(),
            'browser' => $this->agent->browser(),
            'platform' => $this->agent->platform(),
            'status' => 'locked',
            'type' => 'account_locked',
            'failure_reason' => $reason,
            'session_id' => session()->getId(),
            'is_suspicious' => $this->detectSuspiciousActivity($user->email, $request->ip()),
            'metadata' => [
                'user_agent_string' => $request->header('User-Agent'),
                'referer' => $request->header('Referer'),
                'accept_language' => $request->header('Accept-Language'),
            ],
        ];

        // Try to get location information
        $locationData = $this->getLocationData($request->ip());
        $loginData = array_merge($loginData, $locationData);

        return LoginLog::create($loginData);
    }

    /**
     * Log a logout.
     */
    public function logLogout(?User $user = null): ?LoginLog
    {
        if (!$user) {
            $user = Auth::user();
        }

        if (!$user) {
            return null;
        }

        // Find the most recent successful login for this user
        $lastLogin = LoginLog::where('user_id', $user->id)
            ->where('status', 'success')
            ->where('type', 'login')
            ->whereNull('logout_at')
            ->latest('login_at')
            ->first();

        if ($lastLogin) {
            $sessionDuration = $lastLogin->login_at ?
                now()->diffInMinutes($lastLogin->login_at) : null;

            $lastLogin->update([
                'logout_at' => now(),
                'session_duration' => $sessionDuration,
            ]);

            // Also create a separate logout log entry
            return $this->createLogoutEntry($user, $sessionDuration);
        }

        // Create a new logout log if no matching login found
        return $this->createLogoutEntry($user);
    }

    /**
     * Create a logout log entry.
     */
    protected function createLogoutEntry(User $user, ?int $sessionDuration = null): LoginLog
    {
        $request = request();

        // Parse user agent for device information
        $this->agent->setUserAgent($request->header('User-Agent'));

        $logoutData = [
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'device_type' => $this->agent->getDeviceType(),
            'browser' => $this->agent->browser(),
            'platform' => $this->agent->platform(),
            'status' => 'success',
            'type' => 'logout',
            'logout_at' => now(),
            'session_duration' => $sessionDuration,
            'session_id' => session()->getId(),
            'is_suspicious' => false,
            'metadata' => [
                'user_agent_string' => $request->header('User-Agent'),
                'referer' => $request->header('Referer'),
                'accept_language' => $request->header('Accept-Language'),
            ],
        ];

        // Try to get location information
        $locationData = $this->getLocationData($request->ip());
        $logoutData = array_merge($logoutData, $locationData);

        return LoginLog::create($logoutData);
    }



    /**
     * Detect suspicious login activity.
     */
    protected function detectSuspiciousActivity(string $email, string $ipAddress): bool
    {
        // Check for multiple failed attempts from same IP in last hour
        $recentFailures = LoginLog::where('ip_address', $ipAddress)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentFailures >= 5) {
            return true;
        }

        // Check for login attempts from different countries in short time
        $recentLogins = LoginLog::where('email', $email)
            ->where('created_at', '>=', now()->subHours(2))
            ->whereNotNull('country')
            ->distinct('country')
            ->count();

        if ($recentLogins >= 3) {
            return true;
        }

        // Check for unusual user agent patterns
        $userAgent = request()->header('User-Agent');
        if (empty($userAgent) || strlen($userAgent) < 10) {
            return true;
        }

        return false;
    }

    /**
     * Get location data from IP address.
     */
    protected function getLocationData(string $ipAddress): array
    {
        // Skip for local IPs
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return [
                'location' => 'Localhost',
                'country' => 'Local',
                'city' => 'Local',
            ];
        }

        try {
            $baseUrl = ip_api_url();
            // Ensure trailing slash if not present, but handling if user enters full path without slash or with
            // Since we are appending the IP, we should ensure the base URL ends with / or the IP is appended correctly.
            // ip-api.in docs show: https://ip-api.in/api/v1/ip/{ip}

            $url = rtrim($baseUrl, '/') . '/' . $ipAddress;
            $token = ip_api_token();

            $request = \Illuminate\Support\Facades\Http::timeout(3);

            if ($token) {
                $request->withToken($token);
            }

            $response = $request->get($url);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['success']) && $data['success'] && isset($data['data'])) {
                    $geo = $data['data'];

                    $city = $geo['city'] ?? null;
                    $country = $geo['country'] ?? null;

                    $locationParts = [];
                    if ($city) $locationParts[] = $city;
                    if ($country) $locationParts[] = $country;

                    return [
                        'location' => implode(', ', $locationParts),
                        'country' => $country,
                        'city' => $city,
                        // Store additional useful data if the table supports it, 
                        // otherwise these keys will just be ignored by array_merge if not in fillable/table
                        'latitude' => $geo['latitude'] ?? null,
                        'longitude' => $geo['longitude'] ?? null,
                        'timezone' => $geo['timezone'] ?? null,
                        'organization' => $geo['organization'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silently fail and return empty data to avoid blocking login
            Log::warning('IP Geolocation failed: ' . $e->getMessage());
        }

        return [
            'location' => null,
            'country' => null,
            'city' => null,
        ];
    }

    /**
     * Get login statistics.
     */
    public function getLoginStats(array $filters = []): array
    {
        $baseQuery = LoginLog::query();

        // Apply filters to base query
        if (isset($filters['date_from'])) {
            $baseQuery->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $baseQuery->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['type'])) {
            $baseQuery->where('type', $filters['type']);
        }

        // Clone the base query for different counts to avoid conflicts
        $total = (clone $baseQuery)->count();
        $successful = (clone $baseQuery)->where('status', 'success')->count();
        $failed = (clone $baseQuery)->whereIn('status', ['failed', 'blocked', 'locked'])->count();
        $suspicious = (clone $baseQuery)->where('is_suspicious', true)->count();
        $uniqueUsers = (clone $baseQuery)->whereNotNull('user_id')->distinct('user_id')->count();

        return [
            'total' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'suspicious' => $suspicious,
            'unique_users' => $uniqueUsers,
            'success_rate' => $total > 0 ? round($successful / $total * 100, 2) : 0,
        ];
    }

    /**
     * Get recent login activity for a user.
     */
    public function getUserRecentActivity(User $user, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return LoginLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get suspicious login attempts.
     */
    public function getSuspiciousLogins(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return LoginLog::where('is_suspicious', true)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
