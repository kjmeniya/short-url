<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class EmailLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message_id',
        'email_template_id',
        'user_id',
        'recipient_email',
        'recipient_name',
        'sender_email',
        'sender_name',
        'subject',
        'body',
        'type',
        'status',
        'metadata',
        'mailer',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'error_message',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    /**
     * Get the email template that was used for this email.
     */
    public function emailTemplate(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    /**
     * Get the user who sent this email.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for filtering by recipient email.
     */
    public function scopeByRecipient(Builder $query, string $email): Builder
    {
        return $query->where('recipient_email', 'like', '%' . $email . '%');
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeByDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for successful emails.
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->whereIn('status', ['sent', 'delivered', 'opened', 'clicked']);
    }

    /**
     * Scope for failed emails.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->whereIn('status', ['failed', 'bounced']);
    }

    /**
     * Get available email statuses.
     */
    public static function getStatuses(): array
    {
        return [
            'pending' => 'Pending',
            'sent' => 'Sent',
            'delivered' => 'Delivered',
            'failed' => 'Failed',
            'bounced' => 'Bounced',
            'opened' => 'Opened',
            'clicked' => 'Clicked',
        ];
    }

    /**
     * Get available email types.
     */
    public static function getTypes(): array
    {
        return [
            'general' => 'General',
            'password_reset' => 'Password Reset',
            'welcome' => 'Welcome Email',
            'email_verification' => 'Email Verification',
            'two_factor_auth' => 'Two-Factor Auth',
            'account_management' => 'Account Management',
            'newsletter' => 'Newsletter',
            'contact' => 'Contact',
            'test' => 'Test Email',
            'notification' => 'Notification',
            'reminder' => 'Reminder',
        ];
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'sent' => '<span class="badge bg-primary">Sent</span>',
            'delivered' => '<span class="badge bg-success">Delivered</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
            'bounced' => '<span class="badge bg-danger">Bounced</span>',
            'opened' => '<span class="badge bg-info">Opened</span>',
            'clicked' => '<span class="badge bg-success">Clicked</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get type badge HTML.
     */
    public function getTypeBadgeAttribute(): string
    {
        $badges = [
            'general' => '<span class="badge bg-secondary">General</span>',
            'password_reset' => '<span class="badge bg-warning">Password Reset</span>',
            'welcome' => '<span class="badge bg-success">Welcome</span>',
            'email_verification' => '<span class="badge bg-info">Email Verification</span>',
            'two_factor_auth' => '<span class="badge bg-primary">2FA Code</span>',
            'account_management' => '<span class="badge bg-danger">Account Mgmt</span>',
            'newsletter' => '<span class="badge bg-purple">Newsletter</span>',
            'contact' => '<span class="badge bg-secondary">Contact</span>',
            'test' => '<span class="badge bg-dark">Test</span>',
            'notification' => '<span class="badge bg-info">Notification</span>',
            'reminder' => '<span class="badge bg-primary">Reminder</span>',
        ];

        return $badges[$this->type] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Check if email was successfully sent.
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['sent', 'delivered', 'opened', 'clicked']);
    }

    /**
     * Check if email failed.
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'bounced']);
    }

    /**
     * Get formatted sent date.
     */
    public function getFormattedSentDateAttribute(): string
    {
        return $this->sent_at ? formatUserDateTime($this->sent_at) : 'Not sent';
    }

    /**
     * Get short body preview.
     */
    public function getBodyPreviewAttribute(): string
    {
        if (!$this->body) {
            return 'No content';
        }

        $text = strip_tags($this->body);
        return strlen($text) > 100 ? substr($text, 0, 100) . '...' : $text;
    }
}
