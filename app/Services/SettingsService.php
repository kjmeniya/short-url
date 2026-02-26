<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SettingsService
{
    /**
     * Cache key for settings.
     */
    const CACHE_KEY = 'system_settings';

    /**
     * Cache duration in seconds (1 hour).
     */
    const CACHE_DURATION = 3600;

    /**
     * Get all settings from cache or database.
     */
    public function getAllSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_DURATION, function () {
            try {
                return Setting::active()
                    ->pluck('value', 'key')
                    ->toArray();
            } catch (\Exception $e) {
                Log::error('Failed to load settings from database: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get a specific setting value.
     */
    public function get(string $key, $default = null)
    {
        $settings = $this->getAllSettings();
        $value = $settings[$key] ?? null;

        // If no value found, try to get from defaults
        if ($value === null) {
            $defaults = $this->getDefaults();
            $value = $defaults[$key] ?? $default;
        }

        return $value;
    }

    /**
     * Get typed setting value.
     */
    public function getTyped(string $key, $default = null)
    {
        try {
            $setting = Setting::where('key', $key)->active()->first();

            if (!$setting) {
                return $default;
            }

            return $setting->typed_value ?? $default;
        } catch (\Exception $e) {
            Log::error('Failed to get typed setting: ' . $e->getMessage());
            return $default;
        }
    }

    /**
     * Set a setting value.
     */
    public function set(string $key, $value): bool
    {
        try {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                $setting->update(['value' => $value]);
                $this->clearCache();
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to set setting: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get public settings (accessible by non-admin users).
     */
    public function getPublicSettings(): array
    {
        return Cache::remember('public_settings', self::CACHE_DURATION, function () {
            try {
                return Setting::active()
                    ->public()
                    ->pluck('value', 'key')
                    ->toArray();
            } catch (\Exception $e) {
                Log::error('Failed to load public settings: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get settings by group.
     */
    public function getByGroup(string $group): array
    {
        $cacheKey = "settings_group_{$group}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($group) {
            try {
                return Setting::active()
                    ->byGroup($group)
                    ->orderBy('sort_order')
                    ->pluck('value', 'key')
                    ->toArray();
            } catch (\Exception $e) {
                Log::error("Failed to load settings for group {$group}: " . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Update multiple settings at once.
     */
    public function updateMultiple(array $settings): bool
    {
        try {
            foreach ($settings as $key => $value) {
                Setting::where('key', $key)->update(['value' => $value]);
            }

            $this->clearCache();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update multiple settings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear settings cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('public_settings');

        // Clear group caches
        Cache::forget('settings_groups');
        Cache::forget('settings_group_keys');

        $groups = Setting::getGroups();
        foreach (array_keys($groups) as $group) {
            Cache::forget("settings_group_{$group}");
        }
    }

    /**
     * Check if a setting exists.
     */
    public function exists(string $key): bool
    {
        try {
            return Setting::where('key', $key)->exists();
        } catch (\Exception $e) {
            Log::error('Failed to check setting existence: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get setting with metadata.
     */
    public function getWithMetadata(string $key): ?Setting
    {
        try {
            return Setting::where('key', $key)->active()->first();
        } catch (\Exception $e) {
            Log::error('Failed to get setting with metadata: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all settings grouped by their groups.
     */
    public function getAllGrouped(): array
    {
        return Cache::remember('settings_grouped', self::CACHE_DURATION, function () {
            try {
                return Setting::active()
                    ->orderBy('group')
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy('group')
                    ->toArray();
            } catch (\Exception $e) {
                Log::error('Failed to load grouped settings: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Validate setting value based on its type.
     */
    public function validateValue(Setting $setting, $value): bool
    {
        switch ($setting->type) {
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false;

            case 'number':
                return is_numeric($value);

            case 'boolean':
                return in_array($value, ['0', '1', 0, 1, true, false], true);

            case 'select':
                return $setting->options && array_key_exists($value, $setting->options);

            default:
                return true; // text, textarea, file types are always valid
        }
    }

    /**
     * Get default settings for initial setup.
     */
    public function getDefaults(): array
    {
        return [
            // General Settings
            'site_name' => 'ShortURL',
            'site_description' => 'ShortURL is a software development company.',
            'contact_email' => 'info@softdev.in',
            'timezone' => 'UTC',
            'default_date_format' => 'M d, Y',
            'default_datetime_format' => 'M d, Y g:i A',
            'site_favicon' => 'favicon.ico',
            'admin_logo_small_light' => 'build/images/logo-mini-dark.png',
            'admin_logo_small_dark' => 'build/images/logo-mini-light.png',
            'admin_logo_light' => 'build/images/logo-dark.png',
            'admin_logo_dark' => 'build/images/logo-light.png',
            'frontend_logo_small_light' => 'build/images/logo-mini-dark.png',
            'frontend_logo_small_dark' => 'build/images/logo-mini-light.png',
            'frontend_logo_light' => 'build/images/logo-dark.png',
            'frontend_logo_dark' => 'build/images/logo-light.png',
            'app_url' => env('APP_URL', 'https://softdev.in'),

            // Email Settings
            'mail_driver' => 'smtp',
            'mail_from_name' => 'ShortURL',
            'mail_from_address' => 'info@softdev.in',
            'mail_reply_to' => 'info@softdev.in',
            'email_enabled' => '1',
            'email_queue_enabled' => '1',
            'email_retry_attempts' => '3',

            // SMTP Settings
            'smtp_host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'smtp_port' => env('MAIL_PORT', '465'),
            'smtp_username' => env('MAIL_USERNAME', 'info@softdev.in'),
            'smtp_password' => env('MAIL_PASSWORD', ''),
            'smtp_encryption' => env('MAIL_ENCRYPTION', 'ssl'),
            'smtp_timeout' => '30',
            'smtp_local_domain' => env('MAIL_EHLO_DOMAIN', ''),
            'smtp_verify_peer' => '1',
            'smtp_ssl_mode' => '1',

            // Storage Settings
            'asset_url' => env('ASSET_URL', ''),
            'upload_path' => 'uploads',
            'max_upload_size' => '10',
            'allowed_file_types' => 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
            'storage_driver' => 'local',
            'storage_public_url' => '/storage',

            // Security Settings
            'session_timeout' => '1440',
            'max_login_attempts' => '5',
            'lockout_duration' => '15',
            'two_factor_auth' => '0',
            'password_min_length' => '8',
            'csrf_token_lifetime' => '1440',
            'password_reset_expiry' => '60',
            'force_password_change' => '0',
            'login_ip_restriction' => '',

            // Appearance Settings
            'primary_color' => '#245dac',
            'secondary_color' => '#6c757d',
            'success_color' => '#198754',
            'danger_color' => '#dc3545',
            'warning_color' => '#ffc107',
            'info_color' => '#0dcaf0',
            'light_color' => '#e9ecef',
            'dark_color' => '#212529',
            'font_family' => 'Roboto, sans-serif',

            // Admin Settings (admin_ prefix)
            'admin_items_per_page' => '25',
            'admin_primary_color' => '#245dac',
            'admin_default_theme' => 'light',

            // Frontend Settings (front_ prefix)
            'front_items_per_page' => '10',
            'front_primary_color' => '#245dac',
            'front_show_breadcrumbs' => '1',
            'front_enable_search' => '1',
            'front_show_sidebar' => '1',
            'front_enable_comments' => '1',
            'frontend_default_theme' => 'light',

            // Legacy settings (for backward compatibility)
            'items_per_page' => '10',

            // System Settings
            'maintenance_mode' => '0',
            'maintenance_message' => 'We are currently performing scheduled maintenance. Please check back soon.',
            'debug_mode' => env('APP_DEBUG', false) ? '1' : '0',
            'app_debug' => env('APP_DEBUG', false) ? '1' : '0',
            'app_env' => env('APP_ENV', 'production'),
            'cache_enabled' => '1',
            'log_level' => 'error',
            'api_version' => 'v1',

            // Notification Settings
            'notifications_enabled' => '1',
            'email_notifications' => '1',
            'admin_notification_email' => 'kalpeshmeniya96@gmail.com',
            'notification_frequency' => 'daily',

            // Google Authentication Settings
            'google_auth_enabled' => '0',
            'google_client_id' => '',
            'google_client_secret' => '',

            // API Settings
            'api_enabled' => '0',
            'api_guest_rate_limit' => '100',
            'api_user_rate_limit' => '1000',
            'ip_api_url' => 'https://ip-api.in/api/v1/ip/',
            'ip_api_token' => '',
        ];
    }

    /**
     * Reset settings to default values.
     */
    public function resetToDefaults(?array $keys = null, ?string $group = null): int
    {
        $defaults = $this->getDefaults();
        $resetCount = 0;

        if ($group) {
            // Reset by group
            $settings = Setting::where('group', $group)->get();
            foreach ($settings as $setting) {
                if (isset($defaults[$setting->key])) {
                    $setting->update(['value' => $defaults[$setting->key]]);
                    $resetCount++;
                }
            }
        } elseif ($keys) {
            // Reset specific keys
            foreach ($keys as $key) {
                if (isset($defaults[$key])) {
                    Setting::where('key', $key)->update(['value' => $defaults[$key]]);
                    $resetCount++;
                }
            }
        } else {
            // Reset all settings
            foreach ($defaults as $key => $value) {
                Setting::where('key', $key)->update(['value' => $value]);
                $resetCount++;
            }
        }

        $this->clearCache();
        return $resetCount;
    }

    /**
     * Get admin-specific settings.
     */
    public function getAdminSettings(): array
    {
        $allSettings = $this->getAllSettings();
        $adminSettings = [];

        foreach ($allSettings as $key => $value) {
            if (str_starts_with($key, 'admin_')) {
                $adminSettings[$key] = $value;
            }
        }

        return $adminSettings;
    }

    /**
     * Get frontend-specific settings.
     */
    public function getFrontendSettings(): array
    {
        $allSettings = $this->getAllSettings();
        $frontendSettings = [];

        foreach ($allSettings as $key => $value) {
            if (str_starts_with($key, 'front_')) {
                $frontendSettings[$key] = $value;
            }
        }

        return $frontendSettings;
    }

    /**
     * Get dynamic setting groups with caching.
     */
    public function getDynamicGroups(): array
    {
        return Cache::remember('settings_groups', self::CACHE_DURATION, function () {
            try {
                return Setting::getDynamicGroups();
            } catch (\Exception $e) {
                Log::error('Failed to load dynamic settings groups: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Clear all settings-related caches (used by command).
     */
    public function clearAllCaches(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('public_settings');
        Cache::forget('settings_groups');
        Cache::forget('settings_group_keys');

        // Clear individual group caches
        $groups = Setting::getGroups();
        foreach (array_keys($groups) as $group) {
            Cache::forget("settings_group_{$group}");
        }
    }
}
