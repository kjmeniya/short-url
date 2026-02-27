<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\TwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Here are all the authentication-related routes including login, register,
| password reset, email verification, and two-factor authentication.
|
*/

Route::group(['prefix' => 'auth'], function () {
    // Login & Registration Routes
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('auth.login')->middleware('guest');
    Route::post('login', [AuthController::class, 'login'])->name('auth.login.post')->middleware(['guest', 'throttle.login']);
    // Registration is moved to the user portal (routes/user.php).
    // Admin creation is done by existing admins dynamically, not openly.
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Google Authentication Routes
    Route::get('google', [AuthController::class, 'redirectToGoogle'])->name('auth.google')->middleware('guest');
    Route::get('google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback')->middleware('guest');



    // Password Reset Routes
    Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('auth.forgot-password');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('auth.forgot-password.post');
    Route::post('check-google-user', [AuthController::class, 'checkGoogleUser'])->name('auth.check-google-user');
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('auth.reset-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password.post');

    // Two-Factor Authentication Routes
    Route::get('two-factor/verify', [TwoFactorController::class, 'show'])->name('auth.two-factor.verify');
    Route::post('two-factor/verify', [TwoFactorController::class, 'verify'])->name('auth.two-factor.verify.post');
    Route::get('two-factor/recovery', [TwoFactorController::class, 'showRecovery'])->name('auth.two-factor.recovery');
    Route::post('two-factor/recovery', [TwoFactorController::class, 'verifyRecovery'])->name('auth.two-factor.recovery.post');
});
