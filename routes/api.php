<?php

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BlogController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
| All routes are protected by api.throttle middleware which checks if API is enabled
*/

Route::prefix('v1')->name('api.v1.')->middleware(['api.throttle'])->group(function () {

    // ==========================================
    // Public routes (no authentication required)
    // ==========================================

    // Authentication
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
    Route::post('auth/verify-email', [AuthController::class, 'verifyEmail'])->name('auth.verify-email');
    Route::post('auth/resend-verification', [AuthController::class, 'resendVerification'])->name('auth.resend-verification');
    Route::post('auth/two-factor/verify', [AuthController::class, 'verifyTwoFactor'])->name('auth.two-factor.verify');
    Route::post('auth/two-factor/send-code', [AuthController::class, 'sendTwoFactorEmailCode'])->name('auth.two-factor.send-code');

    // API Info
    Route::get('/', [ApiController::class, 'index'])->name('index');
    Route::get('health', [ApiController::class, 'health'])->name('health');

    // Settings routes (public settings only)
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::get('settings/{key}', [SettingController::class, 'show'])->name('settings.show');

    // Blog routes
    Route::prefix('blogs')->name('blogs.')->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/featured', [BlogController::class, 'featured'])->name('featured');
        Route::get('/search', [BlogController::class, 'search'])->name('search');
        Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
    });

    // ==========================================
    // Protected routes (authentication required)
    // ==========================================
    Route::middleware(['auth:sanctum'])->group(function () {

        // Auth routes (session management)
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::post('auth/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
        Route::post('auth/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');

        // Profile routes
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
        Route::post('profile/send-verification', [ProfileController::class, 'sendVerificationEmail'])->name('profile.send-verification');
        Route::post('profile/logout-all', [ProfileController::class, 'logoutAll'])->name('profile.logout-all');

        // Two-Factor Authentication Management (profile)
        Route::get('profile/two-factor', [ProfileController::class, 'twoFactorStatus'])->name('profile.two-factor.status');
        Route::post('profile/two-factor/secret', [ProfileController::class, 'generateTwoFactorSecret'])->name('profile.two-factor.secret');
        Route::post('profile/two-factor/enable', [ProfileController::class, 'enableTwoFactor'])->name('profile.two-factor.enable');
        Route::post('profile/two-factor/disable', [ProfileController::class, 'disableTwoFactor'])->name('profile.two-factor.disable');
        Route::post('profile/two-factor/recovery-codes', [ProfileController::class, 'regenerateRecoveryCodes'])->name('profile.two-factor.recovery-codes');

        // User routes
        Route::apiResource('users', UserController::class);
        Route::get('users/{user}/profile', [UserController::class, 'profile'])->name('users.profile');

        // Notification routes
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
            Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
            Route::get('/stats', [NotificationController::class, 'stats'])->name('stats');
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::post('/delete-all-read', [NotificationController::class, 'deleteAllRead'])->name('delete-all-read');
            Route::post('/delete-all', [NotificationController::class, 'deleteAll'])->name('delete-all');
            Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        });
    });
});

// Fallback for API not found (also check if API is enabled)
Route::fallback(function () {
    if (!api_enabled()) {
        return response()->json([
            'success' => false,
            'message' => 'API is currently disabled.',
            'error' => 'Service Unavailable',
            'code' => 503
        ], 503);
    }

    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found.',
        'error' => 'Not Found',
        'code' => 404
    ], 404);
});
