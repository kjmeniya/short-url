<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class ConfigurationService
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Apply all settings to Laravel configuration
     */
    public function applySettings(): void
    {
        $this->applyMailSettings();
        $this->applyGeneralSettings();
        $this->applyStorageSettings();
        $this->applySecuritySettings();
        $this->applySystemSettings();
    }

    /**
     * Apply mail and SMTP settings
     */
    protected function applyMailSettings(): void
    {
        // Mail Driver Configuration
        $driver = mail_driver();
        if ($driver) {
            Config::set('mail.default', $driver);
        }

        // SMTP Configuration
        $host = smtp_host();
        if ($host) {
            Config::set('mail.mailers.smtp.host', $host);
        }

        $port = smtp_port();
        if ($port) {
            Config::set('mail.mailers.smtp.port', $port);
        }

        $username = smtp_username();
        if ($username) {
            Config::set('mail.mailers.smtp.username', $username);
        }

        $password = smtp_password();
        if ($password) {
            Config::set('mail.mailers.smtp.password', $password);
        }

        $encryption = smtp_encryption();
        if ($encryption) {
            Config::set('mail.mailers.smtp.encryption', $encryption);
        }

        $timeout = smtp_timeout();
        if ($timeout) {
            Config::set('mail.mailers.smtp.timeout', $timeout);
        }

        // SSL Mode Configuration
        if (smtp_ssl_mode()) {
            // Enable SSL verification
            Config::set('mail.mailers.smtp.stream', [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                ],
            ]);
        } else {
            // Disable SSL verification for development/testing
            Config::set('mail.mailers.smtp.stream', [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ]);
        }

        // Email From Configuration
        $fromAddress = mail_from_address();
        if ($fromAddress) {
            Config::set('mail.from.address', $fromAddress);
        }

        $fromName = mail_from_name();
        if ($fromName) {
            Config::set('mail.from.name', $fromName);
        }

        // Reply-To Configuration
        $replyTo = mail_reply_to();
        if ($replyTo) {
            Config::set('mail.reply_to.address', $replyTo);
            Config::set('mail.reply_to.name', $fromName ?: 'Support');
        }
    }

    /**
     * Apply general settings
     */
    protected function applyGeneralSettings(): void
    {
        // App URL Configuration
        if ($this->settingsService->get('app_url')) {
            Config::set('app.url', $this->settingsService->get('app_url'));
            URL::forceRootUrl($this->settingsService->get('app_url'));
        }

        // Timezone Configuration
        // if ($this->settingsService->get('timezone')) {
        //     Config::set('app.timezone', $this->settingsService->get('timezone'));
        //     date_default_timezone_set($this->settingsService->get('timezone'));
        // }
    }

    /**
     * Apply storage settings
     */
    protected function applyStorageSettings(): void
    {
        // Asset URL Configuration
        $assetUrl = asset_url_setting();
        if ($assetUrl) {
            Config::set('app.asset_url', $assetUrl);
        }

        // Storage Driver Configuration
        $driver = storage_driver();
        if ($driver) {
            Config::set('filesystems.default', $driver);
        }

        // Public Storage URL
        $publicUrl = storage_public_url();
        if ($publicUrl) {
            Config::set('filesystems.disks.public.url', $publicUrl);
        }

        // File Upload Configuration
        $maxSize = max_upload_size();
        if ($maxSize) {
            Config::set('filesystems.max_upload_size', $maxSize * 1024); // Convert MB to KB
        }

        // Upload Path Configuration
        $path = upload_path();
        if ($path) {
            Config::set('filesystems.upload_path', $path);
        }

        // Allowed File Types Configuration
        Config::set('filesystems.allowed_types', allowed_file_types());
    }

    /**
     * Apply security settings
     */
    protected function applySecuritySettings(): void
    {
        // Session Configuration
        $sessionTimeout = session_timeout();
        if ($sessionTimeout) {
            Config::set('session.lifetime', $sessionTimeout);
        }

        // CSRF Token Lifetime - tied to session lifetime in Laravel
        // We apply the csrf_token_lifetime to session as well if different
        $csrfLifetime = csrf_token_lifetime();
        if ($csrfLifetime && $csrfLifetime !== $sessionTimeout) {
            // CSRF uses session, so we use the smaller of the two values
            Config::set('session.lifetime', min($sessionTimeout, $csrfLifetime));
        }

        // Password Reset Configuration
        $passwordResetExpiry = password_reset_expiry();
        if ($passwordResetExpiry) {
            Config::set('auth.passwords.users.expire', $passwordResetExpiry);
        }

        // Force HTTPS
        if ($this->settingsService->getTyped('force_https', false)) {
            URL::forceScheme('https');
        }
    }

    /**
     * Apply system settings
     */
    protected function applySystemSettings(): void
    {
        // Debug Mode
        Config::set('app.debug', app_debug());

        // Environment
        $env = app_env();
        if ($env) {
            Config::set('app.env', $env);
        }

        // Cache Configuration
        if (!cache_enabled()) {
            Config::set('cache.default', 'array');
        }

        // Logging Configuration
        $logLevel = log_level();
        if ($logLevel) {
            Config::set('logging.channels.single.level', $logLevel);
            Config::set('logging.channels.daily.level', $logLevel);
        }

        // Note: API Rate Limiting is now handled directly in ApiThrottleMiddleware
        // using api_guest_rate_limit() and api_user_rate_limit() helpers
    }

    /**
     * Get mail configuration for internal use (SMTP testing)
     */
    protected function getMailConfig(): array
    {
        return [
            'driver' => mail_driver(),
            'host' => smtp_host(),
            'port' => smtp_port(),
            'username' => smtp_username(),
            'password' => smtp_password(),
            'encryption' => smtp_encryption(),
            'timeout' => smtp_timeout(),
            'ssl_mode' => smtp_ssl_mode(),
            'from_address' => mail_from_address(),
            'from_name' => mail_from_name(),
            'reply_to' => mail_reply_to(),
        ];
    }

    /**
     * Test SMTP connection
     */
    public function testSmtpConnection(): array
    {
        try {
            $config = $this->getMailConfig();
            // Create a test transport
            $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                $config['host'],
                $config['port'],
                $config['encryption'] !== 'none' ? $config['encryption'] : null
            );

            if ($config['username']) {
                $transport->setUsername($config['username']);
            }

            if ($config['password']) {
                $transport->setPassword($config['password']);
            }

            // Test the connection
            $transport->start();

            return [
                'success' => true,
                'message' => 'SMTP connection successful'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'SMTP connection failed: ' . $e->getMessage()
            ];
        }
    }
}
