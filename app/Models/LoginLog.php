<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class LoginLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'email',
        'name',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
        'status',
        'type',
        'location',
        'country',
        'city',
        'failure_reason',
        'metadata',
        'login_at',
        'logout_at',
        'session_duration',
        'is_suspicious',
        'session_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'is_suspicious' => 'boolean',
    ];

    /**
     * Get the user that this login log belongs to.
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
     * Scope for filtering by user email.
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
     * Scope for successful logins.
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed logins.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->whereIn('status', ['failed', 'blocked', 'locked']);
    }

    /**
     * Scope for suspicious activities.
     */
    public function scopeSuspicious(Builder $query): Builder
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Get available login statuses.
     */
    public static function getStatuses(): array
    {
        return [
            'success' => 'Success',
            'failed' => 'Failed',
            'blocked' => 'Blocked',
            'locked' => 'Account Locked',
        ];
    }

    /**
     * Get available login types.
     */
    public static function getTypes(): array
    {
        return [
            'login' => 'Login',
            'logout' => 'Logout',
            'password_reset' => 'Password Reset',
            'account_locked' => 'Account Locked',
        ];
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'success' => '<span class="badge bg-success">Success</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
            'blocked' => '<span class="badge bg-warning">Blocked</span>',
            'locked' => '<span class="badge bg-dark">Account Locked</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get type badge HTML.
     */
    public function getTypeBadgeAttribute(): string
    {
        $badges = [
            'login' => '<span class="badge bg-primary">Login</span>',
            'logout' => '<span class="badge bg-secondary">Logout</span>',
            'password_reset' => '<span class="badge bg-warning">Password Reset</span>',
            'account_locked' => '<span class="badge bg-danger">Account Locked</span>',
        ];

        return $badges[$this->type] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Check if login was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if login failed.
     */
    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'blocked', 'locked']);
    }

    /**
     * Get formatted login date.
     */
    public function getFormattedLoginDateAttribute(): string
    {
        return $this->login_at ? formatUserDateTime($this->login_at) : 'Not logged';
    }

    /**
     * Get device info summary.
     */
    public function getDeviceInfoAttribute(): string
    {
        $parts = array_filter([
            $this->browser,
            $this->platform,
            $this->device_type
        ]);

        return implode(' • ', $parts) ?: 'Unknown Device';
    }

    /**
     * Get location summary.
     */
    public function getLocationSummaryAttribute(): string
    {
        if ($this->city && $this->country) {
            return $this->city . ', ' . $this->country;
        }

        return $this->country ?: $this->location ?: 'Unknown Location';
    }

    /**
     * Get session duration in human readable format.
     */
    public function getSessionDurationHumanAttribute(): string
    {
        if ($this->session_duration) {
            return format_session_duration($this->session_duration);
        }

        // If session is active (no logout time) and was successful, calculate duration so far
        if ($this->status === 'success' && $this->login_at && !$this->logout_at) {
            $duration = $this->login_at->diffInMinutes(now());
            return 'Active (' . format_session_duration($duration) . ')';
        }

        return 'N/A';
    }
}
