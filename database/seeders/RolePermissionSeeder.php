<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles by ID
        $superAdminRole = Role::find(Role::SUPER_ADMIN_ID);
        $adminRole = Role::find(Role::ADMIN_ID);
        $userRole = Role::find(Role::USER_ID);

        if (!$userRole || !$adminRole || !$superAdminRole) {
            $this->command->error('Roles not found. Please run RoleSeeder first.');
            return;
        }

        // Define default permissions for each role
        $this->assignUserPermissions($userRole);
        $this->assignAdminPermissions($adminRole);
        $this->assignSuperAdminPermissions($superAdminRole);

        $this->command->info('Default permissions assigned successfully.');
    }

    /**
     * Assign default permissions to User role
     */
    private function assignUserPermissions(Role $userRole): void
    {
        $userPermissions = [
            // Profile permissions (full access to own profile)
            'admin.profile.get',
            'admin.profile.edit.get',
            'admin.profile.update.put',
            'admin.profile.login-history.get',
            'admin.profile.email-history.get',
            'admin.profile.delete.request.post',
            'admin.profile.delete.verify.post',
            'admin.profile.google.disconnect.request.post',
            'admin.profile.google.disconnect.verify.post',

            // Two-Factor Authentication permissions (full access to own 2FA)
            'admin.profile.two-factor.generate-qr.post',
            'admin.profile.two-factor.enable.post',
            'admin.profile.two-factor.disable.post',
            'admin.profile.two-factor.regenerate-codes.post',
            'admin.profile.two-factor.send-email-code.post',

            // Dashboard access (view only)
            'admin.dashboard.get',
            'admin.dashboard.refresh.get',

            // Notification permissions (basic access - own notifications only)
            'admin.notifications.navbar.get',
            'admin.notifications.count.get',
            'admin.notifications.read.post',
            'admin.notifications.show.get',
            'admin.notifications.index.get',
            'admin.notifications.mark-as-read.post',
            'admin.notifications.mark-all-as-read.post',
            'admin.notifications.read-all.post',
            'admin.notifications.destroy.delete',

            // Search functionality
            'admin.search.get',
        ];

        $this->assignPermissions($userRole, $userPermissions, 'User');
    }

    /**
     * Assign default permissions to Admin role
     */
    private function assignAdminPermissions(Role $adminRole): void
    {
        $adminPermissions = [
            // Profile permissions (full access to own profile)
            'admin.profile.get',
            'admin.profile.edit.get',
            'admin.profile.update.put',
            'admin.profile.login-history.get',
            'admin.profile.email-history.get',
            'admin.profile.delete.request.post',
            'admin.profile.delete.verify.post',
            'admin.profile.google.disconnect.request.post',
            'admin.profile.google.disconnect.verify.post',

            // Two-Factor Authentication permissions (full access to own 2FA)
            'admin.profile.two-factor.generate-qr.post',
            'admin.profile.two-factor.enable.post',
            'admin.profile.two-factor.disable.post',
            'admin.profile.two-factor.regenerate-codes.post',
            'admin.profile.two-factor.send-email-code.post',

            // Dashboard access (full)
            'admin.dashboard.get',
            'admin.dashboard.refresh.get',
            'admin.dashboard.export.get',
            'admin.dashboard.print.get',

            // User management (full access)
            'admin.users.index.get',
            'admin.users.show.get',
            'admin.users.create.get',
            'admin.users.store.post',
            'admin.users.edit.get',
            'admin.users.update.put',
            'admin.users.update.patch',
            'admin.users.destroy.delete',
            'admin.users.force-delete.delete',
            'admin.users.restore.post',
            'admin.users.toggle-status.post',
            'admin.users.unlock-account.post',
            'admin.users.trashed.get',
            'admin.users.login-history.get',
            'admin.users.email-history.get',

            // User Two-Factor Authentication management (full access)
            'admin.users.two-factor.enable.post',
            'admin.users.two-factor.disable.post',
            'admin.users.two-factor.generate-qr.post',
            'admin.users.two-factor.send-email-code.post',
            'admin.users.two-factor.regenerate-codes.post',

            // Search functionality
            'admin.search.get',

            // Notification permissions (full access)
            'admin.notifications.index.get',
            'admin.notifications.show.get',
            'admin.notifications.navbar.get',
            'admin.notifications.count.get',
            'admin.notifications.export.get',
            'admin.notifications.import.post',
            'admin.notifications.bulk-action.post',
            'admin.notifications.read-all.post',
            'admin.notifications.read.post',
            'admin.notifications.destroy.delete',
            'admin.notifications.force-delete.delete',
            'admin.notifications.restore.post',
            'admin.notifications.trashed.get',
            'admin.notifications.mark-as-read.post',
            'admin.notifications.mark-all-as-read.post',
            'admin.notifications.send.get',
            'admin.notifications.send.post',

            // Analytics (full access)
            'admin.analytics.live.get',
            'admin.analytics.page-views.get',

            // Email logs (full access)
            'admin.email-logs.index.get',
            'admin.email-logs.show.get',
            'admin.email-logs.export.get',
            'admin.email-logs.preview.get',
            'admin.email-logs.retry.post',
            'admin.email-logs.stats.get',

            // Login logs (full access)
            'admin.login-logs.index.get',
            'admin.login-logs.show.get',
            'admin.login-logs.export.get',
            'admin.login-logs.mark-safe.post',
            'admin.login-logs.stats.get',

            // Laravel logs (full access)
            'admin.laravel-logs.index.get',
            'admin.laravel-logs.show.get',
            'admin.laravel-logs.download.get',
            'admin.laravel-logs.export.get',
            'admin.laravel-logs.parse.post',
            'admin.laravel-logs.stats.get',

            // Email templates (full access)
            'admin.email-templates.index.get',
            'admin.email-templates.show.get',
            'admin.email-templates.create.get',
            'admin.email-templates.store.post',
            'admin.email-templates.edit.get',
            'admin.email-templates.update.put',
            'admin.email-templates.update.patch',
            'admin.email-templates.destroy.delete',
            'admin.email-templates.preview.post',

            // Contact management (full access)
            'admin.contacts.index.get',
            'admin.contacts.show.get',
            'admin.contacts.mark-read.post',
            'admin.contacts.mark-spam.post',
            'admin.contacts.mark-not-spam.post',
            'admin.contacts.reply.post',
            'admin.contacts.archive.post',
            'admin.contacts.destroy.delete',
            'admin.contacts.export.get',

            // Blog management (full access)
            'admin.blogs.index.get',
            'admin.blogs.show.get',
            'admin.blogs.create.get',
            'admin.blogs.store.post',
            'admin.blogs.edit.get',
            'admin.blogs.update.put',
            'admin.blogs.update.patch',
            'admin.blogs.destroy.delete',
            'admin.blogs.stats.get',
            'admin.blogs.bulk-action.post',
            'admin.blogs.export.post',
            'admin.blogs.import.post',

            // Settings (view and limited edit)
            'admin.settings.index.get',
            'admin.settings.group.get',
            'admin.settings.show.get',
            'admin.settings.send-test-email.post',
            'admin.settings.test-smtp.post',
            'admin.settings.verify-password.post',
        ];

        $this->assignPermissions($adminRole, $adminPermissions, 'Admin');
    }

    /**
     * Assign all permissions to Super Admin role
     */
    private function assignSuperAdminPermissions(Role $superAdminRole): void
    {
        // Super admin gets all permissions
        $allPermissions = Permission::pluck('id')->toArray();

        if (!empty($allPermissions)) {
            $superAdminRole->permissions()->sync($allPermissions);
            $this->command->info("Assigned all " . count($allPermissions) . " permissions to Super Admin role.");
        }
    }

    /**
     * Helper method to assign permissions to a role
     */
    private function assignPermissions(Role $role, array $permissionNames, string $roleName): void
    {
        $permissions = Permission::whereIn('name', $permissionNames)->pluck('id')->toArray();

        if (!empty($permissions)) {
            $role->permissions()->sync($permissions);
            $this->command->info("Assigned " . count($permissions) . " permissions to {$roleName} role.");
        } else {
            $this->command->warn("No permissions found for {$roleName} role. Make sure permissions are seeded first.");
        }
    }
}
