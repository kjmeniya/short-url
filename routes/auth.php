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
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('auth.register')->middleware(['guest', 'maintenance']);
    Route::post('register', [AuthController::class, 'register'])->name('auth.register.post')->middleware('guest');
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');

    // Google Authentication Routes
    Route::get('google', [AuthController::class, 'redirectToGoogle'])->name('auth.google')->middleware('guest');
    Route::get('google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback')->middleware('guest');

    // Email Verification Routes
    Route::get('email/verify', [EmailVerificationController::class, 'notice'])->name('auth.verification.notice');
    Route::post('email/verify', [AuthController::class, 'verifyEmail'])->name('auth.verification.verify.post');
    Route::post('email/resend', [AuthController::class, 'resendVerification'])->name('auth.verification.resend.post');

    // Legacy link-based verification (kept for backward compatibility)
    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('auth.verification.verify');
    Route::post('email/verification-notification', [EmailVerificationController::class, 'send'])->name('auth.verification.send')->middleware('auth');
    Route::post('email/resend-verification', [EmailVerificationController::class, 'resend'])->name('auth.verification.resend');
    Route::get('email/verification-check', function () {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        return response()->json(['verified' => $user && $user->hasVerifiedEmail()]);
    })->name('auth.verification.check');

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
