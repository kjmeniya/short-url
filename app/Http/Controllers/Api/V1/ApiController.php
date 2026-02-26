<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ApiController extends BaseApiController
{
    protected string $version = 'v1';

    /**
     * API Index - Show API information
     */
    public function index(): JsonResponse
    {
        return $this->successResponse([
            'name' => site_name() . ' API',
            'version' => $this->version,
            'current_version' => api_version(),
            'description' => 'RESTful API for ' . site_name(),
            'documentation' => url('/api/docs'),
            'base_url' => url('/api/v1'),
            'authentication' => [
                'type' => 'Bearer Token (Laravel Sanctum)',
                'header' => 'Authorization: Bearer {token}',
                'note' => 'Obtain token via /api/v1/auth/login endpoint',
            ],
            'rate_limiting' => [
                'enabled' => true,
                'note' => 'Rate limits are enforced via api.throttle middleware',
            ],
            'endpoints' => [
                'authentication' => [
                    'description' => 'Public authentication endpoints (no auth required)',
                    'routes' => [
                        [
                            'name' => 'Login',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/auth/login',
                            'auth_required' => false,
                            'description' => 'Authenticate user and receive access token',
                        ],
                        [
                            'name' => 'Register',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/auth/register',
                            'auth_required' => false,
                            'description' => 'Register a new user account',
                        ],
                        [
                            'name' => 'Forgot Password',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/auth/forgot-password',
                            'auth_required' => false,
                            'description' => 'Request password reset link via email',
                        ],
                        [
                            'name' => 'Reset Password',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/auth/reset-password',
                            'auth_required' => false,
                            'description' => 'Reset password using token from email',
                        ],
                        [
                            'name' => 'Verify Email',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/auth/verify-email',
                            'auth_required' => false,
                            'description' => 'Verify user email address',
                        ],
                        [
                            'name' => 'Verify Two-Factor Code',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/auth/two-factor/verify',
                            'auth_required' => false,
                            'description' => 'Verify 2FA code during login process',
                        ],
                        [
                            'name' => 'Send Two-Factor Code',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/auth/two-factor/send-code',
                            'auth_required' => false,
                            'description' => 'Request 2FA code via email',
                        ],
                        [
                            'name' => 'Logout',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/auth/logout',
                            'auth_required' => true,
                            'description' => 'Logout and invalidate current access token',
                        ],
                        [
                            'name' => 'Logout All Devices',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/auth/logout-all',
                            'auth_required' => true,
                            'description' => 'Logout from all other devices (keeps current session active)',
                        ],
                        [
                            'name' => 'Refresh Token',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/auth/refresh',
                            'auth_required' => true,
                            'description' => 'Refresh the current access token',
                        ],
                    ],
                ],
                'profile_management' => [
                    'description' => 'User profile management endpoints (auth required)',
                    'routes' => [
                        [
                            'name' => 'Get Profile',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/profile',
                            'auth_required' => true,
                            'description' => 'Get authenticated user profile information',
                        ],
                        [
                            'name' => 'Update Profile',
                            'method' => 'PUT',
                            'endpoint' => '/api/v1/profile',
                            'auth_required' => true,
                            'description' => 'Update authenticated user profile',
                        ],
                        [
                            'name' => 'Change Password',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/profile/change-password',
                            'auth_required' => true,
                            'description' => 'Change user password',
                        ],
                        [
                            'name' => 'Send Verification Email',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/profile/send-verification',
                            'auth_required' => true,
                            'description' => 'Resend email verification link',
                        ],
                        [
                            'name' => 'Logout All Devices',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/profile/logout-all',
                            'auth_required' => true,
                            'description' => 'Logout from all devices and invalidate all tokens',
                        ],
                    ],
                ],
                'two_factor_authentication' => [
                    'description' => 'Two-factor authentication management (auth required)',
                    'routes' => [
                        [
                            'name' => 'Get 2FA Status',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/profile/two-factor',
                            'auth_required' => true,
                            'description' => 'Get current 2FA status and configuration',
                        ],
                        [
                            'name' => 'Generate 2FA Secret',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/profile/two-factor/secret',
                            'auth_required' => true,
                            'description' => 'Generate new 2FA secret and QR code',
                        ],
                        [
                            'name' => 'Enable 2FA',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/profile/two-factor/enable',
                            'auth_required' => true,
                            'description' => 'Enable two-factor authentication',
                        ],
                        [
                            'name' => 'Disable 2FA',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/profile/two-factor/disable',
                            'auth_required' => true,
                            'description' => 'Disable two-factor authentication',
                        ],
                        [
                            'name' => 'Regenerate Recovery Codes',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/profile/two-factor/recovery-codes',
                            'auth_required' => true,
                            'description' => 'Generate new 2FA recovery codes',
                        ],
                    ],
                ],
                'user_management' => [
                    'description' => 'User management endpoints (auth required)',
                    'routes' => [
                        [
                            'name' => 'List Users',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/users',
                            'auth_required' => true,
                            'description' => 'Get paginated list of all users',
                        ],
                        [
                            'name' => 'Get User',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/users/{user}',
                            'auth_required' => true,
                            'description' => 'Get specific user details by ID',
                        ],
                        [
                            'name' => 'Create User',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/users',
                            'auth_required' => true,
                            'description' => 'Create a new user',
                        ],
                        [
                            'name' => 'Update User',
                            'method' => 'PUT',
                            'endpoint' => '/api/v1/users/{user}',
                            'auth_required' => true,
                            'description' => 'Update existing user information',
                        ],
                        [
                            'name' => 'Delete User',
                            'method' => 'DELETE',
                            'endpoint' => '/api/v1/users/{user}',
                            'auth_required' => true,
                            'description' => 'Delete a user account',
                        ],
                        [
                            'name' => 'Get User Profile',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/users/{user}/profile',
                            'auth_required' => true,
                            'description' => 'Get public profile information for a user',
                        ],
                    ],
                ],
                'settings' => [
                    'description' => 'Application settings endpoints',
                    'routes' => [
                        [
                            'name' => 'List Settings',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/settings',
                            'auth_required' => false,
                            'description' => 'Get all public application settings',
                        ],
                        [
                            'name' => 'Get Setting',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/settings/{key}',
                            'auth_required' => false,
                            'description' => 'Get specific setting value by key',
                        ],
                    ],
                ],
                'blogs' => [
                    'description' => 'Blog and content endpoints',
                    'routes' => [
                        [
                            'name' => 'List Blogs',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/blogs',
                            'auth_required' => false,
                            'description' => 'Get paginated list of published blog posts',
                        ],
                        [
                            'name' => 'Featured Blogs',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/blogs/featured',
                            'auth_required' => false,
                            'description' => 'Get list of featured blog posts',
                        ],
                        [
                            'name' => 'Search Blogs',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/blogs/search',
                            'auth_required' => false,
                            'description' => 'Search blog posts by keyword',
                        ],
                        [
                            'name' => 'Get Blog',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/blogs/{slug}',
                            'auth_required' => false,
                            'description' => 'Get specific blog post details by slug',
                        ],
                    ],
                ],
                'notifications' => [
                    'description' => 'Notification management endpoints (auth required)',
                    'routes' => [
                        [
                            'name' => 'List Notifications',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/notifications',
                            'auth_required' => true,
                            'description' => 'Get paginated list of user notifications',
                        ],
                        [
                            'name' => 'Recent Notifications',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/notifications/recent',
                            'auth_required' => true,
                            'description' => 'Get recent notifications for navbar/dropdown',
                        ],
                        [
                            'name' => 'Unread Count',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/notifications/unread-count',
                            'auth_required' => true,
                            'description' => 'Get count of unread notifications',
                        ],
                        [
                            'name' => 'Notification Stats',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/notifications/stats',
                            'auth_required' => true,
                            'description' => 'Get notification statistics (total, unread, read, today, this week)',
                        ],
                        [
                            'name' => 'Mark as Read',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/notifications/{id}/mark-read',
                            'auth_required' => true,
                            'description' => 'Mark a specific notification as read',
                        ],
                        [
                            'name' => 'Mark All as Read',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/notifications/mark-all-read',
                            'auth_required' => true,
                            'description' => 'Mark all notifications as read',
                        ],
                        [
                            'name' => 'Delete Notification',
                            'method' => 'DELETE',
                            'endpoint' => '/api/v1/notifications/{id}',
                            'auth_required' => true,
                            'description' => 'Delete a specific notification',
                        ],
                        [
                            'name' => 'Delete All Read',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/notifications/delete-all-read',
                            'auth_required' => true,
                            'description' => 'Delete all read notifications',
                        ],
                        [
                            'name' => 'Delete All',
                            'method' => 'POST',
                            'endpoint' => '/api/v1/notifications/delete-all',
                            'auth_required' => true,
                            'description' => 'Delete all notifications',
                        ],
                    ],
                ],
                'system' => [
                    'description' => 'System and utility endpoints',
                    'routes' => [
                        [
                            'name' => 'API Information',
                            'method' => 'GET',
                            'endpoint' => '/api/v1',
                            'auth_required' => false,
                            'description' => 'Get API version and available endpoints',
                        ],
                        [
                            'name' => 'Health Check',
                            'method' => 'GET',
                            'endpoint' => '/api/v1/health',
                            'auth_required' => false,
                            'description' => 'Check API health status and system availability',
                        ],
                    ],
                ],
            ],
        ], 'API v1 Information');
    }

    /**
     * API Health Check
     */
    public function health(): JsonResponse
    {
        $checks = [
            'api' => true,
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $allHealthy = !in_array(false, $checks, true);

        return $this->successResponse([
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
            'version' => $this->version,
        ], $allHealthy ? 'All systems operational' : 'Some systems are degraded');
    }

    /**
     * Check database connection
     */
    protected function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check cache connection
     */
    protected function checkCache(): bool
    {
        try {
            Cache::put('health_check', true, 10);
            return Cache::get('health_check') === true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
