<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailLogController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\LaravelLogController;
use App\Http\Controllers\Admin\LoginLogController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ShortUrlController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| All admin panel routes are defined here. These routes are protected by
| authentication, two-factor, maintenance, and permission middleware.
|
*/

Route::prefix('admin')->middleware(['auth', 'two-factor', 'maintenance', 'permission'])->group(function () {
    // Dashboard Routes
    Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('dashboard/refresh', [DashboardController::class, 'refresh'])->name('admin.dashboard.refresh');
    Route::get('dashboard/export', [DashboardController::class, 'export'])->name('admin.dashboard.export');
    Route::get('dashboard/print', [DashboardController::class, 'print'])->name('admin.dashboard.print');

    // Profile Routes
    Route::get('profile', [ProfileController::class, 'show'])->name('admin.profile');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::get('profile/login-history', [ProfileController::class, 'loginHistory'])->name('admin.profile.login-history');
    Route::get('profile/email-history', [ProfileController::class, 'emailHistory'])->name('admin.profile.email-history');

    // Two-Factor Authentication Profile Routes
    Route::post('profile/two-factor/generate-qr', [ProfileController::class, 'generateTwoFactorQR'])->name('admin.profile.two-factor.generate-qr');
    Route::post('profile/two-factor/enable', [ProfileController::class, 'enableTwoFactor'])->name('admin.profile.two-factor.enable');
    Route::post('profile/two-factor/disable', [ProfileController::class, 'disableTwoFactor'])->name('admin.profile.two-factor.disable');
    Route::post('profile/two-factor/regenerate-codes', [ProfileController::class, 'regenerateRecoveryCodes'])->name('admin.profile.two-factor.regenerate-codes');
    Route::post('profile/two-factor/send-email-code', [ProfileController::class, 'sendEmailCode'])->name('admin.profile.two-factor.send-email-code');

    // Google Account Management Routes
    Route::post('profile/google/disconnect/request', [ProfileController::class, 'requestGoogleDisconnect'])->name('admin.profile.google.disconnect.request');
    Route::post('profile/google/disconnect/verify', [ProfileController::class, 'verifyGoogleDisconnect'])->name('admin.profile.google.disconnect.verify');

    // Account Deletion Routes
    Route::post('profile/delete/request', [ProfileController::class, 'requestAccountDeletion'])->name('admin.profile.delete.request');
    Route::post('profile/delete/verify', [ProfileController::class, 'verifyAccountDeletion'])->name('admin.profile.delete.verify');

    // User Management Routes
    Route::resource('users', UserController::class, ['as' => 'admin']);
    Route::get('users/trashed/list', [UserController::class, 'trashed'])->name('admin.users.trashed');
    Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
    Route::delete('users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('admin.users.force-delete');
    Route::get('users/{user}/login-history', [UserController::class, 'loginHistory'])->name('admin.users.login-history');
    Route::get('users/{user}/email-history', [UserController::class, 'emailHistory'])->name('admin.users.email-history');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::post('users/{user}/unlock-account', [UserController::class, 'unlockAccount'])->name('admin.users.unlock-account');

    // User Two-Factor Authentication Routes
    Route::post('users/{user}/two-factor/enable', [UserController::class, 'enableTwoFactor'])->name('admin.users.two-factor.enable');
    Route::post('users/{user}/two-factor/disable', [UserController::class, 'disableTwoFactor'])->name('admin.users.two-factor.disable');
    Route::post('users/{user}/two-factor/generate-qr', [UserController::class, 'generateQrCode'])->name('admin.users.two-factor.generate-qr');
    Route::post('users/{user}/two-factor/send-email-code', [UserController::class, 'sendEmailCode'])->name('admin.users.two-factor.send-email-code');
    Route::post('users/{user}/two-factor/regenerate-codes', [UserController::class, 'regenerateRecoveryCodes'])->name('admin.users.two-factor.regenerate-codes');

    // Role Management Routes
    Route::resource('roles', RoleController::class, ['as' => 'admin']);

    // Permission Management Routes
    Route::get('permissions', [PermissionController::class, 'index'])->name('admin.permissions.index');
    Route::get('permissions/{permission}', [PermissionController::class, 'show'])->name('admin.permissions.show');
    Route::get('permissions-sync', [PermissionController::class, 'sync'])->name('admin.permissions.sync');
    Route::post('permissions-sync', [PermissionController::class, 'syncPermissions'])->name('admin.permissions.sync.process');

    // Blog Management Routes
    Route::get('blogs/stats', [BlogController::class, 'getBlogStats'])->name('admin.blogs.stats');
    Route::post('blogs/bulk-action', [BlogController::class, 'bulkAction'])->name('admin.blogs.bulk-action');
    Route::post('blogs/export', [BlogController::class, 'export'])->name('admin.blogs.export');
    Route::post('blogs/import', [BlogController::class, 'import'])->name('admin.blogs.import');
    Route::resource('blogs', BlogController::class, ['as' => 'admin']);

    // Short URL Management Routes
    Route::post('short-urls/bulk-action', [ShortUrlController::class, 'bulkAction'])->name('admin.short-urls.bulk-action');
    Route::get('short-urls/export', [ShortUrlController::class, 'export'])->name('admin.short-urls.export');
    Route::resource('short-urls', ShortUrlController::class, ['as' => 'admin']);

    // Login Logs Routes
    Route::get('login-logs', [LoginLogController::class, 'index'])->name('admin.login-logs.index');
    Route::get('login-logs/{loginLog}', [LoginLogController::class, 'show'])->name('admin.login-logs.show');
    Route::post('login-logs/{loginLog}/mark-safe', [LoginLogController::class, 'markAsSafe'])->name('admin.login-logs.mark-safe');
    Route::get('login-logs-export', [LoginLogController::class, 'export'])->name('admin.login-logs.export');
    Route::get('login-logs-stats', [LoginLogController::class, 'stats'])->name('admin.login-logs.stats');

    // Contact Routes
    Route::get('contacts', [ContactController::class, 'index'])->name('admin.contacts.index');
    Route::get('contacts/{contact}', [ContactController::class, 'show'])->name('admin.contacts.show');
    Route::post('contacts/{contact}/mark-read', [ContactController::class, 'markAsRead'])->name('admin.contacts.mark-read');
    Route::post('contacts/{contact}/mark-spam', [ContactController::class, 'markAsSpam'])->name('admin.contacts.mark-spam');
    Route::post('contacts/{contact}/mark-not-spam', [ContactController::class, 'markAsNotSpam'])->name('admin.contacts.mark-not-spam');
    Route::post('contacts/{contact}/reply', [ContactController::class, 'reply'])->name('admin.contacts.reply');
    Route::post('contacts/{contact}/archive', [ContactController::class, 'archive'])->name('admin.contacts.archive');
    Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->name('admin.contacts.destroy');
    Route::get('contacts-export', [ContactController::class, 'export'])->name('admin.contacts.export');

    // Search Route
    Route::get('search', [SearchController::class, 'search'])->name('admin.search.get');

    // Email Template Management Routes
    Route::resource('email-templates', EmailTemplateController::class, ['as' => 'admin']);
    Route::post('email-templates/preview', [EmailTemplateController::class, 'preview'])->name('admin.email-templates.preview');

    // Email Log Management Routes
    Route::get('email-logs', [EmailLogController::class, 'index'])->name('admin.email-logs.index');
    Route::get('email-logs/{emailLog}', [EmailLogController::class, 'show'])->name('admin.email-logs.show');
    Route::get('email-logs/{emailLog}/preview', [EmailLogController::class, 'preview'])->name('admin.email-logs.preview');
    Route::post('email-logs/{emailLog}/retry', [EmailLogController::class, 'retry'])->name('admin.email-logs.retry');
    Route::get('email-logs-export', [EmailLogController::class, 'export'])->name('admin.email-logs.export');
    Route::get('email-logs-stats', [EmailLogController::class, 'stats'])->name('admin.email-logs.stats');

    // Laravel Log Management Routes
    Route::get('laravel-logs', [LaravelLogController::class, 'index'])->name('admin.laravel-logs.index');
    Route::get('laravel-logs/{laravelLog}', [LaravelLogController::class, 'show'])->name('admin.laravel-logs.show');
    Route::post('laravel-logs/parse', [LaravelLogController::class, 'parse'])->name('admin.laravel-logs.parse');
    Route::get('laravel-logs-export', [LaravelLogController::class, 'export'])->name('admin.laravel-logs.export');
    Route::get('laravel-logs-stats', [LaravelLogController::class, 'stats'])->name('admin.laravel-logs.stats');
    Route::get('laravel-logs-download', [LaravelLogController::class, 'downloadLogFile'])->name('admin.laravel-logs.download');

    // Notification Routes
    Route::prefix('notifications')->name('admin.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/navbar', [NotificationController::class, 'navbar'])->name('navbar');
        Route::get('/count', [NotificationController::class, 'count'])->name('count');
        Route::get('/trashed', [NotificationController::class, 'trashed'])->name('trashed');
        Route::get('/export', [NotificationController::class, 'export'])->name('export');
        Route::post('/import', [NotificationController::class, 'import'])->name('import');
        Route::post('/bulk-action', [NotificationController::class, 'bulkAction'])->name('bulk-action');

        // Send notification routes
        Route::get('/send', [NotificationController::class, 'create'])->name('send');
        Route::post('/send', [NotificationController::class, 'send'])->name('send.post');

        Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read'); // Alias for mark-as-read
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all'); // Alias for mark-all-as-read
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [NotificationController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [NotificationController::class, 'forceDelete'])->name('force-delete');
    });

    // Analytics Routes
    Route::prefix('analytics')->name('admin.analytics.')->group(function () {
        Route::get('live', [AnalyticsController::class, 'live'])->name('live');
        Route::get('page-views', [AnalyticsController::class, 'pageViews'])->name('page-views');
    });

    // Settings Routes
    Route::prefix('settings')->name('admin.settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::get('/{group}', [SettingController::class, 'showGroup'])->name('group');
        Route::post('/', [SettingController::class, 'store'])->name('store');
        Route::get('/setting/{setting}', [SettingController::class, 'show'])->name('show');
        Route::put('/setting/{setting}', [SettingController::class, 'update'])->name('update');
        Route::delete('/setting/{setting}', [SettingController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-update', [SettingController::class, 'updateBulk'])->name('bulk-update');
        Route::post('/verify-password', [SettingController::class, 'verifyPassword'])->name('verify-password');
        Route::post('/test-smtp', [SettingController::class, 'testSmtp'])->name('test-smtp');
        Route::post('/send-test-email', [SettingController::class, 'sendTestEmail'])->name('send-test-email');
        Route::post('/reset-defaults', [SettingController::class, 'resetToDefaults'])->name('reset-defaults');
        Route::post('/logout-other-devices', [SettingController::class, 'logoutOtherDevices'])->name('logout-other-devices');
        Route::post('/logout-all-users', [SettingController::class, 'logoutAllUsers'])->name('logout-all-users');
        Route::post('/download-database', [SettingController::class, 'downloadDatabase'])->name('download-database');
    });
});

// Internal Analytics Routes (for Socket.IO server)
// These routes are excluded from CSRF protection in bootstrap/app.php
Route::prefix('admin')->group(function () {
    Route::post('live/page-visit', [AnalyticsController::class, 'pageVisit'])->name('admin.internal.page-visit');
});
