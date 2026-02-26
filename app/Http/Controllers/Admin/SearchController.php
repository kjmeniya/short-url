<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\LaravelLog;
use App\Models\LoginLog;
use App\Models\Notification;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * Global search functionality
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'results' => [],
                'total' => 0,
                'message' => 'Please enter at least 2 characters'
            ]);
        }

        $results = [];
        $currentUser = Auth::user();

        // Search Users (if user has permission)
        if (hasPermission('admin.users.index.get')) {
            $users = User::visibleTo($currentUser)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('email', 'LIKE', "%{$query}%");

                    // Only search phone if it's not null
                    if (!empty($query)) {
                        $q->orWhere('phone', 'LIKE', "%{$query}%");
                    }
                })
                ->with('role:id,name,display_name')
                ->limit($limit)
                ->get(['id', 'name', 'email', 'avatar', 'phone', 'role_id', 'is_active']);

            foreach ($users as $user) {
                $results[] = [
                    'type' => 'user',
                    'title' => $user->name,
                    'subtitle' => $user->email,
                    'description' => $user->phone ?? ($user->role ? $user->role->display_name : ''),
                    'url' => route('admin.users.show', $user->id),
                    'icon' => 'user',
                    'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                    'status' => $user->is_active ? 'Active' : 'Inactive',
                    'category' => 'Users'
                ];
            }
        }

        // Search Roles (if user has permission)
        if (hasPermission('admin.roles.index.get')) {
            $roles = Role::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('display_name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            })
                ->withCount('users')
                ->limit($limit)
                ->get(['id', 'name', 'display_name', 'description', 'is_active']);

            foreach ($roles as $role) {
                $results[] = [
                    'type' => 'role',
                    'title' => $role->display_name,
                    'subtitle' => $role->name,
                    'description' => $role->description ?? "{$role->users_count} users assigned",
                    'url' => route('admin.roles.show', $role->id),
                    'icon' => 'user-check',
                    'status' => $role->is_active ? 'Active' : 'Inactive',
                    'category' => 'Roles'
                ];
            }
        }

        // Search Permissions (if user has permission)
        if (hasPermission('admin.permissions.index.get')) {
            $permissions = Permission::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('display_name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhere('category', 'LIKE', "%{$query}%");
            })
                ->limit($limit)
                ->get(['id', 'name', 'display_name', 'description', 'category', 'method']);

            foreach ($permissions as $permission) {
                $results[] = [
                    'type' => 'permission',
                    'title' => $permission->display_name,
                    'subtitle' => $permission->name,
                    'description' => $permission->description ?? $permission->category,
                    'url' => route('admin.permissions.show', $permission->id),
                    'icon' => 'shield',
                    'badge' => $permission->method,
                    'category' => 'Permissions'
                ];
            }
        }

        // Search Email Templates (if user has permission)
        if (hasPermission('admin.email-templates.index.get')) {
            $emailTemplates = EmailTemplate::where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('subject', 'LIKE', "%{$query}%")
                    ->orWhere('type', 'LIKE', "%{$query}%");
            })
                ->limit($limit)
                ->get(['id', 'name', 'subject', 'type', 'is_active']);

            foreach ($emailTemplates as $template) {
                $results[] = [
                    'type' => 'email_template',
                    'title' => $template->name,
                    'subtitle' => $template->subject,
                    'description' => 'Type: ' . ucfirst($template->type),
                    'url' => route('admin.email-templates.show', $template->id),
                    'icon' => 'mail',
                    'status' => $template->is_active ? 'Active' : 'Inactive',
                    'category' => 'Email Templates'
                ];
            }
        }

        // Search Email Logs (if user has permission)
        if (hasPermission('admin.email-logs.index.get')) {
            $emailLogs = EmailLog::where(function ($q) use ($query) {
                $q->where('recipient_email', 'LIKE', "%{$query}%")
                    ->orWhere('subject', 'LIKE', "%{$query}%")
                    ->orWhere('status', 'LIKE', "%{$query}%");
            })
                ->limit($limit)
                ->get(['id', 'recipient_email', 'subject', 'status', 'sent_at']);

            foreach ($emailLogs as $log) {
                $results[] = [
                    'type' => 'email_log',
                    'title' => $log->subject,
                    'subtitle' => $log->recipient_email,
                    'description' => "Status: {$log->status}" . ($log->sent_at ? " • Sent: " . formatUserDateTime($log->sent_at) : ''),
                    'url' => route('admin.email-logs.show', $log->id),
                    'icon' => 'send',
                    'badge' => $log->status,
                    'category' => 'Email Logs'
                ];
            }
        }

        // Search Login Logs (if user has permission)
        if (hasPermission('admin.login-logs.index.get')) {
            $loginLogs = LoginLog::where(function ($q) use ($query) {
                $q->where('email', 'LIKE', "%{$query}%")
                    ->orWhere('ip_address', 'LIKE', "%{$query}%")
                    ->orWhere('user_agent', 'LIKE', "%{$query}%")
                    ->orWhere('status', 'LIKE', "%{$query}%");
            })
                ->with('user:id,name')
                ->limit($limit)
                ->get(['id', 'user_id', 'email', 'ip_address', 'status', 'login_at']);

            foreach ($loginLogs as $log) {
                $results[] = [
                    'type' => 'login_log',
                    'title' => $log->user ? $log->user->name : $log->email,
                    'subtitle' => $log->email,
                    'description' => "IP: {$log->ip_address} • Status: {$log->status}" . ($log->login_at ? " • " . formatUserDateTime($log->login_at) : ''),
                    'url' => route('admin.login-logs.show', $log->id),
                    'icon' => 'activity',
                    'badge' => $log->status,
                    'category' => 'Login Logs'
                ];
            }
        }

        // Search Laravel Logs (if user has permission)
        if (hasPermission('admin.laravel-logs.index.get')) {
            $laravelLogs = LaravelLog::where(function ($q) use ($query) {
                $q->where('level', 'LIKE', "%{$query}%")
                    ->orWhere('channel', 'LIKE', "%{$query}%")
                    ->orWhere('message', 'LIKE', "%{$query}%")
                    ->orWhere('environment', 'LIKE', "%{$query}%")
                    ->orWhere('exception_class', 'LIKE', "%{$query}%");
            })
                ->limit($limit)
                ->get(['id', 'level', 'channel', 'message', 'environment', 'logged_at', 'exception_class']);

            foreach ($laravelLogs as $log) {
                $results[] = [
                    'type' => 'laravel_log',
                    'title' => ucfirst($log->level) . ' Log',
                    'subtitle' => $log->channel,
                    'description' => Str::limit($log->message, 100) . " • Environment: {$log->environment}" . ($log->logged_at ? " • " . formatUserDateTime($log->logged_at) : ''),
                    'url' => route('admin.laravel-logs.show', $log->id),
                    'icon' => 'file-text',
                    'badge' => ucfirst($log->level),
                    'category' => 'System Logs'
                ];
            }
        }

        // Search Notifications (if user has permission)
        if (hasPermission('admin.notifications.index.get')) {
            $notifications = Notification::where(function ($q) use ($query) {
                $q->where('type', 'LIKE', "%{$query}%")
                    ->orWhere('data', 'LIKE', "%{$query}%");
            })
                ->limit($limit)
                ->get(['id', 'type', 'data', 'read_at', 'created_at']);

            foreach ($notifications as $notification) {
                $data = $notification->data ?? []; // Already an array in Laravel notifications
                $title = $data['title'] ?? $data['subject'] ?? 'Notification';
                $message = $data['message'] ?? $data['body'] ?? $data['content'] ?? 'No message';

                $results[] = [
                    'type' => 'notification',
                    'title' => $title,
                    'subtitle' => 'System Notification',
                    'description' => Str::limit($message, 100) . " • " . formatUserDateTime($notification->created_at),
                    'url' => route('admin.notifications.show', $notification->id),
                    'icon' => 'bell',
                    'status' => $notification->read_at ? 'Read' : 'Unread',
                    'category' => 'Notifications'
                ];
            }
        }

        // Add navigation/menu items
        $navigationResults = $this->searchNavigation($query);
        $results = array_merge($results, $navigationResults);

        // Sort results by relevance (exact matches first)
        usort($results, function ($a, $b) use ($query) {
            $aExact = stripos($a['title'], $query) === 0 ? 1 : 0;
            $bExact = stripos($b['title'], $query) === 0 ? 1 : 0;
            return $bExact - $aExact;
        });

        // Limit total results
        $results = array_slice($results, 0, $limit);

        return response()->json([
            'success' => true,
            'results' => $results,
            'total' => count($results),
            'query' => $query
        ]);
    }

    /**
     * Search navigation/menu items
     */
    private function searchNavigation($query)
    {
        $navigation = [];

        $menuItems = [
            ['title' => 'Dashboard', 'url' => 'admin.dashboard', 'icon' => 'home', 'permission' => 'admin.dashboard.get'],
            ['title' => 'Users', 'url' => 'admin.users.index', 'icon' => 'users', 'permission' => 'admin.users.index.get'],
            ['title' => 'Roles', 'url' => 'admin.roles.index', 'icon' => 'user-check', 'permission' => 'admin.roles.index.get'],
            ['title' => 'Permissions', 'url' => 'admin.permissions.index', 'icon' => 'shield', 'permission' => 'admin.permissions.index.get'],
            ['title' => 'Blog Posts', 'url' => 'admin.blogs.index', 'icon' => 'edit', 'permission' => 'admin.blogs.index.get'],
            ['title' => 'Pages', 'url' => 'admin.pages.index', 'icon' => 'file-text', 'permission' => 'admin.pages.index.get'],
            ['title' => 'CMS Blocks', 'url' => 'admin.cms-blocks.index', 'icon' => 'layout', 'permission' => 'admin.cms-blocks.index.get'],
            ['title' => 'Menus', 'url' => 'admin.menus.index', 'icon' => 'menu', 'permission' => 'admin.menus.index.get'],
            ['title' => 'Newsletter', 'url' => 'admin.newsletter.index', 'icon' => 'mail-plus', 'permission' => 'admin.newsletter.index.get'],
            ['title' => 'Notifications', 'url' => 'admin.notifications.index', 'icon' => 'bell', 'permission' => 'admin.notifications.index.get'],
            ['title' => 'Analytics', 'url' => 'admin.analytics.index', 'icon' => 'bar-chart', 'permission' => 'admin.analytics.index.get'],
            ['title' => 'Reports', 'url' => 'admin.reports.index', 'icon' => 'pie-chart', 'permission' => 'admin.reports.index.get'],
            ['title' => 'Email Templates', 'url' => 'admin.email-templates.index', 'icon' => 'mail', 'permission' => 'admin.email-templates.index.get'],
            ['title' => 'Email Logs', 'url' => 'admin.email-logs.index', 'icon' => 'send', 'permission' => 'admin.email-logs.index.get'],
            ['title' => 'Login Logs', 'url' => 'admin.login-logs.index', 'icon' => 'activity', 'permission' => 'admin.login-logs.index.get'],
            ['title' => 'System Logs', 'url' => 'admin.laravel-logs.index', 'icon' => 'file-text', 'permission' => 'admin.laravel-logs.index.get'],
            ['title' => 'Settings', 'url' => 'admin.settings.index', 'icon' => 'settings', 'permission' => 'admin.settings.index.get'],
            ['title' => 'Profile', 'url' => 'admin.profile', 'icon' => 'user', 'permission' => 'admin.profile.get'],
        ];

        foreach ($menuItems as $item) {
            if (stripos($item['title'], $query) !== false && hasPermission($item['permission'])) {
                $navigation[] = [
                    'type' => 'navigation',
                    'title' => $item['title'],
                    'subtitle' => 'Navigate to ' . $item['title'],
                    'description' => 'Admin panel navigation',
                    'url' => route($item['url']),
                    'icon' => $item['icon'],
                    'category' => 'Navigation'
                ];
            }
        }

        return $navigation;
    }
}
