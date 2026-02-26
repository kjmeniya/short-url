<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;

class EmailService
{
    /**
     * Email type constants for better maintainability.
     */
    private const TYPE_GENERAL = 'general';

    /**
     * Email status constants.
     */
    private const STATUS_PENDING = 'pending';
    private const STATUS_SENT = 'sent';
    private const STATUS_FAILED = 'failed';

    // ============================================
    // Core Email Sending Methods
    // ============================================

    /**
     * Check if email queue is enabled.
     */
    public function isQueueEnabled(): bool
    {
        return email_queue_enabled();
    }

    /**
     * Send email using template (with queue support).
     */
    public function sendTemplateEmail(string $templateSlug, string $toEmail, array $variables = [], ?string $toName = null): bool
    {
        if ($this->isQueueEnabled()) {
            dispatch(function () use ($templateSlug, $toEmail, $variables, $toName) {
                $this->sendTemplateEmailSync($templateSlug, $toEmail, $variables, $toName);
            })->onQueue('emails');
            return true;
        }

        return $this->sendTemplateEmailSync($templateSlug, $toEmail, $variables, $toName);
    }

    /**
     * Send email using template synchronously (immediate sending).
     */
    public function sendTemplateEmailSync(string $templateSlug, string $toEmail, array $variables = [], ?string $toName = null): bool
    {
        try {
            $template = EmailTemplate::findBySlug($templateSlug);

            if (!$template) {
                Log::error("Email template not found", ['slug' => $templateSlug]);
                return false;
            }

            $subject = $this->replaceVariables($template->subject, $variables);
            $body = $this->replaceVariables($template->body, $variables);
            $user = User::where('email', $toEmail)->first();

            $emailLog = $this->createEmailLog(
                $toEmail,
                $subject,
                $body,
                $template->type ?? self::TYPE_GENERAL,
                $template,
                $toName,
                $user
            );

            Mail::send('emails.custom-template', ['content' => $body], function ($message) use ($toEmail, $toName, $subject) {
                $message->to($toEmail, $toName ?: $toEmail)->subject($subject);
            });

            $this->updateEmailLogStatus($emailLog, self::STATUS_SENT, ['delivered_at' => now(), 'sent_at' => now()]);

            return true;
        } catch (\Exception $e) {
            $this->handleEmailError($e, $templateSlug, $toEmail, $emailLog ?? null);
            return false;
        }
    }

    /**
     * Send welcome email to user.
     */
    public function sendWelcomeEmail(string $email, string $name): bool
    {
        return $this->sendTemplateEmail('welcome-email', $email, [
            'name' => $name,
            'email' => $email,
            'dashboard_link' => route('admin.dashboard'),
            'app_name' => site_name(),
        ], $name);
    }

    /**
     * Send email verification email.
     */
    public function sendEmailVerification(string $email, string $name, string $verificationLink): bool
    {
        return $this->sendTemplateEmail('email-verification', $email, [
            'name' => $name,
            'email' => $email,
            'verification_link' => $verificationLink,
            'app_name' => site_name(),
        ], $name);
    }

    /**
     * Send email verification OTP.
     */
    public function sendEmailVerificationOTP(string $email, string $name, string $otp): bool
    {
        return $this->sendTemplateEmail('email-verification-otp', $email, [
            'name' => $name,
            'email' => $email,
            'otp' => $otp,
            'expires_in' => 10,
            'app_name' => site_name(),
        ], $name);
    }

    /**
     * Send two-factor authentication code email.
     */
    public function sendTwoFactorCode(string $email, string $name, string $code, int $expiresInMinutes = 5): bool
    {
        return $this->sendTemplateEmail('two-factor-auth-code', $email, [
            'name' => $name,
            'code' => $code,
            'expires_in' => $expiresInMinutes,
            'app_name' => site_name(),
        ], $name);
    }

    /**
     * Send password reset email.
     */
    public function sendPasswordResetEmail(string $email, string $name, string $resetLink): bool
    {
        return $this->sendTemplateEmail('password-reset', $email, [
            'name' => $name,
            'reset_link' => $resetLink,
            'app_name' => site_name(),
            'app_url' => app_url(),
        ], $name);
    }

    /**
     * Send contact thank you email.
     */
    public function sendContactThankYou(string $email, string $name, string $subject, string $message): bool
    {
        return $this->sendTemplateEmail('contact-thank-you', $email, [
            'name' => $name,
            'subject' => $subject,
            'message' => $message,
            'app_name' => site_name(),
            'app_url' => app_url(),
        ], $name);
    }

    /**
     * Send contact reply email.
     */
    public function sendContactReply(string $email, string $name, string $subject, string $originalMessage, string $replyMessage, string $sentDate): bool
    {
        return $this->sendTemplateEmail('contact-replied-user', $email, [
            'name' => $name,
            'subject' => $subject,
            'message' => $originalMessage,
            'reply_message' => $replyMessage,
            'sent_date' => $sentDate,
            'reply_to_email' => contact_email(),
            'app_name' => site_name(),
        ], $name);
    }

    // ============================================
    // Protected Helper Methods
    // ============================================

    /**
     * Replace variables in template content.
     */
    protected function replaceVariables(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Create email log entry.
     * 
     * @param string $toEmail Recipient email address
     * @param string $subject Email subject
     * @param string $body Email body content
     * @param string $type Email type (default: general)
     * @param EmailTemplate|null $template Optional email template
     * @param string|null $toName Optional recipient name
     * @param User|null $user Optional user object
     * @return EmailLog
     */
    protected function createEmailLog(
        string $toEmail,
        string $subject,
        string $body,
        string $type = self::TYPE_GENERAL,
        ?EmailTemplate $template = null,
        ?string $toName = null,
        ?User $user = null
    ): EmailLog {
        // Prepare metadata
        $metadata = [
            'user_agent' => Request::header('User-Agent'),
            'ip_address' => Request::ip(),
            'template_slug' => $template?->slug,
            'timestamp' => now()->toIso8601String(),
        ];

        return EmailLog::create([
            'email_template_id' => $template?->id,
            'user_id' => $user?->id,
            'recipient_email' => $toEmail,
            'recipient_name' => $toName ?? $user?->name,
            'sender_email' => config('mail.from.address'),
            'sender_name' => config('mail.from.name'),
            'subject' => $subject,
            'body' => $body,
            'type' => $type,
            'status' => self::STATUS_PENDING,
            'mailer' => config('mail.default'),
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Update email log status helper.
     */
    protected function updateEmailLogStatus(EmailLog $emailLog, string $status, array $additionalData = []): void
    {
        $emailLog->update(array_merge(['status' => $status], $additionalData));
    }

    /**
     * Handle email sending error.
     */
    protected function handleEmailError(\Exception $e, string $context, string $toEmail, ?EmailLog $emailLog): void
    {
        Log::error("Failed to send email", [
            'context' => $context,
            'to_email' => $toEmail,
            'error' => $e->getMessage(),
        ]);

        if ($emailLog) {
            $emailLog->update([
                'status' => self::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Retry sending a failed email (with queue support).
     * 
     * @param EmailLog $emailLog
     * @return bool
     * @throws \Exception
     */
    public function retryEmail(EmailLog $emailLog): bool
    {
        // Validate that the email can be retried
        if ($emailLog->status !== 'failed') {
            throw new \Exception('Only failed emails can be retried');
        }

        // Check if we have the necessary data to resend
        if (!$emailLog->recipient_email || !$emailLog->subject || !$emailLog->body) {
            throw new \Exception('Email data is incomplete and cannot be resent');
        }

        // If queue is enabled, dispatch to queue
        if ($this->isQueueEnabled()) {
            dispatch(function () use ($emailLog) {
                $this->retryEmailSync($emailLog);
            })->onQueue('emails');

            // Update status to pending (will be updated by queue worker)
            $emailLog->update([
                'status' => self::STATUS_PENDING,
                'error_message' => null,
            ]);

            return true;
        }

        // Otherwise, send synchronously
        return $this->retryEmailSync($emailLog);
    }

    /**
     * Retry sending a failed email synchronously.
     * 
     * @param EmailLog $emailLog
     * @return bool
     * @throws \Exception
     */
    protected function retryEmailSync(EmailLog $emailLog): bool
    {
        // Reset the email log status
        $emailLog->update([
            'status' => self::STATUS_PENDING,
            'error_message' => null,
            'sent_at' => null,
            'delivered_at' => null,
        ]);

        try {
            // Send the email directly without creating a new log
            Mail::send(
                'emails.custom-template',
                ['content' => $emailLog->body],
                function ($message) use ($emailLog) {
                    $message->to(
                        $emailLog->recipient_email,
                        $emailLog->recipient_name ?: $emailLog->recipient_email
                    )->subject($emailLog->subject);
                }
            );

            // Update the existing email log status
            $emailLog->update([
                'status' => self::STATUS_SENT,
                'sent_at' => now(),
                'delivered_at' => now(),
                'error_message' => null,
            ]);

            return true;
        } catch (\Exception $e) {
            // Update email log with error
            $emailLog->update([
                'status' => self::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            // Re-throw the exception
            throw $e;
        }
    }
}
