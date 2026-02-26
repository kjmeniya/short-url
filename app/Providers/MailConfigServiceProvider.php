<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            // Check if settings table exists and has data
            if (Schema::hasTable('settings')) {
                // Get SMTP settings from database
                $mailDriver = Setting::where('key', 'mail_driver')->value('value') ?? 'smtp';
                $smtpHost = Setting::where('key', 'smtp_host')->value('value');
                $smtpPort = Setting::where('key', 'smtp_port')->value('value');
                $smtpEncryption = Setting::where('key', 'smtp_encryption')->value('value');
                $smtpUsername = Setting::where('key', 'smtp_username')->value('value');
                $smtpPassword = Setting::where('key', 'smtp_password')->value('value');
                $mailFromAddress = Setting::where('key', 'mail_from_address')->value('value');
                $mailFromName = Setting::where('key', 'mail_from_name')->value('value');
                $smtpSslMode = Setting::where('key', 'smtp_ssl_mode')->value('value') ?? '1';
                $smtpTimeout = Setting::where('key', 'smtp_timeout')->value('value') ?? '30';

                // Configure mail settings if SMTP settings exist
                if ($smtpHost && $smtpPort) {
                    Config::set('mail.default', $mailDriver);

                    Config::set('mail.mailers.smtp', [
                        'transport' => 'smtp',
                        'host' => $smtpHost,
                        'port' => (int) $smtpPort,
                        'encryption' => $smtpEncryption === 'none' ? null : $smtpEncryption,
                        'username' => $smtpUsername,
                        'password' => $smtpPassword,
                        'timeout' => (int) $smtpTimeout,
                        'local_domain' => env('MAIL_EHLO_DOMAIN'),
                    ]);

                    // Configure from address
                    if ($mailFromAddress) {
                        Config::set('mail.from.address', $mailFromAddress);
                    }

                    if ($mailFromName) {
                        Config::set('mail.from.name', $mailFromName);
                    }

                    // Configure SSL verification
                    if ($smtpSslMode == '0') {
                        Config::set('mail.mailers.smtp.verify_peer', false);
                        Config::set('mail.mailers.smtp.verify_peer_name', false);
                        Config::set('mail.mailers.smtp.allow_self_signed', true);
                    }
                }
            }
        } catch (\Exception $e) {
            // Silently fail if settings table doesn't exist (during migration)
            // This prevents errors during initial setup
            Log::debug('Mail config not loaded from database: ' . $e->getMessage());
        }
    }
}
