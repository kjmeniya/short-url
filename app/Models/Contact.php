<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Contact extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'subject',
        'message',
        'status',
        'is_spam',
        'replied_at',
        'replied_by',
        'reply_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'replied_at' => 'datetime',
        'is_spam' => 'boolean',
    ];

    /**
     * Get the user who replied to this contact.
     */
    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by email.
     */
    public function scopeByEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', 'like', '%' . $email . '%');
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeByDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for new contacts.
     */
    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope for read contacts.
     */
    public function scopeRead(Builder $query): Builder
    {
        return $query->where('status', 'read');
    }

    /**
     * Scope for replied contacts.
     */
    public function scopeReplied(Builder $query): Builder
    {
        return $query->where('status', 'replied');
    }

    /**
     * Scope for archived contacts.
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope for spam contacts.
     */
    public function scopeSpam(Builder $query): Builder
    {
        return $query->where('is_spam', true);
    }

    /**
     * Scope for non-spam contacts.
     */
    public function scopeNotSpam(Builder $query): Builder
    {
        return $query->where('is_spam', false);
    }

    /**
     * Get available contact statuses.
     */
    public static function getStatuses(): array
    {
        return [
            'new' => 'New',
            'read' => 'Read',
            'replied' => 'Replied',
            'archived' => 'Archived',
        ];
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'new' => '<span class="badge bg-primary">New</span>',
            'read' => '<span class="badge bg-info">Read</span>',
            'replied' => '<span class="badge bg-success">Replied</span>',
            'archived' => '<span class="badge bg-secondary">Archived</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get spam badge HTML.
     */
    public function getSpamBadgeAttribute(): string
    {
        if ($this->is_spam) {
            return '<span class="badge bg-danger">Spam</span>';
        }

        return '<span class="badge bg-success">Legitimate</span>';
    }

    /**
     * Get formatted created date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at ? formatUserDateTime($this->created_at) : 'Unknown';
    }

    /**
     * Get formatted replied date.
     */
    public function getFormattedRepliedDateAttribute(): string
    {
        return $this->replied_at ? formatUserDateTime($this->replied_at) : 'Not replied';
    }

    /**
     * Get short message preview.
     */
    public function getMessagePreviewAttribute(): string
    {
        return Str::limit($this->message, 100);
    }

    /**
     * Check if contact is new.
     */
    public function isNew(): bool
    {
        return $this->status === 'new';
    }

    /**
     * Check if contact is read.
     */
    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    /**
     * Check if contact is replied.
     */
    public function isReplied(): bool
    {
        return $this->status === 'replied';
    }

    /**
     * Check if contact is archived.
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Mark contact as read.
     */
    public function markAsRead(): bool
    {
        return $this->update(['status' => 'read']);
    }

    /**
     * Mark contact as spam.
     */
    public function markAsSpam(): bool
    {
        return $this->update(['is_spam' => true, 'status' => 'archived']);
    }

    /**
     * Mark contact as not spam.
     */
    public function markAsNotSpam(): bool
    {
        return $this->update(['is_spam' => false]);
    }

    /**
     * Mark contact as replied.
     */
    public function markAsReplied(int $userId, string $replyMessage = null): bool
    {
        return $this->update([
            'status' => 'replied',
            'replied_at' => now(),
            'replied_by' => $userId,
            'reply_message' => $replyMessage,
        ]);
    }

    /**
     * Archive contact.
     */
    public function archive(): bool
    {
        return $this->update(['status' => 'archived']);
    }
}
