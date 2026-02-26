<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'name',
        'description',
        'value',
        'type',
        'options',
        'group',
        'sort_order',
        'is_public',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get setting value with proper type casting.
     */
    public function getTypedValueAttribute()
    {
        if (is_null($this->value)) {
            return null;
        }

        return match ($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($this->value) ? (float) $this->value : 0,
            'email' => filter_var($this->value, FILTER_VALIDATE_EMAIL) ? $this->value : null,
            'url' => filter_var($this->value, FILTER_VALIDATE_URL) ? $this->value : null,
            default => $this->value,
        };
    }

    /**
     * Scope for active settings.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for public settings.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for settings by group.
     */
    public function scopeByGroup(Builder $query, string $group): Builder
    {
        return $query->where('group', $group);
    }

    /**
     * Get all settings as key-value pairs.
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('system_settings', 3600, function () {
            return self::active()
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    /**
     * Get setting value by key.
     */
    public static function get(string $key, $default = null)
    {
        $settings = self::getAllSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Set setting value by key.
     */
    public static function set(string $key, $value): bool
    {
        $setting = self::where('key', $key)->first();

        if ($setting) {
            $setting->update(['value' => $value]);
            Cache::forget('system_settings');
            return true;
        }

        return false;
    }

    /**
     * Get available setting groups.
     */
    public static function getGroups(): array
    {
        return [
            'general' => 'General Settings',
            'email' => 'Email Settings',
            'smtp' => 'SMTP Settings',
            'storage' => 'Storage Settings',
            'security' => 'Security Settings',
            'appearance' => 'Appearance Settings',
            'system' => 'System Settings',
            'notifications' => 'Notification Settings',
            'api' => 'API Settings',
            'mobile_app' => 'Mobile App Settings',
            'authentication' => 'Authentication Settings',
        ];
    }

    /**
     * Get dynamic setting groups from database with metadata
     */
    public static function getDynamicGroups(): array
    {
        $groups = self::select('group')
            ->where('is_active', true)
            ->groupBy('group')
            ->orderBy('group')
            ->pluck('group')
            ->toArray();

        $groupMetadata = [
            'general' => [
                'name' => 'General',
                'icon' => 'home',
                'description' => 'Basic site configuration'
            ],
            'email' => [
                'name' => 'Email',
                'icon' => 'mail',
                'description' => 'Email configuration and settings'
            ],
            'smtp' => [
                'name' => 'SMTP',
                'icon' => 'send',
                'description' => 'SMTP server configuration'
            ],
            'storage' => [
                'name' => 'Storage',
                'icon' => 'hard-drive',
                'description' => 'File storage settings'
            ],
            'security' => [
                'name' => 'Security',
                'icon' => 'shield',
                'description' => 'Security and authentication'
            ],
            'appearance' => [
                'name' => 'Appearance',
                'icon' => 'palette',
                'description' => 'Theme and UI settings'
            ],
            'system' => [
                'name' => 'System',
                'icon' => 'cpu',
                'description' => 'System configuration'
            ],
            'notifications' => [
                'name' => 'Notifications',
                'icon' => 'bell',
                'description' => 'Notification settings'
            ],
            'api' => [
                'name' => 'API',
                'icon' => 'code',
                'description' => 'API configuration'
            ],
            'mobile_app' => [
                'name' => 'Mobile App',
                'icon' => 'smartphone',
                'description' => 'Mobile application settings'
            ],
            'authentication' => [
                'name' => 'Authentication',
                'icon' => 'shield',
                'description' => 'Authentication and OAuth settings'
            ],
        ];

        $result = [];
        foreach ($groups as $group) {
            if (isset($groupMetadata[$group])) {
                $result[$group] = $groupMetadata[$group];
            } else {
                // Fallback for unknown groups
                $result[$group] = [
                    'name' => ucfirst($group),
                    'icon' => 'settings',
                    'description' => ucfirst($group) . ' settings'
                ];
            }
        }

        return $result;
    }

    /**
     * Get available setting types.
     */
    public static function getTypes(): array
    {
        return [
            'text' => 'Text',
            'textarea' => 'Textarea',
            'number' => 'Number',
            'boolean' => 'Boolean',
            'select' => 'Select',
            'email' => 'Email',
            'url' => 'URL',
            'password' => 'Password',
            'color' => 'Color',
            'file' => 'File',
        ];
    }
}
