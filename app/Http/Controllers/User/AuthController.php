<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\LoginTrackingService;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show the user login form.
     */
    public function showLoginForm()
    {
        return view('user.auth.login');
    }

    /**
     * Show the user registration form.
     */
    public function showRegisterForm()
    {
        return view('user.auth.register');
    }



    /**
     * Handle user login.
     */
    public function login(Request $request, LoginTrackingService $loginTracker, TwoFactorAuthService $twoFactorService)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:' . password_min_length(),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember    = $request->has('remember');
        $email       = $credentials['email'];

        $user = \App\Models\User::where('email', $email)->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {

            // Maintenance mode — only super_admin can log in
            if (maintenance_mode()) {
                $isSuperAdmin = $user->role && $user->role->name === 'super_admin';
                if (!$isSuperAdmin) {
                    $loginTracker->logBlockedLogin($email, 'Maintenance mode active', $user);
                    return redirect()->back()
                        ->withErrors(['email' => 'The site is currently under maintenance. Please try again later.'])
                        ->withInput($request->except('password'));
                }
            }

            // Check if account is active
            if (!$user->isActiveUser()) {
                if ($user->locked_until && $user->locked_until->isFuture()) {
                    $mins = (int) ceil($user->locked_until->diffInMinutes(now(), true));
                    $errorMessage = "Your account is temporarily locked. Please try again in {$mins} minute(s) or reset your password.";
                } else {
                    $errorMessage = 'Your account has been deactivated. Please contact support.';
                }

                $loginTracker->logBlockedLogin($email, 'Account inactive/locked', $user);
                return redirect()->back()
                    ->withErrors(['email' => $errorMessage])
                    ->withInput($request->except('password'));
            }

            // Admins should use the admin login instead
            if ($user->isAdmin() || $user->isSuperAdmin()) {
                return redirect()->back()
                    ->withErrors(['email' => 'Administrator accounts should use the Admin Login page.'])
                    ->withInput($request->except('password'));
            }

            // Record login
            $user->recordLogin($request->ip());
            $loginTracker->logSuccessfulLogin($user, $email);
            \App\Http\Middleware\ThrottleLoginAttempts::clearLoginAttempts($email, $request->ip());

            Auth::login($user, $remember);
            $request->session()->regenerate();



            // 2FA check
            if ($twoFactorService->requiresTwoFactorVerification($user)) {
                session()->forget('two_factor_verified');
                if ($user->two_factor_method === 'email') {
                    $twoFactorService->sendEmailCode($user);
                }
                return redirect()->route('auth.two-factor.verify');
            }

            session(['two_factor_verified' => true]);

            return redirect()->route('user.dashboard')
                ->with('success', 'Welcome back, ' . $user->name . '!');

        } else {
            // Failed login
            if ($user) {
                $user->incrementLoginAttempts();
                $freshUser = $user->fresh();

                if ($freshUser->locked_until && $freshUser->locked_until->isFuture()) {
                    $mins = (int) ceil($freshUser->locked_until->diffInMinutes(now(), true));
                    $errorMessage = "Too many failed attempts. Account locked for {$mins} minute(s).";
                    $loginTracker->logAccountLocked($user);
                } else {
                    $maxAttempts      = max_login_attempts();
                    $remainingAttempts = $maxAttempts - $freshUser->login_attempts;
                    if ($remainingAttempts <= 2 && $remainingAttempts > 0) {
                        $errorMessage = "Incorrect password. {$remainingAttempts} attempt(s) remaining before lockout.";
                    } else {
                        $errorMessage = 'The password you entered is incorrect. Please try again.';
                    }
                    $loginTracker->logFailedLogin($email, 'Invalid password', $user);
                }
            } else {
                $errorMessage = "No account found with that email address.";
                $loginTracker->logFailedLogin($email, 'User not found');
            }

            return redirect()->back()
                ->withErrors(['email' => $errorMessage])
                ->withInput($request->except('password'));
        }
    }

    /**
     * Log out the user and redirect to user login.
     */
    public function logout(Request $request, LoginTrackingService $loginTracker)
    {
        $loginTracker->logLogout();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
