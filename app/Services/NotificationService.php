<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected SettingsService $settingsService;
    protected EmailService $emailService;

    public function __construct(SettingsService $settingsService, EmailService $emailService)
    {
        $this->settingsService = $settingsService;
        $this->emailService = $emailService;
    }

    /**
     * Check if notifications are enabled.
     */
    protected function isEnabled(): bool
    {
        return notifications_enabled();
    }

    /**
     * Check if email notifications are enabled.
     */
    protected function isEmailEnabled(): bool
    {
        return email_notifications_enabled();
    }

    /**
     * Get admin notification email address.
     */
    protected function getAdminNotificationEmail(): string
    {
        return admin_notification_email();
    }

    /**
     * Get notification frequency setting.
     */
    protected function getNotificationFrequency(): string
    {
        return notification_frequency();
    }

    /**
     * Send email notification to admin.
     */
    protected function sendAdminEmailNotification(string $type, string $title, string $message, array $data = []): void
    {
        if (!$this->isEmailEnabled()) {
            return;
        }

        $adminEmail = $this->getAdminNotificationEmail();
        if (empty($adminEmail)) {
            return;
        }

        // Only send instant notifications if frequency is 'instant'
        if ($this->getNotificationFrequency() !== 'instant') {
            // For daily/weekly, notifications would be batched (future implementation)
            return;
        }

        try {
            $this->emailService->sendTemplateEmail('admin-notification', $adminEmail, [
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => $data,
                'app_name' => site_name(),
                'app_url' => app_url(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to send admin notification email: ' . $e->getMessage());
        }
    }
    /**
     * Notification types for different admin actions.
     */
    const TYPES = [
        // User Management
        'user_created' => 'User Created',
        'user_updated' => 'User Updated',
        'user_deleted' => 'User Deleted',
        'user_restored' => 'User Restored',
        'user_role_changed' => 'User Role Changed',
        'user_status_changed' => 'User Status Changed',
        'user_registered' => 'User Registered',
        'google_user_registered' => 'Google User Registered',

        // Role & Permission Management
        'role_created' => 'Role Created',
        'role_updated' => 'Role Updated',
        'role_deleted' => 'Role Deleted',
        'permission_updated' => 'Permissions Updated',

        // Content Management
        'blog_created' => 'Blog Post Created',
        'blog_updated' => 'Blog Post Updated',
        'blog_published' => 'Blog Post Published',
        'blog_deleted' => 'Blog Post Deleted',
        'page_created' => 'Page Created',
        'page_updated' => 'Page Updated',
        'page_published' => 'Page Published',
        'page_deleted' => 'Page Deleted',
        'cms_content_updated' => 'CMS Content Updated',

        // Email & Communication
        'email_template_created' => 'Email Template Created',
        'email_template_updated' => 'Email Template Updated',
        'email_template_deleted' => 'Email Template Deleted',
        'bulk_email_sent' => 'Bulk Email Sent',

        // System & Settings
        'settings_updated' => 'System Settings Updated',
        'menu_updated' => 'Menu Updated',
        'system_backup_created' => 'System Backup Created',
        'system_maintenance' => 'System Maintenance',

        // Security & Authentication
        'login_failed_attempts' => 'Multiple Failed Login Attempts',
        'password_reset_requested' => 'Password Reset Requested',
        'two_factor_enabled' => 'Two-Factor Authentication Enabled',
        'two_factor_disabled' => 'Two-Factor Authentication Disabled',

        // Analytics & Reports
        'report_generated' => 'Report Generated',
        'analytics_milestone' => 'Analytics Milestone Reached',

        // Newsletter Management
        'newsletter_subscribed' => 'Newsletter Subscription',
        'newsletter_unsubscribed' => 'Newsletter Unsubscription',
        'newsletter_updated' => 'Newsletter Updated',
        'newsletter_status_changed' => 'Newsletter Status Changed',

        // Contact Management
        'contact_received' => 'Contact Message Received',
        'contact_submitted' => 'Contact Form Submitted',
        'contact_status_changed' => 'Contact Status Changed',

        // General System
        'system_error' => 'System Error',
        'system_warning' => 'System Warning',
        'system_info' => 'System Information',

        // Custom notification types
        'custom_notification' => 'Custom Notification',
    ];

    /**
     * Get all notification types.
     *
     * @return array
     */
    public function getNotificationTypes(): array
    {
        $types = self::TYPES;
        asort($types); // Sort alphabetically by value (display name)
        return $types;
    }

    /**
     * Send notification to specific user.
     */
    public function sendToUser(User $user, string $type, string $title, string $message, array $data = []): void
    {
        // Check if notifications are enabled
        if (!$this->isEnabled()) {
            return;
        }

        $notificationData = [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $data['icon'] ?? $this->getIconForType($type),
            'color' => $data['color'] ?? $this->getColorForType($type),
            'url' => $data['url'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'user_name' => $data['user_name'] ?? null,
            'action_by' => $data['action_by'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'timestamp' => $data['timestamp'] ?? now()->toISOString(),
        ];

        // Create notification directly in database
        $user->notifications()->create([
            'id' => \Illuminate\Support\Str::uuid(),
            'type' => 'App\Notifications\DatabaseNotification',
            'data' => $notificationData,
            'read_at' => null,
        ]);
    }

    /**
     * Send notification to multiple users.
     */
    public function sendToUsers($users, string $type, string $title, string $message, array $data = []): void
    {
        // Convert Collection to array if needed
        if ($users instanceof \Illuminate\Database\Eloquent\Collection) {
            $users = $users->all();
        }

        foreach ($users as $user) {
            $this->sendToUser($user, $type, $title, $message, $data);
        }
    }

    /**
     * Send notification to users based on role.
     */
    public function sendToRole(string $roleName, string $type, string $title, string $message, array $data = []): void
    {
        $users = User::whereHas('role', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->get();

        $this->sendToUsers($users, $type, $title, $message, $data);
    }

    /**
     * Send notification to all admin users.
     */
    public function sendToAdmins(string $type, string $title, string $message, array $data = []): void
    {
        $admins = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['admin', 'super_admin']);
        })->get();

        $this->sendToUsers($admins, $type, $title, $message, $data);

        // Also send email notification to admin notification email for important events
        $importantTypes = [
            'security_alert',
            'login_failed_attempts',
            'system_error',
            'system_warning',
            'contact_received',
            'contact_submitted',
            'user_registered',
            'google_user_registered',
        ];

        if (in_array($type, $importantTypes)) {
            $this->sendAdminEmailNotification($type, $title, $message, $data);
        }

        // Send real-time notification via Socket.IO
        // This provides instant browser notifications for connected admins
        send_admin_notification(
            $title,
            $message,
            $this->mapColorToSocketType($this->getColorForType($type)),
            [
                'icon' => $data['icon'] ?? $this->getIconForType($type),
                'url' => $data['url'] ?? null,
                'notification_type' => $type,
            ]
        );
    }

    /**
     * Send notification to super admins only.
     */
    public function sendToSuperAdmins(string $type, string $title, string $message, array $data = []): void
    {
        $this->sendToRole('super_admin', $type, $title, $message, $data);
    }

    /**
     * Send notification to all users.
     */
    public function sendToAllUsers(string $type, string $title, string $message, array $data = []): void
    {
        $users = User::where('is_active', true)->get();
        $this->sendToUsers($users, $type, $title, $message, $data);
    }

    /**
     * Get icon for notification type.
     */
    private function getIconForType(string $type): string
    {
        $icons = [
            'user_created' => 'user-plus',
            'user_updated' => 'user-check',
            'user_deleted' => 'user-x',
            'user_restored' => 'user-check',
            'user_role_changed' => 'shield',
            'user_status_changed' => 'toggle-left',
            'role_created' => 'shield-plus',
            'role_updated' => 'shield-check',
            'role_deleted' => 'shield-x',
            'permission_updated' => 'key',
            'blog_created' => 'file-plus',
            'blog_updated' => 'file-edit',
            'blog_published' => 'send',
            'blog_deleted' => 'file-x',
            'page_created' => 'file-plus',
            'page_updated' => 'file-edit',
            'page_published' => 'send',
            'page_deleted' => 'file-x',
            'cms_content_updated' => 'edit',
            'email_template_created' => 'mail-plus',
            'email_template_updated' => 'mail-check',
            'email_template_deleted' => 'mail-x',
            'bulk_email_sent' => 'send',
            'settings_updated' => 'settings',
            'menu_updated' => 'menu',
            'system_backup_created' => 'database',
            'system_maintenance' => 'tool',
            'login_failed_attempts' => 'alert-triangle',
            'password_reset_requested' => 'key',
            'two_factor_enabled' => 'shield-check',
            'two_factor_disabled' => 'shield-x',
            'report_generated' => 'bar-chart',
            'analytics_milestone' => 'trending-up',
            'newsletter_subscribed' => 'mail-plus',
            'newsletter_unsubscribed' => 'mail-minus',
            'newsletter_updated' => 'edit',
            'newsletter_status_changed' => 'edit',
            'contact_received' => 'mail',
            'contact_submitted' => 'message-circle',
            'contact_status_changed' => 'edit',
            'system_error' => 'alert-circle',
            'system_warning' => 'alert-triangle',
            'system_info' => 'info',
        ];

        return $icons[$type] ?? 'bell';
    }

    /**
     * Get color for notification type.
     */
    private function getColorForType(string $type): string
    {
        $colors = [
            'user_created' => 'success',
            'user_updated' => 'info',
            'user_deleted' => 'danger',
            'user_restored' => 'success',
            'user_role_changed' => 'warning',
            'user_status_changed' => 'info',
            'role_created' => 'success',
            'role_updated' => 'info',
            'role_deleted' => 'danger',
            'permission_updated' => 'warning',
            'blog_created' => 'success',
            'blog_updated' => 'info',
            'blog_published' => 'primary',
            'blog_deleted' => 'danger',
            'page_created' => 'success',
            'page_updated' => 'info',
            'page_published' => 'primary',
            'page_deleted' => 'danger',
            'cms_content_updated' => 'info',
            'email_template_created' => 'success',
            'email_template_updated' => 'info',
            'email_template_deleted' => 'danger',
            'bulk_email_sent' => 'primary',
            'settings_updated' => 'warning',
            'menu_updated' => 'info',
            'system_backup_created' => 'success',
            'system_maintenance' => 'warning',
            'login_failed_attempts' => 'danger',
            'password_reset_requested' => 'warning',
            'two_factor_enabled' => 'success',
            'two_factor_disabled' => 'warning',
            'report_generated' => 'info',
            'analytics_milestone' => 'success',
            'newsletter_subscribed' => 'success',
            'newsletter_unsubscribed' => 'warning',
            'newsletter_updated' => 'info',
            'newsletter_status_changed' => 'info',
            'contact_received' => 'primary',
            'contact_submitted' => 'primary',
            'contact_status_changed' => 'info',
            'system_error' => 'danger',
            'system_warning' => 'warning',
            'system_info' => 'info',
        ];

        return $colors[$type] ?? 'primary';
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(string $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Soft delete notification.
     */
    public function deleteNotification(string $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if ($notification) {
            // Set deleted_by before soft deleting
            $notification->update(['deleted_by' => Auth::id()]);
            $notification->delete();
            return true;
        }

        return false;
    }

    /**
     * Get notification statistics for a user.
     */
    public function getNotificationStats(User $user): array
    {
        // Super admins see all notifications stats
        if ($user->isSuperAdmin()) {
            return [
                'total' => Notification::count(),
                'unread' => Notification::whereNull('read_at')->count(),
                'today' => Notification::whereDate('created_at', today())->count(),
                'this_week' => Notification::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            ];
        }

        // Regular users see only their own notifications
        return [
            'total' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
            'today' => $user->notifications()->whereDate('created_at', today())->count(),
            'this_week' => $user->notifications()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];
    }

    /**
     * Generate notification for user actions.
     */
    public function notifyUserAction(string $action, User $user, array $details = []): void
    {
        $actionBy = Auth::user();
        if (!$actionBy || !$this->isUserAdmin($actionBy)) {
            return; // Only generate notifications for admin actions
        }

        $messages = [
            'created' => "User '{$user->name}' has been created by {$actionBy->name}.",
            'updated' => "User '{$user->name}' has been updated by {$actionBy->name}.",
            'deleted' => "User '{$user->name}' has been deleted by {$actionBy->name}.",
            'restored' => "User '{$user->name}' has been restored by {$actionBy->name}.",
            'role_changed' => "User '{$user->name}' role has been changed by {$actionBy->name}.",
            'status_changed' => "User '{$user->name}' status has been changed by {$actionBy->name}.",
        ];

        $message = $messages[$action] ?? "User '{$user->name}' has been {$action} by {$actionBy->name}.";

        $this->sendToAdmins(
            "user_{$action}",
            ucfirst(str_replace('_', ' ', $action)) . ' User',
            $message,
            array_merge($details, [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'action_by' => $actionBy->name,
                'url' => route('admin.users.show', $user->id)
            ])
        );
    }

    /**
     * Generate notification for login events.
     */
    public function notifyLoginEvent(string $email, bool $successful, ?string $ip = null): void
    {
        if (!$successful) {
            // Find user and notify them + admins about failed login
            $user = User::where('email', $email)->first();

            if ($user) {
                $this->sendToUser(
                    $user,
                    'login_failed',
                    'Failed Login Attempt',
                    "Failed login attempt detected from IP: {$ip}",
                    [
                        'ip_address' => $ip,
                        'attempt_time' => now()->toISOString(),
                        'icon' => 'alert-triangle',
                        'color' => 'danger'
                    ]
                );
            }

            // Notify admins
            $this->sendToAdmins(
                'security_alert',
                'Failed Login Attempt',
                "Failed login attempt for email '{$email}' from IP: {$ip}",
                [
                    'email' => $email,
                    'ip_address' => $ip,
                    'icon' => 'shield-alert',
                    'color' => 'warning',
                    'url' => route('admin.users.index')
                ]
            );
        }
    }

    /**
     * Generate notification for system events.
     */
    public function notifySystemEvent(string $type, string $title, string $message, array $data = []): void
    {
        $this->sendToAdmins(
            $type,
            $title,
            $message,
            array_merge($data, [
                'timestamp' => now()->toISOString(),
                'url' => route('admin.dashboard')
            ])
        );
    }

    /**
     * Generate notification for bulk operations.
     */
    public function notifyBulkOperation(string $operation, int $count, string $entity): void
    {
        $actionBy = Auth::user();

        $this->sendToAdmins(
            'bulk_operation',
            'Bulk Operation Completed',
            "Bulk {$operation} completed for {$count} {$entity}(s) by {$actionBy->name}.",
            [
                'operation' => $operation,
                'count' => $count,
                'entity' => $entity,
                'performed_by' => $actionBy->name,
                'icon' => 'layers',
                'color' => 'info'
            ]
        );
    }

    /**
     * Generate notification for newsletter subscription.
     */
    public function notifyNewsletterSubscription(string $email, ?string $ip = null): void
    {
        $this->sendToAdmins(
            'newsletter_subscribed',
            'New Newsletter Subscription',
            "New newsletter subscription from {$email}",
            [
                'email' => $email,
                'ip_address' => $ip,
                'icon' => 'mail-plus',
                'color' => 'success',
                'url' => route('admin.newsletter.index')
            ]
        );
    }

    /**
     * Generate notification for newsletter unsubscription.
     */
    public function notifyNewsletterUnsubscription(string $email, ?string $ip = null): void
    {
        $this->sendToAdmins(
            'newsletter_unsubscribed',
            'Newsletter Unsubscription',
            "Newsletter unsubscription from {$email}",
            [
                'email' => $email,
                'ip_address' => $ip,
                'icon' => 'mail-minus',
                'color' => 'warning',
                'url' => route('admin.newsletter.index')
            ]
        );
    }

    /**
     * Generate notification for newsletter status update by admin.
     */
    public function notifyNewsletterStatusUpdate(string $email, string $oldStatus, string $newStatus): void
    {
        $actionBy = Auth::user();
        if (!$actionBy || !$this->isUserAdmin($actionBy)) {
            return; // Only generate notifications for admin actions
        }

        $this->sendToAdmins(
            'newsletter_status_changed',
            'Newsletter Status Updated',
            "Newsletter status for {$email} changed from {$oldStatus} to {$newStatus} by {$actionBy->name}",
            [
                'email' => $email,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'action_by' => $actionBy->name,
                'icon' => 'edit',
                'color' => 'info',
                'url' => route('admin.newsletter.index')
            ]
        );
    }

    /**
     * Generate notification for contact form submission.
     */
    public function notifyContactSubmission(string $name, string $email, string $subject, ?string $ip = null): void
    {
        $this->sendToAdmins(
            'contact_submitted',
            'New Contact Form Submission',
            "New contact form submission from {$name} ({$email}): {$subject}",
            [
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'ip_address' => $ip,
                'icon' => 'message-circle',
                'color' => 'primary',
                'url' => route('admin.contacts.index')
            ]
        );
    }

    /**
     * Generate notification for contact status update by admin.
     */
    public function notifyContactStatusUpdate(string $email, string $name, string $oldStatus, string $newStatus): void
    {
        $actionBy = Auth::user();
        if (!$actionBy || !$this->isUserAdmin($actionBy)) {
            return; // Only generate notifications for admin actions
        }

        $this->sendToAdmins(
            'contact_status_changed',
            'Contact Status Updated',
            "Contact status for {$name} ({$email}) changed from {$oldStatus} to {$newStatus} by {$actionBy->name}",
            [
                'name' => $name,
                'email' => $email,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'action_by' => $actionBy->name,
                'icon' => 'edit',
                'color' => 'info',
                'url' => route('admin.contacts.index')
            ]
        );
    }

    /**
     * Map Laravel notification color to Socket.IO type
     * Laravel uses: primary, success, danger, warning, info, secondary
     * Socket.IO/SweetAlert uses: success, info, warning, error
     */
    private function mapColorToSocketType(string $color): string
    {
        $mapping = [
            'success' => 'success',
            'danger' => 'error',
            'warning' => 'warning',
            'info' => 'info',
            'primary' => 'info',
            'secondary' => 'info',
        ];

        return $mapping[$color] ?? 'info';
    }

    /**
     * Check if user is admin.
     */
    private function isUserAdmin(User $user): bool
    {
        return $user->role && in_array($user->role->name, ['admin', 'super_admin']);
    }
}
