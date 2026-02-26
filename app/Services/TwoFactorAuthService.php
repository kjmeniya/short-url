<?php

namespace App\Services;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Support\Facades\Log;

class TwoFactorAuthService
{
    // No dependencies needed - all functionality consolidated

    /**
     * Generate a new secret key for two-factor authentication.
     */
    public function generateSecretKey(): string
    {
        return $this->generateSecret();
    }

    /**
     * Generate QR code URL for the given user and secret.
     */
    public function generateQRCodeUrl(User $user, string $secret): string
    {
        $label = $user->email;
        $issuer = config('app.name');

        $params = [
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30
        ];

        return 'otpauth://totp/' . urlencode($label) . '?' . http_build_query($params);
    }

    /**
     * Generate QR code as SVG for the given user and secret.
     */
    public function generateQRCodeSvg(User $user, string $secret): string
    {
        $qrCodeUrl = $this->generateQRCodeUrl($user, $secret);
        $encodedData = urlencode($qrCodeUrl);
        $size = 200;
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}";

        return '<img src="' . $qrUrl . '" alt="QR Code" class="img-fluid" style="max-width: ' . $size . 'px;">';
    }

    /**
     * Verify a code based on the user's 2FA method.
     */
    public function verifyCode(User $user, string $code): bool
    {
        if ($user->two_factor_method === 'email') {
            return $this->verifyEmailCode($user, $code);
        } else {
            // QR code method
            $secret = $user->getTwoFactorSecret();

            if (!$secret) {
                return false;
            }

            return $this->verifyTOTPCode($secret, $code);
        }
    }

    /**
     * Verify a TOTP code against a given secret (for setup verification).
     */
    public function verifyCodeWithSecret(string $secret, string $code): bool
    {
        return $this->verifyTOTPCode($secret, $code);
    }

    /**
     * Generate recovery codes for the user.
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5)));
        }
        return $codes;
    }

    /**
     * Verify a recovery code for the user.
     */
    public function verifyRecoveryCode(User $user, string $code): bool
    {
        return $user->useRecoveryCode($code);
    }

    /**
     * Enable two-factor authentication for a user.
     */
    public function enableTwoFactor(User $user, string $method, ?string $secret = null, ?string $verificationCode = null): bool
    {
        if ($method === 'qr_code') {
            // Verify the TOTP code first
            if (!$secret || !$verificationCode || !$this->verifyCodeWithSecret($secret, $verificationCode)) {
                return false;
            }
        } elseif ($method === 'email') {
            // For email method, we don't need a secret, just verify the method is valid
            $secret = null;
        } else {
            return false;
        }

        // Generate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();

        // Enable 2FA for the user
        $user->enableTwoFactor($method, $secret, $recoveryCodes);

        return true;
    }

    /**
     * Disable two-factor authentication for a user.
     */
    public function disableTwoFactor(User $user): void
    {
        $user->disableTwoFactor();
    }

    /**
     * Regenerate recovery codes for a user.
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $recoveryCodes = $this->generateRecoveryCodes();
        
        $user->update([
            'two_factor_recovery_codes' => array_map('encrypt', $recoveryCodes)
        ]);
        
        return $recoveryCodes;
    }

    /**
     * Check if the user needs to verify 2FA during login.
     */
    public function requiresTwoFactorVerification(User $user): bool
    {
        return $user->hasTwoFactorConfirmed();
    }

    /**
     * Verify either TOTP code or recovery code.
     */
    public function verifyTwoFactor(User $user, string $code): bool
    {
        // First try method-specific code
        if ($this->verifyCode($user, $code)) {
            return true;
        }

        // Then try recovery code
        return $this->verifyRecoveryCode($user, $code);
    }

    /**
     * Send verification code for email-based 2FA.
     */
    public function sendEmailCode(User $user): bool
    {
        return $this->sendVerificationCodeEmail($user);
    }

    /**
     * Check if user has a pending email code.
     */
    public function hasPendingEmailCode(User $user): bool
    {
        return $user->two_factor_code &&
               $user->two_factor_code_expires_at &&
               $user->two_factor_code_expires_at->isFuture();
    }

    /**
     * Get remaining time for email code.
     */
    public function getEmailCodeRemainingTime(User $user): string
    {
        if (!$this->hasPendingEmailCode($user)) {
            return '0:00';
        }

        $seconds = $user->two_factor_code_expires_at->diffInSeconds(now());
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }

    /**
     * Resend email verification code.
     */
    public function resendEmailCode(User $user): bool
    {
        return $this->sendEmailCode($user);
    }

    // ========================================
    // CONSOLIDATED PRIVATE METHODS
    // ========================================

    /**
     * Generate a random secret key for TOTP.
     */
    private function generateSecret(int $length = 32): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $secret;
    }

    /**
     * Verify TOTP code.
     */
    private function verifyTOTPCode(string $secret, string $code, int $window = 1): bool
    {
        $timestamp = time();

        // Check current time and adjacent windows
        for ($i = -$window; $i <= $window; $i++) {
            $testTime = $timestamp + ($i * 30);
            if ($this->generateTOTPCode($secret, $testTime) === $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate TOTP code based on secret and time.
     */
    private function generateTOTPCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $timeSlice = intval($timestamp / 30); // 30-second window

        // Convert secret from base32
        $binarySecret = $this->base32Decode($secret);

        // Create time-based counter
        $timeBytes = pack('N*', 0) . pack('N*', $timeSlice);

        // Generate HMAC
        $hash = hash_hmac('sha1', $timeBytes, $binarySecret, true);

        // Dynamic truncation
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify email verification code.
     */
    private function verifyEmailCode(User $user, string $code): bool
    {
        // Check if code exists and hasn't expired
        if (!$user->two_factor_code || !$user->two_factor_code_expires_at) {
            return false;
        }

        // Check if code has expired
        if ($user->two_factor_code_expires_at->isPast()) {
            $this->clearEmailCode($user);
            return false;
        }

        // Verify code
        if ($user->two_factor_code === $code) {
            $this->clearEmailCode($user);
            return true;
        }

        return false;
    }

    /**
     * Send verification code email.
     */
    private function sendVerificationCodeEmail(User $user): bool
    {
        try {
            // Generate 6-digit code
            $code = $this->generateEmailCode();

            // Set expiration time (5 minutes)
            $expiresAt = now()->addMinutes(5);

            // Store code in database
            $user->update([
                'two_factor_code' => $code,
                'two_factor_code_expires_at' => $expiresAt
            ]);

            // Send email
            $this->sendCodeEmail($user, $code);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send 2FA code: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear the email verification code.
     */
    private function clearEmailCode(User $user): void
    {
        $user->update([
            'two_factor_code' => null,
            'two_factor_code_expires_at' => null
        ]);
    }

    /**
     * Generate a 6-digit email verification code.
     */
    private function generateEmailCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send verification code email.
     */
    private function sendCodeEmail(User $user, string $code): void
    {
        $emailService = app(EmailService::class);
        $emailService->sendTwoFactorCode($user->email, $user->name, $code, 5);
    }

    /**
     * Base32 decode function for TOTP.
     */
    private function base32Decode(string $secret): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper($secret);
        $decoded = '';
        $buffer = 0;
        $bitsLeft = 0;

        for ($i = 0; $i < strlen($secret); $i++) {
            $char = $secret[$i];
            $val = strpos($chars, $char);

            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $decoded .= chr(($buffer >> ($bitsLeft - 8)) & 0xFF);
                $bitsLeft -= 8;
            }
        }

        return $decoded;
    }
}
