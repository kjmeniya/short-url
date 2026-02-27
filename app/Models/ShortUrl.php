<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class ShortUrl extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'original_url',
        'mobile_url',
        'desktop_url',
        'tablet_url',
        'timezone',
        'office_days',
        'office_start_time',
        'office_end_time',
        'office_url',
        'after_hours_url',
        'og_title',
        'og_description',
        'og_image',
        'title',
        'custom_alias',
        'status',
        'clicks',
        'max_clicks',
        'expires_at',
        'password',
        'is_private',
        'is_24h_story',
        'is_one_time',
        'disabled_at',
        'created_by',
        'updated_by',
        'guest_id',
        'redirect_delay',
    ];

    protected $casts = [
        'clicks'     => 'integer',
        'max_clicks' => 'integer',
        'expires_at'   => 'datetime',
        'disabled_at'  => 'datetime',
        'is_private'   => 'boolean',
        'is_24h_story' => 'boolean',
        'is_one_time'  => 'boolean',
        'office_days'  => 'array',
    ];

    protected $dates = ['expires_at', 'disabled_at', 'deleted_at'];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function clickLogs()
    {
        return $this->hasMany(ShortUrlClick::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
                ->orWhere('original_url', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")
                ->orWhere('custom_alias', 'like', "%{$search}%");
        });
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getShortUrlAttribute(): string
    {
        $identifier = $this->custom_alias ?: $this->code;
        return url($identifier);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isClickLimitReached(): bool
    {
        return $this->max_clicks !== null && $this->clicks >= $this->max_clicks;
    }

    /**
     * Percentage of click quota used (0–100), or null when no limit is set.
     */
    public function clickUsagePercent(): ?int
    {
        if ($this->max_clicks === null || $this->max_clicks === 0) {
            return null;
        }
        return (int) min(100, round(($this->clicks / $this->max_clicks) * 100));
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired() && !$this->isClickLimitReached() && !$this->isOneTimeUsed();
    }

    public function isPasswordProtected(): bool
    {
        return !empty($this->password);
    }

    public function isPrivate(): bool
    {
        return (bool) $this->is_private;
    }

    public function is24hStory(): bool
    {
        return (bool) $this->is_24h_story;
    }

    public function isOneTime(): bool
    {
        return (bool) $this->is_one_time;
    }

    public function isOneTimeUsed(): bool
    {
        return $this->isOneTime() && $this->disabled_at !== null;
    }

    // ── Static helpers ────────────────────────────────────────────────────────

    public static function generateUniqueCode(int $length = 6): string
    {
        do {
            $code = Str::random($length);
        } while (static::withTrashed()->where('code', $code)->exists());

        return $code;
    }

    public static function getStatusOptions(): array
    {
        return [
            'active'   => 'Active',
            'inactive' => 'Inactive',
            'expired'  => 'Expired',
        ];
    }

    public static function getStats(): array
    {
        return [
            'total'      => static::count(),
            'active'     => static::where('status', 'active')->count(),
            'inactive'   => static::where('status', 'inactive')->count(),
            'expired'    => static::where('status', 'expired')->count(),
            'total_clicks' => static::sum('clicks'),
            'this_month' => static::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    // ── Boot ──────────────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = static::generateUniqueCode();
            }
        });
    }

    public function ipBlocks()
    {
        return $this->hasMany(IpBlock::class);
    }
}
