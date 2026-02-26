<?php

function active_class($path, $active = 'active')
{
    return request()->is(...(array)$path) ? $active : '';
}



use App\Services\SettingsService;

if (!function_exists('setting')) {
    /**
     * Get a setting value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        return app(SettingsService::class)->get($key, $default);
    }
}



if (!function_exists('setting_options')) {
    /**
     * Get setting options from database.
     *
     * @param string $key
     * @return array
     */
    function setting_options(string $key): array
    {
        try {
            $setting = \App\Models\Setting::where('key', $key)->first();

            if (!$setting || !$setting->options) {
                return [];
            }

            // Handle different option formats
            if (is_array($setting->options)) {
                return $setting->options;
            } elseif (is_string($setting->options)) {
                // Try to decode JSON
                $decoded = json_decode($setting->options, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }

            return [];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error retrieving setting options for '{$key}': " . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('admin_items_per_page')) {
    /**
     * Get admin items per page setting value.
     *
     * @return int
     */
    function admin_items_per_page(): int
    {
        return (int) setting('admin_items_per_page', 25);
    }
}

// Helper functions for commonly used settings
if (!function_exists('site_name')) {
    /**
     * Get site name setting.
     *
     * @return string
     */
    function site_name(): string
    {
        return setting('site_name', config('app.name'));
    }
}

if (!function_exists('site_description')) {
    /**
     * Get site description setting.
     *
     * @return string
     */
    function site_description(): string
    {
        return setting('site_description', 'Professional policy generator for creating privacy policies, terms and conditions, disclaimers, and cookie policies.');
    }
}

if (!function_exists('contact_email')) {
    /**
     * Get contact email setting.
     *
     * @return string
     */
    function contact_email(): string
    {
        return setting('contact_email', 'info@softdev.in');
    }
}

if (!function_exists('app_url')) {
    /**
     * Get application URL setting.
     *
     * @return string
     */
    function app_url(): string
    {
        return setting('app_url', config('app.url'));
    }
}

if (!function_exists('timezone_setting')) {
    /**
     * Get timezone setting.
     *
     * @return string
     */
    function timezone_setting(): string
    {
        return setting('timezone', 'UTC');
    }
}

if (!function_exists('date_format_setting')) {
    /**
     * Get date format setting.
     *
     * @return string
     */
    function date_format_setting(): string
    {
        return setting('date_format', 'M d, Y');
    }
}

if (!function_exists('time_format_setting')) {
    /**
     * Get time format setting.
     *
     * @return string
     */
    function time_format_setting(): string
    {
        return setting('time_format', 'g:i A');
    }
}

if (!function_exists('datetime_format_setting')) {
    /**
     * Get datetime format setting.
     *
     * @return string
     */
    function datetime_format_setting(): string
    {
        return setting('default_datetime_format', 'M d, Y g:i A');
    }
}

// ==========================================
// System Settings Helpers
// ==========================================

if (!function_exists('app_debug')) {
    /**
     * Check if debug mode is enabled.
     *
     * @return bool
     */
    function app_debug(): bool
    {
        $debug = setting('app_debug', '0');
        return $debug === '1' || $debug === 1 || $debug === true;
    }
}

if (!function_exists('app_env')) {
    /**
     * Get application environment.
     *
     * @return string
     */
    function app_env(): string
    {
        return setting('app_env', 'production');
    }
}

if (!function_exists('cache_enabled')) {
    /**
     * Check if cache is enabled.
     *
     * @return bool
     */
    function cache_enabled(): bool
    {
        $enabled = setting('cache_enabled', '1');
        return $enabled === '1' || $enabled === 1 || $enabled === true;
    }
}

if (!function_exists('log_level')) {
    /**
     * Get log level setting.
     *
     * @return string
     */
    function log_level(): string
    {
        return setting('log_level', 'error');
    }
}

if (!function_exists('maintenance_mode')) {
    /**
     * Check if maintenance mode is enabled.
     *
     * @return bool
     */
    function maintenance_mode(): bool
    {
        $mode = setting('maintenance_mode', '0');
        return $mode === '1' || $mode === 1 || $mode === true;
    }
}

if (!function_exists('maintenance_message')) {
    /**
     * Get maintenance mode message.
     *
     * @return string
     */
    function maintenance_message(): string
    {
        return setting('maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.');
    }
}

if (!function_exists('notifications_enabled')) {
    /**
     * Check if notifications are enabled.
     *
     * @return bool
     */
    function notifications_enabled(): bool
    {
        return (bool) setting('notifications_enabled', true);
    }
}

// ==========================================
// SMTP / Mail Settings Helpers
// ==========================================

if (!function_exists('mail_driver')) {
    /**
     * Get mail driver setting.
     *
     * @return string
     */
    function mail_driver(): string
    {
        return setting('mail_driver', 'smtp');
    }
}

if (!function_exists('smtp_host')) {
    /**
     * Get SMTP host setting.
     *
     * @return string
     */
    function smtp_host(): string
    {
        return setting('smtp_host', 'smtp.mailgun.org');
    }
}

if (!function_exists('smtp_port')) {
    /**
     * Get SMTP port setting.
     *
     * @return int
     */
    function smtp_port(): int
    {
        return (int) setting('smtp_port', 587);
    }
}

if (!function_exists('smtp_username')) {
    /**
     * Get SMTP username setting.
     *
     * @return string
     */
    function smtp_username(): string
    {
        return setting('smtp_username', '');
    }
}

if (!function_exists('smtp_password')) {
    /**
     * Get SMTP password setting.
     *
     * @return string
     */
    function smtp_password(): string
    {
        return setting('smtp_password', '');
    }
}

if (!function_exists('smtp_encryption')) {
    /**
     * Get SMTP encryption setting.
     *
     * @return string
     */
    function smtp_encryption(): string
    {
        return setting('smtp_encryption', 'tls');
    }
}

if (!function_exists('smtp_timeout')) {
    /**
     * Get SMTP timeout setting in seconds.
     *
     * @return int
     */
    function smtp_timeout(): int
    {
        return (int) setting('smtp_timeout', 30);
    }
}

if (!function_exists('smtp_ssl_mode')) {
    /**
     * Check if SMTP SSL verification is enabled.
     *
     * @return bool
     */
    function smtp_ssl_mode(): bool
    {
        $mode = setting('smtp_ssl_mode', '1');
        return $mode === '1' || $mode === 1 || $mode === true;
    }
}

if (!function_exists('mail_from_name')) {
    /**
     * Get mail from name setting.
     *
     * @return string
     */
    function mail_from_name(): string
    {
        return setting('mail_from_name', config('app.name'));
    }
}

if (!function_exists('mail_from_address')) {
    /**
     * Get mail from address setting.
     *
     * @return string
     */
    function mail_from_address(): string
    {
        return setting('mail_from_address', 'hello@example.com');
    }
}

if (!function_exists('mail_reply_to')) {
    /**
     * Get mail reply-to address setting.
     *
     * @return string
     */
    function mail_reply_to(): string
    {
        return setting('mail_reply_to', '');
    }
}

// ==========================================
// Storage Settings Helpers
// ==========================================

if (!function_exists('storage_driver')) {
    /**
     * Get storage driver setting.
     *
     * @return string
     */
    function storage_driver(): string
    {
        return setting('storage_driver', 'local');
    }
}

if (!function_exists('asset_url_setting')) {
    /**
     * Get asset URL setting.
     *
     * @return string
     */
    function asset_url_setting(): string
    {
        return setting('asset_url', '');
    }
}

if (!function_exists('upload_path')) {
    /**
     * Get upload path setting.
     *
     * @return string
     */
    function upload_path(): string
    {
        return setting('upload_path', 'uploads');
    }
}

if (!function_exists('max_upload_size')) {
    /**
     * Get max upload size in MB.
     *
     * @return int
     */
    function max_upload_size(): int
    {
        return (int) setting('max_upload_size', 10);
    }
}

if (!function_exists('allowed_file_types')) {
    /**
     * Get allowed file types as array.
     *
     * @return array
     */
    function allowed_file_types(): array
    {
        $types = setting('allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx');
        return array_map('trim', explode(',', $types));
    }
}

if (!function_exists('storage_public_url')) {
    /**
     * Get storage public URL setting.
     *
     * @return string
     */
    function storage_public_url(): string
    {
        return setting('storage_public_url', '/storage');
    }
}

if (!function_exists('email_notifications_enabled')) {
    /**
     * Check if email notifications are enabled.
     *
     * @return bool
     */
    function email_notifications_enabled(): bool
    {
        return (bool) setting('email_notifications', true);
    }
}

if (!function_exists('email_queue_enabled')) {
    /**
     * Check if email queue is enabled.
     *
     * @return bool
     */
    function email_queue_enabled(): bool
    {
        $enabled = setting('email_queue_enabled', '1');
        return $enabled === '1' || $enabled === 1 || $enabled === true;
    }
}

if (!function_exists('admin_notification_email')) {
    /**
     * Get admin notification email address.
     *
     * @return string
     */
    function admin_notification_email(): string
    {
        return setting('admin_notification_email', 'kalpeshmeniya96@gmail.com');
    }
}

if (!function_exists('notification_frequency')) {
    /**
     * Get notification frequency setting.
     *
     * @return string
     */
    function notification_frequency(): string
    {
        return setting('notification_frequency', 'instant');
    }
}

if (!function_exists('password_min_length')) {
    /**
     * Get minimum password length setting.
     *
     * @return int
     */
    function password_min_length(): int
    {
        return (int) setting('password_min_length', 8);
    }
}

if (!function_exists('max_login_attempts')) {
    /**
     * Get maximum login attempts setting.
     *
     * @return int
     */
    function max_login_attempts(): int
    {
        return (int) setting('max_login_attempts', 5);
    }
}

if (!function_exists('lockout_duration')) {
    /**
     * Get lockout duration setting in minutes.
     *
     * @return int
     */
    function lockout_duration(): int
    {
        return (int) setting('lockout_duration', 15);
    }
}

if (!function_exists('session_timeout')) {
    /**
     * Get session timeout setting in minutes.
     *
     * @return int
     */
    function session_timeout(): int
    {
        return (int) setting('session_timeout', 1440);
    }
}

if (!function_exists('two_factor_auth_enabled')) {
    /**
     * Check if two-factor authentication is enabled globally.
     *
     * @return bool
     */
    function two_factor_auth_enabled(): bool
    {
        $enabled = setting('two_factor_auth', '0');
        return $enabled === '1' || $enabled === 1 || $enabled === true;
    }
}

if (!function_exists('csrf_token_lifetime')) {
    /**
     * Get CSRF token lifetime setting in minutes.
     *
     * @return int
     */
    function csrf_token_lifetime(): int
    {
        return (int) setting('csrf_token_lifetime', 120);
    }
}

if (!function_exists('password_reset_expiry')) {
    /**
     * Get password reset token expiry in minutes.
     *
     * @return int
     */
    function password_reset_expiry(): int
    {
        return (int) setting('password_reset_expiry', 60);
    }
}

if (!function_exists('force_password_change_days')) {
    /**
     * Get force password change days setting (0 = disabled).
     *
     * @return int
     */
    function force_password_change_days(): int
    {
        return (int) setting('force_password_change', 0);
    }
}

if (!function_exists('login_ip_restriction')) {
    /**
     * Get login IP restriction list.
     *
     * @return array
     */
    function login_ip_restriction(): array
    {
        $ips = setting('login_ip_restriction', '');
        if (empty($ips)) {
            return [];
        }
        return array_map('trim', explode(',', $ips));
    }
}

if (!function_exists('api_version')) {
    /**
     * Get current API version setting.
     *
     * @return string
     */
    function api_version(): string
    {
        return setting('api_version', 'v1');
    }
}

if (!function_exists('api_enabled')) {
    /**
     * Check if API is enabled.
     *
     * @return bool
     */
    function api_enabled(): bool
    {
        $enabled = setting('api_enabled', '1');
        return $enabled === '1' || $enabled === 1 || $enabled === true;
    }
}

if (!function_exists('api_guest_rate_limit')) {
    /**
     * Get API rate limit for guest users (requests per day).
     *
     * @return int
     */
    function api_guest_rate_limit(): int
    {
        return (int) setting('api_guest_rate_limit', 100);
    }
}

if (!function_exists('api_user_rate_limit')) {
    /**
     * Get API rate limit for authenticated users (requests per day).
     *
     * @return int
     */
    function api_user_rate_limit(): int
    {
        return (int) setting('api_user_rate_limit', 1000);
    }
}

if (!function_exists('ip_api_url')) {
    /**
     * Get IP API URL.
     *
     * @return string
     */
    function ip_api_url(): string
    {
        return setting('ip_api_url', 'https://ip-api.in/api/v1/ip/');
    }
}

if (!function_exists('ip_api_token')) {
    /**
     * Get IP API Token.
     *
     * @return string
     */
    function ip_api_token(): string
    {
        return setting('ip_api_token', '');
    }
}

if (!function_exists('format_session_duration')) {
    /**
     * Format duration in minutes to human readable string.
     *
     * @param int $minutes
     * @return string
     */
    function format_session_duration(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $mins . 'm';
        }

        return $mins . 'm';
    }
}

if (!function_exists('schema_markup')) {
    /**
     * Generate simple Schema Markup JSON-LD for pages.
     *
     * @param string $type Schema type (login, register, webpage)
     * @param array $data Additional data for the schema
     * @return string JSON-LD script tag
     */
    function schema_markup(string $type = 'webpage', array $data = []): string
    {
        $siteName = site_name();
        $logoUrl = logo_url('frontend', 'large', 'light');
        $siteDescription = site_description();

        $baseSchema = [
            '@context' => 'https://schema.org',
        ];

        switch ($type) {
            case 'login':
                $loginUrl = route('auth.login');
                $schema = array_merge($baseSchema, [
                    '@type' => 'WebPage',
                    'name' => 'Login - ' . $siteName,
                    'headline' => 'User Login',
                    'description' => 'Secure login page for accessing your dashboard and account management tools.',
                    'url' => $loginUrl,
                    'inLanguage' => app()->getLocale(),
                    'datePublished' => date('c'),
                    'dateModified' => date('c'),
                    'author' => [
                        '@type' => 'Organization',
                        'name' => $siteName,
                    ],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => $siteName,
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => $logoUrl,
                        ],
                    ],
                ], $data);
                break;

            case 'register':
                $registerUrl = route('auth.register');
                $schema = array_merge($baseSchema, [
                    '@type' => 'WebPage',
                    'name' => 'Register - ' . $siteName,
                    'headline' => 'Create Account',
                    'description' => 'Create a new account to access our professional tools and dashboard.',
                    'url' => $registerUrl,
                    'inLanguage' => app()->getLocale(),
                    'datePublished' => date('c'),
                    'dateModified' => date('c'),
                    'author' => [
                        '@type' => 'Organization',
                        'name' => $siteName,
                    ],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => $siteName,
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => $logoUrl,
                        ],
                    ],
                ], $data);
                break;

            default:
                $pageTitle = $data['title'] ?? $siteName;
                $pageDescription = $data['description'] ?? $siteDescription;
                $pageUrl = $data['url'] ?? request()->url();

                $schema = array_merge($baseSchema, [
                    '@type' => 'WebPage',
                    'name' => $pageTitle,
                    'headline' => $pageTitle,
                    'description' => $pageDescription,
                    'url' => $pageUrl,
                    'inLanguage' => app()->getLocale(),
                    'datePublished' => date('c'),
                    'dateModified' => date('c'),
                    'author' => [
                        '@type' => 'Organization',
                        'name' => $siteName,
                    ],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => $siteName,
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => $logoUrl,
                        ],
                    ],
                ], \Illuminate\Support\Arr::except($data, ['title', 'description', 'url']));
        }

        $jsonLd = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return '<script type="application/ld+json">' . $jsonLd . '</script>';
    }
}



if (!function_exists('logo_url')) {
    /**
     * Get logo URL based on type and theme.
     *
     * @param string $type 'admin' or 'frontend'
     * @param string $size 'small' or 'large'
     * @param string $theme 'light' or 'dark'
     * @return string
     */
    function logo_url(string $type = 'admin', string $size = 'large', string $theme = 'light'): string
    {
        // Build the setting key based on the new structure
        $settingKey = $type . '_logo';

        if ($size === 'small') {
            $settingKey .= '_small';
        }

        $settingKey .= '_' . $theme;

        $logoPath = setting($settingKey);

        if ($logoPath) {
            // If it's a default build image (starts with build/)
            if (strpos($logoPath, 'build/') === 0) {
                return asset($logoPath);
            }
            // If it's already a full storage path
            elseif (strpos($logoPath, 'storage/') === 0) {
                return asset($logoPath);
            }
            // If it's just a filename (uploaded file), add storage/ prefix
            else {
                return asset('storage/' . $logoPath);
            }
        }

        // Fallback to default logos based on new key structure
        $fallbacks = [
            'admin_logo_small_light' => 'build/images/logo-mini-dark.png',
            'admin_logo_small_dark' => 'build/images/logo-mini-light.png',
            'admin_logo_light' => 'build/images/logo-dark.png',
            'admin_logo_dark' => 'build/images/logo-light.png',
            'frontend_logo_small_light' => 'build/images/logo-mini-dark.png',
            'frontend_logo_small_dark' => 'build/images/logo-mini-light.png',
            'frontend_logo_light' => 'build/images/logo-dark.png',
            'frontend_logo_dark' => 'build/images/logo-light.png',
        ];

        return asset($fallbacks[$settingKey] ?? 'build/images/logo-light.png');
    }
}

if (!function_exists('favicon_url')) {
    /**
     * Get favicon URL.
     *
     * @return string
     */
    function favicon_url(): string
    {
        $faviconPath = setting('site_favicon', 'favicon.ico');

        if ($faviconPath) {
            if (strpos($faviconPath, 'storage/') === 0) {
                return asset($faviconPath);
            } else {
                return asset($faviconPath);
            }
        }

        return asset('favicon.ico');
    }
}

if (!function_exists('seo_data')) {
    /**
     * Get SEO data for meta tags
     *
     * @param string $title
     * @param string $description
     * @param string $keywords
     * @return array
     */
    function seo_data(string $title = '', string $description = '', string $keywords = ''): array
    {
        $siteName = site_name();

        return [
            'title' => $title ? $title . ' | ' . $siteName : $siteName,
            'description' => $description ?: site_description(),
            'keywords' => $keywords,
            'site_name' => $siteName,
        ];
    }
}

// =============================================================================
// Email Settings Helpers
// =============================================================================

if (!function_exists('email_enabled')) {
    /**
     * Check if email system is enabled.
     *
     * @return bool
     */
    function email_enabled(): bool
    {
        return (bool) setting('email_enabled', true);
    }
}

// =============================================================================
// Date/Time Format Options Helpers
// =============================================================================

if (!function_exists('get_date_format_options')) {
    /**
     * Get available date format options.
     *
     * @return array
     */
    function get_date_format_options(): array
    {
        return [
            // 🌍 INTERNATIONAL / ISO STANDARD
            'Y-m-d'        => '2025-01-15 (Y-m-d)',
            'Y/m/d'        => '2025/01/15 (Y/m/d)',

            // 🇮🇳 / 🇪🇺 EUROPE / INDIA STYLE (Day-Month-Year)
            'd/m/Y'        => '15/01/2025 (d/m/Y)',
            'd-m-Y'        => '15-01-2025 (d-m-Y)',
            'd.m.Y'        => '15.01.2025 (d.m.Y)',

            // 🇺🇸 USA STYLE (Month-Day-Year)
            'm/d/Y'        => '01/15/2025 (m/d/Y)',
            'm-d-Y'        => '01-15-2025 (m-d-Y)',
            'm.d.Y'        => '01.15.2025 (m.d.Y)',

            // 📅 READABLE SHORT MONTH NAMES
            'M d, Y'       => 'Jan 15, 2025 (M d, Y)',
            'j M Y'        => '15 Jan 2025 (j M Y)',
            'd M Y'        => '15 Jan 2025 (d M Y)',

            // 🗓️ FULL MONTH NAME
            'F j, Y'       => 'January 15, 2025 (F j, Y)',
            'j F Y'        => '15 January 2025 (j F Y)',

            // 🗓️ VERBOSE (WITH DAY NAME)
            'D, M j, Y'    => 'Wed, Jan 15, 2025 (D, M j, Y)',
            'l, F j, Y'    => 'Wednesday, January 15, 2025 (l, F j, Y)',
        ];
    }
}

if (!function_exists('get_datetime_format_options')) {
    /**
     * Get available datetime format options.
     *
     * @return array
     */
    function get_datetime_format_options(): array
    {
        return [
            // 🌍 INTERNATIONAL STANDARD (ISO RECOMMENDED)
            'Y-m-d H:i'        => '2025-01-15 14:30 (Y-m-d H:i)',
            'Y-m-d H:i:s'      => '2025-01-15 14:30:45 (Y-m-d H:i:s)',

            // 🇮🇳 / 🇪🇺 EUROPE / INDIA STYLE (Day-Month-Year)
            'd/m/Y H:i'        => '15/01/2025 14:30 (d/m/Y H:i)',
            'd-m-Y H:i'        => '15-01-2025 14:30 (d-m-Y H:i)',
            'd.m.Y H:i'        => '15.01.2025 14:30 (d.m.Y H:i)',

            'd/m/Y g:i A'      => '15/01/2025 2:30 PM (d/m/Y g:i A)',
            'd-m-Y g:i A'      => '15-01-2025 2:30 PM (d-m-Y g:i A)',
            'd.m.Y g:i A'      => '15.01.2025 2:30 PM (d.m.Y g:i A)',

            // 🇺🇸 USA STYLE (Month-Day-Year)
            'm/d/Y h:i A'      => '01/15/2025 2:30 PM (m/d/Y h:i A)',
            'm-d-Y h:i A'      => '01-15-2025 2:30 PM (m-d-Y h:i A)',
            'm.d.Y h:i A'      => '01.15.2025 2:30 PM (m.d.Y h:i A)',

            // 📅 READABLE FORMATS (Short Month Names)
            'M d, Y g:i A'     => 'Jan 15, 2025 2:30 PM (M d, Y g:i A)',
            'j M Y, g:i A'     => '15 Jan 2025, 2:30 PM (j M Y, g:i A)',
            'M d, Y H:i'       => 'Jan 15, 2025 14:30 (M d, Y H:i)',

            // 🗓️ LONG MONTH NAME FORMATS
            'F j, Y g:i A'     => 'January 15, 2025 2:30 PM (F j, Y g:i A)',
            'F j, Y H:i'       => 'January 15, 2025 14:30 (F j, Y H:i)',
            'M d, Y H:i:s'     => 'Jan 15, 2025 14:30:45 (M d, Y H:i:s)',

            // 🗓️ VERBOSE / FULL FORMATS (With Day Names)
            'D, M j, Y g:i A'  => 'Wed, Jan 15, 2025 2:30 PM (D, M j, Y g:i A)',
            'D, d M Y H:i'     => 'Wed, 15 Jan 2025 14:30 (D, d M Y H:i)',
            'l, F j, Y g:i A'  => 'Wednesday, January 15, 2025 2:30 PM (l, F j, Y g:i A)',
            'l, F j, Y H:i:s'  => 'Wednesday, January 15, 2025 14:30:45 (l, F j, Y H:i:s)',
        ];
    }
}

// =============================================================================
// Mail/SMTP Options Helpers
// =============================================================================

if (!function_exists('get_mail_driver_options')) {
    /**
     * Get available mail driver options.
     *
     * @return array
     */
    function get_mail_driver_options(): array
    {
        return [
            'smtp'     => 'SMTP',
            'sendmail' => 'Sendmail',
            'mailgun'  => 'Mailgun',
            'ses'      => 'Amazon SES',
            'postmark' => 'Postmark',
            'log'      => 'Log (Development)',
        ];
    }
}

if (!function_exists('get_smtp_encryption_options')) {
    /**
     * Get available SMTP encryption options.
     *
     * @return array
     */
    function get_smtp_encryption_options(): array
    {
        return [
            'tls'  => 'TLS (Recommended - Port 587)',
            'ssl'  => 'SSL (Port 465)',
            'none' => 'None (Not recommended)',
        ];
    }
}

// =============================================================================
// Storage Options Helpers
// =============================================================================

if (!function_exists('get_storage_driver_options')) {
    /**
     * Get available storage driver options.
     *
     * @return array
     */
    function get_storage_driver_options(): array
    {
        return [
            'local' => 'Local Storage',
            's3'    => 'Amazon S3',
            'gcs'   => 'Google Cloud Storage',
        ];
    }
}

// =============================================================================
// Theme/Appearance Options Helpers
// =============================================================================

if (!function_exists('get_theme_options')) {
    /**
     * Get available theme options.
     *
     * @return array
     */
    function get_theme_options(): array
    {
        return [
            'light' => 'Light',
            'dark'  => 'Dark',
            'auto'  => 'Auto',
        ];
    }
}

if (!function_exists('get_items_per_page_options')) {
    /**
     * Get available items per page options.
     *
     * @return array
     */
    function get_items_per_page_options(): array
    {
        return [
            '10'  => '10 items',
            '25'  => '25 items',
            '50'  => '50 items',
            '100' => '100 items',
            '-1'  => 'All items',
        ];
    }
}

if (!function_exists('breadcrumb_schema')) {
    /**
     * Generate breadcrumb schema markup.
     *
     * @param array $breadcrumbs Array of breadcrumb items [['name' => 'Home', 'url' => '/'], ...]
     * @return string JSON-LD script tag
     */
    function breadcrumb_schema(array $breadcrumbs): string
    {
        if (empty($breadcrumbs)) {
            return '';
        }

        $itemListElement = [];
        foreach ($breadcrumbs as $index => $breadcrumb) {
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['name'],
                'item' => $breadcrumb['url'],
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement,
        ];

        $jsonLd = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return '<script type="application/ld+json">' . $jsonLd . '</script>';
    }
}

// ==========================================
// Socket.IO Settings Helpers
// ==========================================

if (!function_exists('socket_enabled')) {
    /**
     * Check if Socket.IO is enabled.
     *
     * @return bool
     */
    function socket_enabled(): bool
    {
        $enabled = setting('socket_enabled', '1');
        return $enabled === '1' || $enabled === 1 || $enabled === true;
    }
}

if (!function_exists('socket_server_url')) {
    /**
     * Get Socket.IO server URL.
     *
     * @return string
     */
    function socket_server_url(): string
    {
        return setting('socket_server_url', env('SOCKET_SERVER_URL', 'http://localhost:3000'));
    }
}

if (!function_exists('socket_server_port')) {
    /**
     * Get Socket.IO server port.
     *
     * @return int
     */
    function socket_server_port(): int
    {
        return (int) setting('socket_server_port', env('SOCKET_SERVER_PORT', 3000));
    }
}

if (!function_exists('socket_reconnection_attempts')) {
    /**
     * Get Socket.IO reconnection attempts.
     *
     * @return int
     */
    function socket_reconnection_attempts(): int
    {
        return (int) setting('socket_reconnection_attempts', 3);
    }
}

if (!function_exists('socket_reconnection_delay')) {
    /**
     * Get Socket.IO reconnection delay in milliseconds.
     *
     * @return int
     */
    function socket_reconnection_delay(): int
    {
        return (int) setting('socket_reconnection_delay', 5000);
    }
}

if (!function_exists('socket_reconnection_delay_max')) {
    /**
     * Get Socket.IO maximum reconnection delay in milliseconds.
     *
     * @return int
     */
    function socket_reconnection_delay_max(): int
    {
        return (int) setting('socket_reconnection_delay_max', 10000);
    }
}

if (!function_exists('socket_heartbeat_interval')) {
    /**
     * Get Socket.IO heartbeat interval in milliseconds.
     *
     * @return int
     */
    function socket_heartbeat_interval(): int
    {
        return (int) setting('socket_heartbeat_interval', 30000);
    }
}

if (!function_exists('socket_timeout')) {
    /**
     * Get Socket.IO connection timeout in milliseconds.
     *
     * @return int
     */
    function socket_timeout(): int
    {
        return (int) setting('socket_timeout', 10000);
    }
}

if (!function_exists('socket_config')) {
    /**
     * Get all Socket.IO configuration as array.
     *
     * @return array
     */
    function socket_config(): array
    {
        return [
            'enabled' => socket_enabled(),
            'url' => socket_server_url(),
            'port' => socket_server_port(),
            'reconnectionAttempts' => socket_reconnection_attempts(),
            'reconnectionDelay' => socket_reconnection_delay(),
            'reconnectionDelayMax' => socket_reconnection_delay_max(),
            'heartbeatInterval' => socket_heartbeat_interval(),
            'timeout' => socket_timeout(),
        ];
    }
}

if (!function_exists('analytics_store_pageview')) {
    /**
     * Check if pageview storage is enabled.
     *
     * @return bool
     */
    function analytics_store_pageview(): bool
    {
        $enabled = setting('analytics_store_pageview', '1');
        return $enabled === '1' || $enabled === 1 || $enabled === true;
    }
}

if (!function_exists('analytics_deduplication_time')) {
    /**
     * Get pageview deduplication time in seconds.
     *
     * @return int
     */
    function analytics_deduplication_time(): int
    {
        return (int) setting('analytics_deduplication_time', 30);
    }
}

if (!function_exists('analytics_retention_days')) {
    /**
     * Get analytics retention days.
     *
     * @return int
     */
    function analytics_retention_days(): int
    {
        return (int) setting('analytics_retention_days', 90);
    }
}

if (!function_exists('analytics_exclude_ips')) {
    /**
     * Get comma-separated list of excluded IP addresses.
     *
     * @return string
     */
    function analytics_exclude_ips(): string
    {
        return setting('analytics_exclude_ips', '');
    }
}

if (!function_exists('send_admin_notification')) {
    /**
     * Send real-time notification to admins via Socket.IO server
     * 
     * @param string $title The notification title
     * @param string $message The notification message
     * @param string $type The notification type (info, success, warning, danger)
     * @param array $data Additional data (icon, url, etc.)
     * @return bool Success status
     */
    function send_admin_notification(string $title, string $message = '', string $type = 'info', array $data = []): bool
    {
        // Check if socket is enabled
        if (!socket_enabled()) {
            return false;
        }

        try {
            $url = socket_server_url() . '/api/notification';
            $token = config('services.internal_analytics_token', 'secret');

            // Send async request to socket server
            \Illuminate\Support\Facades\Http::timeout(2)
                ->withHeaders([
                    'X-Internal-Token' => $token,
                    'Accept' => 'application/json',
                ])
                ->post($url, [
                    'target' => 'admin',
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data,
                ]);

            return true;
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            \Illuminate\Support\Facades\Log::warning('[Socket] Failed to send admin notification: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('send_app_notification')) {
    /**
     * Send real-time notification to mobile app users via Socket.IO server
     * 
     * @param string $title The notification title
     * @param string $message The notification message
     * @param string $type The notification type (info, success, warning, error)
     * @param array $data Additional data (icon, url, etc.)
     * @param int|null $userId Specific user ID (null for broadcast to all app users)
     * @return bool Success status
     */
    function send_app_notification(string $title, string $message = '', string $type = 'info', array $data = [], ?int $userId = null): bool
    {
        // Check if socket is enabled
        if (!socket_enabled()) {
            return false;
        }

        try {
            $url = socket_server_url() . '/api/notification';
            $token = config('services.internal_analytics_token', 'secret');

            $payload = [
                'target' => 'app',
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ];

            // Add user_id if targeting specific user
            if ($userId !== null) {
                $payload['user_id'] = $userId;
            }

            // Send async request to socket server
            \Illuminate\Support\Facades\Http::timeout(2)
                ->withHeaders([
                    'X-Internal-Token' => $token,
                    'Accept' => 'application/json',
                ])
                ->post($url, $payload);

            return true;
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            \Illuminate\Support\Facades\Log::warning('[Socket] Failed to send app notification: ' . $e->getMessage());
            return false;
        }
    }
}
