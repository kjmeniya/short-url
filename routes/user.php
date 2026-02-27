<?php

use App\Http\Controllers\User\AuthController as UserAuthController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\MyLinkController as UserMyLinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Panel Routes
|--------------------------------------------------------------------------
|
| These routes handle the regular user portal — login, dashboard, and
| user-specific actions. Separate from the admin panel.
|
*/

// ── User Auth (guest-only) ─────────────────────────────────────────────────

Route::prefix('user')->name('user.')->group(function () {

    // Login / Logout
    Route::get('login', [UserAuthController::class, 'showLoginForm'])
        ->name('login')
        ->middleware('guest');

    Route::post('login', [UserAuthController::class, 'login'])
        ->name('login.post')
        ->middleware(['guest', 'throttle.login']);

    // Register (Google Login only)
    Route::get('register', [UserAuthController::class, 'showRegisterForm'])
        ->name('register')
        ->middleware(['guest', 'maintenance']);

    Route::post('logout', [UserAuthController::class, 'logout'])
        ->name('logout');

    // ── Authenticated user area ────────────────────────────────────────────

    Route::middleware(['auth', 'user.role'])->group(function () {

        // Dashboard
        Route::get('dashboard', [UserDashboardController::class, 'index'])
            ->name('dashboard');

        // My Short URLs
        Route::get('links', [UserMyLinkController::class, 'index'])
            ->name('links');

        // Create short link
        Route::get('links/create', [UserMyLinkController::class, 'create'])
            ->name('links.create');

        Route::post('links/store', [UserMyLinkController::class, 'store'])
            ->name('links.store');

        // AJAX slug availability check
        Route::get('links/check-slug', [UserMyLinkController::class, 'checkSlug'])
            ->name('links.check-slug');

        // Edit short link
        Route::get('links/{id}/edit', [UserMyLinkController::class, 'edit'])
            ->name('links.edit');

        // Update short link
        Route::put('links/{id}', [UserMyLinkController::class, 'update'])
            ->name('links.update');

        // Delete own link
        Route::delete('links/{id}', [UserMyLinkController::class, 'destroy'])
            ->name('links.destroy');

        // Toggle link status
        Route::post('links/{id}/toggle', [UserMyLinkController::class, 'toggle'])
            ->name('links.toggle');


        // Profile
        Route::get('profile', [UserDashboardController::class, 'profile'])
            ->name('profile');

        Route::put('profile', [UserDashboardController::class, 'updateProfile'])
            ->name('profile.update');
    });
});
