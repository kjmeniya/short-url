<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Services\EmailService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GoogleAuthService
{
    protected $settingsService;
    protected $emailService;
    protected $notificationService;

    public function __construct(SettingsService $settingsService, EmailService $emailService, NotificationService $notificationService)
    {
        $this->settingsService = $settingsService;
        $this->emailService = $emailService;
        $this->notificationService = $notificationService;
    }

    /**
     * Check if Google authentication is enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) $this->settingsService->get('google_auth_enabled', false);
    }

    /**
     * Get Google OAuth URL for authentication.
     */
    public function getAuthUrl(?string $state = null): string
    {
        $clientId = $this->settingsService->get('google_client_id');
        $redirectUri = route('auth.google.callback');
        
        $params = [
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'openid email profile',
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
        ];

        if ($state) {
            $params['state'] = $state;
        }

        return 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token.
     */
    public function getAccessToken(string $code): ?array
    {
        try {
            $clientId = $this->settingsService->get('google_client_id');
            $clientSecret = $this->settingsService->get('google_client_secret');
            $redirectUri = route('auth.google.callback');

            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google OAuth token exchange failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Google OAuth token exchange error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user information from Google.
     */
    public function getUserInfo(string $accessToken): ?array
    {
        try {
            $response = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Google user info fetch failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Google user info fetch error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Download and store Google avatar image.
     */
    private function downloadGoogleAvatar(string $avatarUrl): ?string
    {
        try {
            // Add timeout and user agent to make the request more likely to succeed
            $response = Http::timeout(10)
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
                ->get($avatarUrl);

            if (!$response->successful()) {
                Log::warning('Google avatar download failed - HTTP error', [
                    'url' => $avatarUrl,
                    'status' => $response->status()
                ]);
                return null;
            }

            $imageData = $response->body();

            // Validate that we got image data
            if (empty($imageData) || strlen($imageData) < 100) {
                Log::warning('Google avatar download failed - invalid image data');
                return null;
            }

            $extension = 'jpg'; // Google avatars are typically JPG
            $filename = 'google_avatar_' . Str::random(20) . '.' . $extension;
            $path = 'avatars/' . $filename;

            if (Storage::disk('public')->put($path, $imageData)) {
                Log::info('Google avatar downloaded successfully', ['path' => $path]);
                return $path;
            }

            Log::warning('Google avatar download failed - storage error');
            return null;
        } catch (\Exception $e) {
            Log::error('Google avatar download error: ' . $e->getMessage(), [
                'avatar_url' => $avatarUrl,
                'exception' => $e
            ]);
            return null;
        }
    }

    /**
     * Find or create user from Google data.
     */
    public function findOrCreateUser(array $googleUser): ?User
    {
        try {
            Log::info('Google user creation/update started', ['email' => $googleUser['email']]);

            // Check if user exists by email
            $user = User::where('email', $googleUser['email'])->first();

            // Download avatar if available (but don't let it fail the user creation)
            $avatarPath = null;
            if (!empty($googleUser['picture'])) {
                try {
                    Log::info('Attempting to download Google avatar', ['url' => $googleUser['picture']]);
                    $avatarPath = $this->downloadGoogleAvatar($googleUser['picture']);
                    if ($avatarPath) {
                        Log::info('Google avatar downloaded successfully', ['path' => $avatarPath]);
                    } else {
                        Log::warning('Failed to download Google avatar, continuing without avatar');
                    }
                } catch (\Exception $e) {
                    Log::error('Avatar download failed, continuing without avatar: ' . $e->getMessage());
                    $avatarPath = null;
                }
            }

            if ($user) {
                // Update existing user info
                $updateData = [
                    'name' => $googleUser['name'] ?? $user->name,
                    'google_id' => $googleUser['id'],
                    'is_google_user' => true,
                ];

                // Only update avatar if we successfully downloaded one
                if ($avatarPath) {
                    // Delete old avatar if it exists and is a local file
                    if ($user->avatar && !filter_var($user->avatar, FILTER_VALIDATE_URL)) {
                        Storage::disk('public')->delete($user->avatar);
                    }
                    $updateData['avatar'] = $avatarPath;
                }

                $user->update($updateData);
                Log::info('Existing Google user updated successfully', ['user_id' => $user->id, 'email' => $user->email]);

                // Send welcome email if this is the first time linking Google account
                if (!$user->is_google_user) {
                    $this->emailService->sendWelcomeEmail($user->email, $user->name);
                    Log::info('Welcome email sent to existing user linking Google account', ['user_id' => $user->id]);
                }

                return $user;
            }

            // Create new user
            $defaultRole = Role::where('name', 'user')->first();

            // Prepare user data
            $userData = [
                'name' => $googleUser['name'] ?? 'Google User',
                'email' => $googleUser['email'],
                'password' => bcrypt(Str::random(32)), // Random password
                'google_id' => $googleUser['id'],
                'is_google_user' => true,
                'role_id' => $defaultRole ? $defaultRole->id : 3, // Fallback to role ID 3 if not found
                'is_active' => true,
                'email_verified_at' => now(),
            ];

            // Only add avatar if we successfully downloaded one
            if ($avatarPath) {
                $userData['avatar'] = $avatarPath;
            }

            $user = User::create($userData);

            Log::info('Google user created successfully', ['user_id' => $user->id, 'email' => $user->email]);

            // Send welcome email to new Google user
            $this->emailService->sendWelcomeEmail($user->email, $user->name);
            Log::info('Welcome email sent to new Google user', ['user_id' => $user->id]);

            // Send notification to admins about new Google user signup
            $this->notificationService->sendToAdmins(
                'google_user_registered',
                'New Google User Registered',
                "New user '{$user->name}' has registered via Google authentication",
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'registration_method' => 'Google OAuth',
                    'url' => route('admin.users.show', $user->id)
                ]
            );
            Log::info('Admin notification sent for new Google user', ['user_id' => $user->id]);

            return $user;
        } catch (\Exception $e) {
            Log::error('Google user creation error: ' . $e->getMessage(), [
                'exception' => $e,
                'google_user_data' => $googleUser,
                'stack_trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Validate Google authentication configuration.
     */
    public function validateConfig(): array
    {
        $errors = [];

        if (!$this->settingsService->get('google_client_id')) {
            $errors[] = 'Google Client ID is required';
        }

        if (!$this->settingsService->get('google_client_secret')) {
            $errors[] = 'Google Client Secret is required';
        }

        return $errors;
    }
}
