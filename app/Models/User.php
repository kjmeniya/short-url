<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Notifications\ResetPasswordNotification;
use App\Services\NotificationService;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, CanResetPassword, HasApiTokens;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['role'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'address',
        'date_of_birth',
        'avatar',
        'role_id',
        'is_active',
        'timezone',
        'language',
        'preferences',
        'password_changed_at',
        'force_password_change',
        'deleted_by',
        'login_attempts',
        'last_login_at',
        'last_login_ip',
        'locked_until',
        'two_factor_enabled',
        'two_factor_method',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'two_factor_code',
        'two_factor_code_expires_at',
        'google_id',
        'is_google_user',
        'email_verification_code',
        'email_verification_code_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'preferences' => 'array',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'password_changed_at' => 'datetime',
            'is_active' => 'boolean',
            'force_password_change' => 'boolean',
            'deleted_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_code_expires_at' => 'datetime',
            'is_google_user' => 'boolean',
            'email_verification_code_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user's avatar URL.
     *
     * @return string
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            // Check if it's a URL (Google avatar) or local file
            if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
                return $this->avatar;
            }
            return url($this->avatar);
        }

        return '';
    }

    public function getAvatarAttribute(): string
    {
        $avatar = $this->attributes['avatar'] ?? null;

        if (!$avatar) {
            return '';
        }

        // Google / external avatar
        if (filter_var($avatar, FILTER_VALIDATE_URL)) {
            return $avatar;
        }

        // Local storage avatar
        return 'storage/' . $avatar;
    }


    /**
     * Get user initials for avatar.
     *
     * @return string
     */
    public function getInitialsAttribute(): string
    {
        $nameParts = explode(' ', trim($this->name));
        $initials = '';

        if (count($nameParts) >= 2) {
            // First and last name
            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
        } elseif (count($nameParts) === 1) {
            // Single name - take first two characters
            $initials = strtoupper(substr($nameParts[0], 0, 2));
        } else {
            $initials = 'U'; // Default for User
        }

        return $initials;
    }

    /**
     * Check if user has an avatar image.
     *
     * @return bool
     */
    public function hasAvatar(): bool
    {
        return !empty($this->avatar);
    }

    /**
     * Check if user is a Google user.
     */
    public function isGoogleUser(): bool
    {
        return (bool) $this->is_google_user || !empty($this->google_id);
    }

    /**
     * Check if Google user needs to set a password.
     */
    public function needsPasswordSetup(): bool
    {
        return $this->isGoogleUser() && empty($this->password_changed_at);
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Scope for active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope for verified users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }



    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        if ($this->role_id && $this->role) {
            return in_array($this->role->name, [Role::ADMIN_NAME, Role::SUPER_ADMIN_NAME]);
        }
        return false;
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role_id === Role::SUPER_ADMIN_ID;
    }

    /**
     * Check if user is a regular user.
     */
    public function isUser(): bool
    {
        if ($this->role_id && $this->role) {
            return $this->role->name === Role::USER_NAME;
        }
        return false;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check role-based permissions
        if ($this->role_id && $this->role) {
            return $this->role->hasPermission($permission);
        }

        return false;
    }

    /**
     * Get all permissions for the user.
     */
    public function getAllPermissions(): array
    {
        if ($this->isSuperAdmin()) {
            return Permission::pluck('name')->toArray();
        }

        if ($this->role) {
            return $this->role->permissions()->pluck('name')->toArray();
        }

        return [];
    }

    /**
     * Check if user account is active.
     */
    public function isActiveUser(): bool
    {
        return $this->is_active && (!$this->locked_until || $this->locked_until->isPast());
    }

    /**
     * Record login attempt.
     */
    public function recordLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
            'login_attempts' => 0,
        ]);
    }

    /**
     * Check if user has two-factor authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return (bool) $this->two_factor_enabled;
    }

    /**
     * Check if user has confirmed two-factor authentication setup.
     */
    public function hasTwoFactorConfirmed(): bool
    {
        return $this->hasTwoFactorEnabled() && !is_null($this->two_factor_confirmed_at);
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enableTwoFactor(string $method, ?string $secret, array $recoveryCodes): void
    {
        $this->update([
            'two_factor_enabled' => true,
            'two_factor_method' => $method,
            'two_factor_secret' => $secret ? encrypt($secret) : null,
            'two_factor_recovery_codes' => array_map('encrypt', $recoveryCodes),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disableTwoFactor(): void
    {
        $this->update([
            'two_factor_enabled' => false,
            'two_factor_method' => 'email',
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_code' => null,
            'two_factor_code_expires_at' => null,
        ]);
    }

    /**
     * Get decrypted two-factor secret.
     */
    public function getTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret ? decrypt($this->two_factor_secret) : null;
    }

    /**
     * Get decrypted recovery codes.
     */
    public function getTwoFactorRecoveryCodes(): array
    {
        if (!$this->two_factor_recovery_codes) {
            return [];
        }

        return array_map('decrypt', $this->two_factor_recovery_codes);
    }

    /**
     * Use a recovery code.
     */
    public function useRecoveryCode(string $code): bool
    {
        $recoveryCodes = $this->getTwoFactorRecoveryCodes();

        if (in_array($code, $recoveryCodes)) {
            $remainingCodes = array_diff($recoveryCodes, [$code]);

            $this->update([
                'two_factor_recovery_codes' => array_map('encrypt', array_values($remainingCodes))
            ]);

            return true;
        }

        return false;
    }

    /**
     * Increment login attempts.
     */
    public function incrementLoginAttempts(): void
    {
        $this->increment('login_attempts');

        // Lock account after max failed attempts
        $maxAttempts = max_login_attempts();
        if ($this->login_attempts >= $maxAttempts) {
            $lockoutDuration = lockout_duration();
            $lockedUntil = now()->addMinutes($lockoutDuration);

            $this->update([
                'locked_until' => $lockedUntil
            ]);

            // Send notification to user about account lock
            try {
                $notificationService = app(NotificationService::class);
                $notificationService->sendToUser(
                    $this,
                    'account_locked',
                    'Your Account Has Been Locked',
                    "Your account has been temporarily locked due to multiple failed login attempts. You can try again in {$lockoutDuration} minutes or use the 'Forgot Password' option to reset your password.",
                    [
                        'locked_until' => formatUserDateTime($lockedUntil),
                        'lockout_duration' => $lockoutDuration,
                        'reason' => 'Multiple failed login attempts',
                    ]
                );
            } catch (\Exception $e) {
                Log::error('Failed to send account lock notification: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get the role that belongs to the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user who deleted this user.
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get the email logs sent by this user.
     */
    public function emailLogs(): HasMany
    {
        return $this->hasMany(EmailLog::class);
    }

    /**
     * Get the login logs for this user.
     */
    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    /**
     * Get the blog posts authored by this user.
     */
    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'author_id');
    }

    /**
     * Get the blog posts created by this user.
     */
    public function createdBlogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'created_by');
    }

    /**
     * Get the blog posts last updated by this user.
     */
    public function updatedBlogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'updated_by');
    }


    /**
     * Soft delete the user with tracking.
     */
    public function softDeleteBy(User $deletedBy): bool
    {
        $this->deleted_by = $deletedBy->id;
        $this->save();

        return $this->delete();
    }

    /**
     * Check if current user can see this user in listings.
     */
    public function canBeSeenBy(User $viewer): bool
    {
        // Users cannot see themselves in listings
        if ($this->id === $viewer->id) {
            return false;
        }

        // If viewer doesn't have a role, they can't see anyone
        if (!$viewer->role) {
            return false;
        }

        // If this user doesn't have a role, only super admin can see them
        if (!$this->role) {
            return $viewer->isSuperAdmin();
        }

        // Use role hierarchy to determine visibility
        return $viewer->role->canView($this->role);
    }

    /**
     * Check if current user can manage this user.
     */
    public function canBeManagedBy(User $viewer): bool
    {
        // Users cannot manage themselves
        if ($this->id === $viewer->id) {
            return false;
        }

        // If viewer doesn't have a role, they can't manage anyone
        if (!$viewer->role) {
            return false;
        }

        // If this user doesn't have a role, only super admin can manage them
        if (!$this->role) {
            return $viewer->isSuperAdmin();
        }

        // Use role hierarchy to determine management capability
        return $viewer->role->canManage($this->role);
    }

    /**
     * Get users visible to the current user based on role hierarchy.
     */
    public static function visibleTo(User $viewer)
    {
        $query = static::query();

        // Exclude the viewer themselves
        $query->where('id', '!=', $viewer->id);

        // If viewer doesn't have a role, they can't see anyone
        if (!$viewer->role) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        // Get viewable role IDs based on hierarchy
        $viewableRoleIds = $viewer->role->getViewableRoles()->pluck('id')->toArray();

        if (!empty($viewableRoleIds)) {
            $query->whereIn('role_id', $viewableRoleIds);
        } else {
            // If no viewable roles, return empty result
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    /**
     * Get users manageable by the current user based on role hierarchy.
     */
    public static function manageableBy(User $viewer)
    {
        $query = static::query();

        // Exclude the viewer themselves
        $query->where('id', '!=', $viewer->id);

        // If viewer doesn't have a role, they can't manage anyone
        if (!$viewer->role) {
            return $query->whereRaw('1 = 0'); // Return empty result
        }

        // Get manageable role IDs based on hierarchy
        $manageableRoleIds = $viewer->role->getManageableRoles()->pluck('id')->toArray();

        if (!empty($manageableRoleIds)) {
            $query->whereIn('role_id', $manageableRoleIds);
        } else {
            // If no manageable roles, return empty result
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    /**
     * Scope to get only user's own data.
     */
    public function scopeOwnData($query, User $user)
    {
        return $query->where('id', $user->id);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $link = route('password.reset', ['token' => $token, 'email' => $this->email]);
        app(\App\Services\EmailService::class)->sendPasswordResetEmail(
            $this->email,
            $this->name,
            $link
        );
    }

    /**
     * Get active sessions for this user.
     */
    public function getActiveSessions()
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return DB::table(config('session.table', 'sessions'))
            ->where('user_id', $this->id)
            ->get();
    }

    /**
     * Logout user from all other devices except current.
     */
    public function logoutOtherDevices(?string $currentSessionId = null): int
    {
        if (config('session.driver') !== 'database') {
            return 0;
        }

        $query = DB::table(config('session.table', 'sessions'))
            ->where('user_id', $this->id);

        if ($currentSessionId) {
            $query->where('id', '!=', $currentSessionId);
        }

        return $query->delete();
    }

    /**
     * Logout user from all devices.
     */
    public function logoutAllDevices(): int
    {
        if (config('session.driver') !== 'database') {
            return 0;
        }

        // Clear all sessions
        $deletedSessions = DB::table(config('session.table', 'sessions'))
            ->where('user_id', $this->id)
            ->delete();

        // Clear remember token
        $this->update(['remember_token' => null]);

        return $deletedSessions;
    }

    /**
     * Get the entity's notifications.
     * Overriding default to use custom Notification model with SoftDeletes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }


    /**
     * Determine if the user has verified their email address.
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark the given user's email as verified.
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Get the email address that should be used for verification.
     */
    public function getEmailForVerification(): string
    {
        return $this->email;
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        // This will be handled by our EmailService
        app(\App\Services\EmailService::class)->sendEmailVerification(
            $this->email,
            $this->name,
            $this->generateVerificationUrl()
        );
    }

    /**
     * Generate email verification URL.
     */
    public function generateVerificationUrl(): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'auth.verification.verify',
            \Carbon\Carbon::now()->addHours(24),
            [
                'id' => $this->getKey(),
                'hash' => sha1($this->getEmailForVerification()),
            ]
        );
    }



    /**
     * Get unread notifications count.
     */
    public function getUnreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }



    /**
     * Get recent unread notifications.
     */
    public function getRecentNotifications(int $limit = 10)
    {
        return $this->notifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread notifications.
     */
    public function getUnreadNotifications(int $limit = 10)
    {
        return $this->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
