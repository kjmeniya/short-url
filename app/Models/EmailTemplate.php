<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'type',
        'variables',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this template.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the email logs that used this template.
     */
    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    /**
     * Scope for active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for templates by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for templates by slug.
     */
    public function scopeBySlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Find template by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->where('is_active', true)->first();
    }

    /**
     * Get available template types.
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
     * Replace variables in the template body.
     */
    public function renderBody(array $data = []): string
    {
        $body = $this->body;

        foreach ($data as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return $body;
    }

    /**
     * Replace variables in the template subject.
     */
    public function renderSubject(array $data = []): string
    {
        $subject = $this->subject;

        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }

        return $subject;
    }
}
