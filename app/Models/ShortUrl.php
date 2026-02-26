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
        'title',
        'custom_alias',
        'status',
        'clicks',
        'expires_at',
        'password',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'clicks'     => 'integer',
        'expires_at' => 'datetime',
    ];

    protected $dates = ['expires_at', 'deleted_at'];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
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

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function isPasswordProtected(): bool
    {
        return !empty($this->password);
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
}
