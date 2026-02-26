<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class LaravelLog extends Model
{
    protected $fillable = [
        'level',
        'channel',
        'message',
        'context',
        'extra',
        'file_path',
        'line_number',
        'environment',
        'log_month',
        'log_date',
        'logged_at',
        'exception_class',
        'stack_trace',
        'request_id',
        'user_id',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'metadata',
        'is_processed',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'metadata' => 'array',
        'is_processed' => 'boolean',
    ];

    protected $dates = [
        'logged_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Log levels with their display names and colors.
     */
    public static function getLevels(): array
    {
        return [
            'emergency' => ['name' => 'Emergency', 'color' => 'danger'],
            'alert' => ['name' => 'Alert', 'color' => 'danger'],
            'critical' => ['name' => 'Critical', 'color' => 'danger'],
            'error' => ['name' => 'Error', 'color' => 'danger'],
            'warning' => ['name' => 'Warning', 'color' => 'warning'],
            'notice' => ['name' => 'Notice', 'color' => 'info'],
            'info' => ['name' => 'Info', 'color' => 'primary'],
            'debug' => ['name' => 'Debug', 'color' => 'secondary'],
        ];
    }

    /**
     * Get available channels.
     */
    public static function getChannels(): array
    {
        return [
            'laravel' => 'Laravel',
            'single' => 'Single',
            'daily' => 'Daily',
            'slack' => 'Slack',
            'syslog' => 'Syslog',
            'errorlog' => 'Error Log',
            'custom' => 'Custom',
        ];
    }

    /**
     * Get available environments.
     */
    public static function getEnvironments(): array
    {
        return [
            'local' => 'Local',
            'staging' => 'Staging',
            'production' => 'Production',
            'testing' => 'Testing',
        ];
    }

    /**
     * Get level badge HTML.
     */
    public function getLevelBadgeAttribute(): string
    {
        $levels = self::getLevels();
        $level = $levels[$this->level] ?? ['name' => ucfirst($this->level), 'color' => 'secondary'];

        return '<span class="badge bg-' . $level['color'] . '">' . $level['name'] . '</span>';
    }

    /**
     * Get channel badge HTML.
     */
    public function getChannelBadgeAttribute(): string
    {
        $channels = self::getChannels();
        $channelName = $channels[$this->channel] ?? ucfirst($this->channel);

        return '<span class="badge bg-info">' . $channelName . '</span>';
    }

    /**
     * Get environment badge HTML.
     */
    public function getEnvironmentBadgeAttribute(): string
    {
        $color = match($this->environment) {
            'production' => 'success',
            'staging' => 'warning',
            'local' => 'primary',
            'testing' => 'info',
            default => 'secondary'
        };

        return '<span class="badge bg-' . $color . '">' . ucfirst($this->environment) . '</span>';
    }

    /**
     * Get formatted message with truncation.
     */
    public function getFormattedMessageAttribute(): string
    {
        $message = strip_tags($this->message);
        return strlen($message) > 100 ? substr($message, 0, 100) . '...' : $message;
    }

    /**
     * Get message preview for listing.
     */
    public function getMessagePreviewAttribute(): string
    {
        $message = strip_tags($this->message);
        return '<strong>' . (strlen($message) > 50 ? substr($message, 0, 50) . '...' : $message) . '</strong>';
    }

    /**
     * Get context data as formatted JSON.
     */
    public function getFormattedContextAttribute(): ?string
    {
        if (empty($this->context)) {
            return null;
        }

        $context = is_string($this->context) ? json_decode($this->context, true) : $this->context;
        return $context ? json_encode($context, JSON_PRETTY_PRINT) : null;
    }

    /**
     * Check if log is an error level.
     */
    public function getIsErrorAttribute(): bool
    {
        return in_array($this->level, ['emergency', 'alert', 'critical', 'error']);
    }

    /**
     * Check if log is a warning level.
     */
    public function getIsWarningAttribute(): bool
    {
        return $this->level === 'warning';
    }

    /**
     * Scope for filtering by level.
     */
    public function scopeLevel(Builder $query, string $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for filtering by channel.
     */
    public function scopeChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope for filtering by environment.
     */
    public function scopeEnvironment(Builder $query, string $environment): Builder
    {
        return $query->where('environment', $environment);
    }

    /**
     * Scope for filtering by month.
     */
    public function scopeMonth(Builder $query, string $month): Builder
    {
        return $query->where('log_month', $month);
    }

    /**
     * Scope for error levels only.
     */
    public function scopeErrors(Builder $query): Builder
    {
        return $query->whereIn('level', ['emergency', 'alert', 'critical', 'error']);
    }

    /**
     * Scope for warnings only.
     */
    public function scopeWarnings(Builder $query): Builder
    {
        return $query->where('level', 'warning');
    }

    /**
     * Scope for recent logs.
     */
    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('logged_at', '>=', Carbon::now()->subHours($hours));
    }

    /**
     * Get user relationship if user_id exists.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
