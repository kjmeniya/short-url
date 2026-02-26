<?php

namespace App\Services;

use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Symfony\Component\Mime\Email;

class EmailTrackingService
{
    /**
     * Log an email before it's sent.
     */
    public function logEmailSending(MessageSending $event): void
    {
        $message = $event->message;
        
        if (!$message instanceof Email) {
            return;
        }

        $emailLog = $this->createEmailLog($message, 'pending');
        
        // Store the log ID in the message headers for later reference
        $message->getHeaders()->addTextHeader('X-Email-Log-ID', $emailLog->id);
    }

    /**
     * Update email log when email is sent.
     */
    public function logEmailSent(MessageSent $event): void
    {
        $message = $event->message;
        
        if (!$message instanceof Email) {
            return;
        }

        // Get the log ID from headers
        $headers = $message->getHeaders();
        $logIdHeader = $headers->get('X-Email-Log-ID');
        
        if (!$logIdHeader) {
            return;
        }

        $logId = $logIdHeader->getBody();
        $emailLog = EmailLog::find($logId);
        
        if ($emailLog) {
            $emailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
                'message_id' => $message->getHeaders()->get('Message-ID')?->getBody(),
            ]);
        }
    }

    /**
     * Create an email log entry.
     */
    protected function createEmailLog(Email $message, string $status = 'pending'): EmailLog
    {
        $to = $message->getTo();
        $from = $message->getFrom();
        $subject = $message->getSubject();
        $body = $message->getHtmlBody() ?: $message->getTextBody();

        // Extract recipient information
        $recipientEmail = '';
        $recipientName = '';
        if (!empty($to)) {
            $firstRecipient = reset($to);
            if ($firstRecipient instanceof \Symfony\Component\Mime\Address) {
                $recipientEmail = $firstRecipient->getAddress();
                $recipientName = $firstRecipient->getName() ?: '';
            } else {
                $recipientEmail = (string) $firstRecipient;
            }
        }

        // Extract sender information
        $senderEmail = '';
        $senderName = '';
        if (!empty($from)) {
            $firstSender = reset($from);
            if ($firstSender instanceof \Symfony\Component\Mime\Address) {
                $senderEmail = $firstSender->getAddress();
                $senderName = $firstSender->getName() ?: '';
            } else {
                $senderEmail = (string) $firstSender;
            }
        }

        // Determine email type and template
        $emailType = $this->determineEmailType($subject, $body);
        $emailTemplateId = $this->findEmailTemplate($emailType, $subject);

        // Get current user if authenticated
        $userId = Auth::id();

        // Prepare metadata
        $metadata = [
            'user_agent' => Request::header('User-Agent'),
            'headers' => $this->extractRelevantHeaders($message),
        ];

        return EmailLog::create([
            'email_template_id' => $emailTemplateId,
            'user_id' => $userId,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'sender_email' => $senderEmail ?: config('mail.from.address'),
            'sender_name' => $senderName ?: config('mail.from.name'),
            'subject' => $subject,
            'body' => $body,
            'type' => $emailType,
            'status' => $status,
            'metadata' => $metadata,
            'mailer' => config('mail.default'),
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }

    /**
     * Determine email type based on subject and content.
     */
    protected function determineEmailType(string $subject, string $body): string
    {
        $subject = strtolower($subject);
        $body = strtolower($body);

        if (str_contains($subject, 'password') || str_contains($subject, 'reset')) {
            return 'password_reset';
        }

        if (str_contains($subject, 'welcome') || str_contains($body, 'welcome')) {
            return 'welcome';
        }

        if (str_contains($subject, 'notification') || str_contains($subject, 'alert')) {
            return 'notification';
        }

        if (str_contains($subject, 'reminder')) {
            return 'reminder';
        }

        if (str_contains($subject, 'marketing') || str_contains($subject, 'newsletter')) {
            return 'marketing';
        }

        return 'general';
    }

    /**
     * Find email template based on type and subject.
     */
    protected function findEmailTemplate(string $type, string $subject): ?int
    {
        $template = EmailTemplate::where('type', $type)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            // Try to find by subject similarity
            $template = EmailTemplate::where('subject', 'like', '%' . substr($subject, 0, 20) . '%')
                ->where('is_active', true)
                ->first();
        }

        return $template?->id;
    }

    /**
     * Extract relevant headers from the message.
     */
    protected function extractRelevantHeaders(Email $message): array
    {
        $headers = [];
        $messageHeaders = $message->getHeaders();

        $relevantHeaders = [
            'Message-ID',
            'Date',
            'Reply-To',
            'Return-Path',
            'X-Mailer',
            'X-Priority',
        ];

        foreach ($relevantHeaders as $headerName) {
            $header = $messageHeaders->get($headerName);
            if ($header) {
                $headers[$headerName] = $header->getBody();
            }
        }

        return $headers;
    }

    /**
     * Log email manually (for custom email sending).
     */
    public function logEmail(array $data): EmailLog
    {
        $defaultData = [
            'status' => 'pending',
            'type' => 'general',
            'mailer' => config('mail.default'),
            'sender_email' => config('mail.from.address'),
            'sender_name' => config('mail.from.name'),
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
            'user_id' => Auth::id(),
            'metadata' => [],
        ];

        return EmailLog::create(array_merge($defaultData, $data));
    }

    /**
     * Update email status.
     */
    public function updateEmailStatus(int $emailLogId, string $status, array $additionalData = []): bool
    {
        $emailLog = EmailLog::find($emailLogId);
        
        if (!$emailLog) {
            return false;
        }

        $updateData = ['status' => $status];

        // Set timestamp based on status
        switch ($status) {
            case 'sent':
                $updateData['sent_at'] = now();
                break;
            case 'delivered':
                $updateData['delivered_at'] = now();
                break;
            case 'opened':
                $updateData['opened_at'] = now();
                break;
            case 'clicked':
                $updateData['clicked_at'] = now();
                break;
        }

        // Merge additional data
        $updateData = array_merge($updateData, $additionalData);

        return $emailLog->update($updateData);
    }

    /**
     * Get email statistics.
     */
    public function getEmailStats(array $filters = []): array
    {
        $query = EmailLog::query();

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $total = $query->count();
        $sent = $query->where('status', 'sent')->count();
        $delivered = $query->where('status', 'delivered')->count();
        $failed = $query->whereIn('status', ['failed', 'bounced'])->count();
        $opened = $query->where('status', 'opened')->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'delivered' => $delivered,
            'failed' => $failed,
            'opened' => $opened,
            'success_rate' => $total > 0 ? round(($sent + $delivered) / $total * 100, 2) : 0,
            'open_rate' => $delivered > 0 ? round($opened / $delivered * 100, 2) : 0,
        ];
    }
}
