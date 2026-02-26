<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\LoginTrackingService;
use App\Services\TwoFactorAuthService;
use App\Services\GoogleAuthService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the admin login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login', [
            'schema_type' => 'login',
            'schema_data' => [],
        ]);
    }

    /**
     * Handle admin login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\LoginTrackingService  $loginTracker
     * @param  \App\Services\TwoFactorAuthService  $twoFactorService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request, LoginTrackingService $loginTracker, TwoFactorAuthService $twoFactorService)
    {
        // Check IP restriction before processing login
        $allowedIps = login_ip_restriction();
        if (!empty($allowedIps) && !in_array($request->ip(), $allowedIps)) {
            $loginTracker->logBlockedLogin($request->input('email', 'unknown'), 'IP not allowed: ' . $request->ip());
            return redirect()->back()
                ->withErrors(['email' => 'Access denied from your IP address.'])
                ->withInput($request->except('password'));
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:' . password_min_length(),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');
        $email = $credentials['email'];

        // Find user and check if they have admin role
        $user = User::where('email', $email)->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Check maintenance mode - only super_admin can login during maintenance
            if (maintenance_mode()) {
                $isSuperAdmin = $user->role && $user->role->name === 'super_admin';
                if (!$isSuperAdmin) {
                    $loginTracker->logBlockedLogin($email, 'Maintenance mode active - non-admin login blocked', $user);
                    return redirect()->back()
                        ->withErrors(['email' => 'The site is currently under maintenance. Only administrators can login at this time.'])
                        ->withInput($request->except('password'));
                }
            }

            // Check if account is active
            if (!$user->isActiveUser()) {
                if ($user->locked_until && $user->locked_until->isFuture()) {
                    $lockTimeRemaining = (int) ceil($user->locked_until->diffInMinutes(now(), true));
                    $timeMessage = $lockTimeRemaining > 0 ? "{$lockTimeRemaining} minute" . ($lockTimeRemaining > 1 ? 's' : '') : 'less than 1 minute';
                    $reason = 'Account is locked until ' . formatUserDateTime($user->locked_until);
                    $errorMessage = "Your account is temporarily locked due to multiple failed login attempts. Please try again in {$timeMessage} or use the 'Forgot Password' option.";
                } else {
                    $reason = 'Account has been deactivated';
                    $errorMessage = 'Your account has been deactivated by an administrator. Please contact support for assistance.';
                }

                $loginTracker->logBlockedLogin($email, $reason, $user);
                return redirect()->back()
                    ->withErrors(['email' => $errorMessage])
                    ->withInput($request->except('password'));
            }

            // Record successful login in both systems
            $user->recordLogin($request->ip());
            $loginTracker->logSuccessfulLogin($user, $email);

            // Clear login attempts on successful login
            \App\Http\Middleware\ThrottleLoginAttempts::clearLoginAttempts($email, $request->ip());

            // Login the user
            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Check if email is verified (skip for Google users as they are auto-verified)
            if (!$user->hasVerifiedEmail() && !$user->is_google_user) {
                // Generate and send OTP
                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $user->update([
                    'email_verification_code' => $otp,
                    'email_verification_code_expires_at' => now()->addMinutes(10),
                ]);

                try {
                    $emailService = app(EmailService::class);
                    $emailService->sendEmailVerificationOTP($user->email, $user->name, $otp);
                } catch (\Exception $e) {
                    Log::error('Failed to send email verification OTP: ' . $e->getMessage());
                }

                // Store email in session for verification page
                session(['verification_email' => $user->email]);

                return redirect()->route('auth.verification.notice')
                    ->with('info', 'Please verify your email address to continue. We have sent a verification code to your email.');
            }

            // Check if user has two-factor authentication enabled
            if ($twoFactorService->requiresTwoFactorVerification($user)) {
                // Clear the two-factor verification flag
                session()->forget('two_factor_verified');

                // Send email code if using email method
                if ($user->two_factor_method === 'email') {
                    $twoFactorService->sendEmailCode($user);
                }

                // Redirect to two-factor verification
                return redirect()->route('auth.two-factor.verify');
            }

            // Mark as verified if no 2FA required
            session(['two_factor_verified' => true]);

            // Check if force password change is enabled and user needs to change password
            $forcePasswordDays = force_password_change_days();
            if ($forcePasswordDays > 0 && $user->password_changed_at) {
                $daysSinceChange = $user->password_changed_at->diffInDays(now());
                if ($daysSinceChange >= $forcePasswordDays) {
                    session(['force_password_change' => true]);
                    return redirect()->route('admin.profile.security')
                        ->with('warning', 'Your password has expired. Please change your password to continue.');
                }
            } elseif ($forcePasswordDays > 0 && !$user->password_changed_at) {
                // User never changed password, require change
                session(['force_password_change' => true]);
                return redirect()->route('admin.profile.security')
                    ->with('warning', 'Please set a new password to continue.');
            }

            // Redirect based on user role
            $intendedUrl = $request->session()->get('url.intended');

            if ($intendedUrl) {
                // If user was trying to access a specific page, redirect there
                return redirect()->intended();
            }

            // Default redirects based on role
            if ($user->isSuperAdmin() || $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            } else {
                // Regular users go to their profile or a user dashboard
                return redirect()->route('admin.profile');
            }
        } else {
            // Handle different types of login failures with appropriate messages
            if ($user) {
                // User exists but password is wrong
                $user->incrementLoginAttempts();
                $freshUser = $user->fresh();

                // Check if account is now locked after this attempt
                if ($freshUser->locked_until && $freshUser->locked_until->isFuture()) {
                    $lockTimeRemaining = (int) ceil($freshUser->locked_until->diffInMinutes(now(), true));
                    $timeMessage = $lockTimeRemaining > 0 ? "{$lockTimeRemaining} minute" . ($lockTimeRemaining > 1 ? 's' : '') : 'less than 1 minute';
                    $errorMessage = "Too many failed login attempts. Your account has been temporarily locked for {$timeMessage}. Please try again later or use the 'Forgot Password' option.";
                    $loginTracker->logAccountLocked($user);
                } else {
                    // Check remaining attempts before lockout
                    $maxAttempts = max_login_attempts();
                    $remainingAttempts = $maxAttempts - $freshUser->login_attempts;
                    if ($remainingAttempts <= 2 && $remainingAttempts > 0) {
                        $errorMessage = "Incorrect password. You have {$remainingAttempts} attempt" . ($remainingAttempts > 1 ? 's' : '') . " remaining before your account is temporarily locked.";
                    } else {
                        $errorMessage = "The password you entered is incorrect. Please check your password and try again.";
                    }
                    $loginTracker->logFailedLogin($email, 'Invalid password', $user);
                }
            } else {
                // User doesn't exist
                $errorMessage = "We couldn't find an account with that email address. Please check your email or create a new account.";
                $loginTracker->logFailedLogin($email, 'User not found');
            }

            return redirect()->back()
                ->withErrors(['email' => $errorMessage])
                ->withInput($request->except('password'));
        }
    }

    /**
     * Show the admin register form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegisterForm()
    {
        return view('auth.register', [
            'schema_type' => 'register',
            'schema_data' => [],
        ]);
    }

    /**
     * Handle admin register request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        $minPasswordLength = password_min_length();

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'unique:users,email',
                new \App\Rules\NotDisposableEmail(),
            ],
            'password' => [
                'required',
                'string',
                'min:' . $minPasswordLength,
                'confirmed',
            ],
            'password_confirmation' => [
                'required',
                'string',
                'min:' . $minPasswordLength,
            ],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => Role::USER_ID,
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP in user table (expires in 10 minutes)
        $user->update([
            'email_verification_code' => $otp,
            'email_verification_code_expires_at' => now()->addMinutes(10),
        ]);

        // Send verification OTP via email
        try {
            $emailService = app(EmailService::class);
            $emailService->sendEmailVerificationOTP($user->email, $user->name, $otp);
        } catch (\Exception $e) {
            Log::error('Failed to send email verification OTP: ' . $e->getMessage());
        }

        // Notify admins
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->sendToAdmins(
                'user_registered',
                'New User Registered',
                "New user '{$user->name}' has registered and is pending email verification",
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'registration_method' => 'Standard Registration',
                    'status' => 'Pending Email Verification',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification: ' . $e->getMessage());
        }

        // Store email in session for verification page
        session(['verification_email' => $user->email]);

        return redirect()
            ->route('auth.verification.notice')
            ->with('success', 'Registration successful! Please check your email for the verification code.');
    }

    /**
     * Verify email with OTP code
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withErrors(['email' => 'User not found.'])
                ->withInput();
        }

        if ($user->hasVerifiedEmail()) {
            // If already verified, just log them in
            Auth::login($user, true);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')
                ->with('info', 'Email is already verified. Welcome back!');
        }

        // Check if OTP is valid
        if ($user->email_verification_code !== $request->code) {
            return back()
                ->withErrors(['code' => 'Invalid verification code.'])
                ->withInput();
        }

        // Check if OTP has expired
        if ($user->email_verification_code_expires_at && now()->isAfter($user->email_verification_code_expires_at)) {
            return back()
                ->withErrors(['code' => 'Verification code has expired. Please request a new one.'])
                ->withInput();
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        // Clear verification code
        $user->update([
            'email_verification_code' => null,
            'email_verification_code_expires_at' => null,
        ]);

        // Clear session
        session()->forget('verification_email');

        // Auto-login the user
        Auth::login($user, true);
        $request->session()->regenerate();

        // Record successful login
        $user->recordLogin($request->ip());

        // Redirect based on user role
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Email verified successfully! Welcome to your dashboard.');
        } else {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Email verified successfully! Welcome to your dashboard.');
        }
    }

    /**
     * Resend email verification OTP
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return back()
                ->with('info', 'Email is already verified.');
        }

        // Generate new 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Update OTP in user table (expires in 10 minutes)
        $user->update([
            'email_verification_code' => $otp,
            'email_verification_code_expires_at' => now()->addMinutes(10),
        ]);

        // Send verification OTP via email
        try {
            $emailService = app(EmailService::class);
            $emailService->sendEmailVerificationOTP($user->email, $user->name, $otp);
        } catch (\Exception $e) {
            Log::error('Failed to resend email verification OTP: ' . $e->getMessage());
            return back()
                ->withErrors(['email' => 'Failed to send verification code. Please try again.'])
                ->withInput();
        }

        return back()
            ->with('success', 'Verification code sent to your email.');
    }

    /**
     * Handle admin logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\LoginTrackingService  $loginTracker
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request, LoginTrackingService $loginTracker)
    {
        // Log the logout before clearing the session
        $loginTracker->logLogout();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    /**
     * Show the forgot password form.
     *
     * @return \Illuminate\View\View
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password', [
            'schema_type' => 'webpage',
            'schema_data' => [
                'title' => 'Forgot Password',
                'description' => 'Reset your password to regain access to your account.',
            ],
        ]);
    }

    /**
     * Send password reset link to user's email.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'This email address is not registered in our system.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if user is admin and active
        $user = User::where('email', $request->email)->first();
        if (!$user || !$user->isAdmin() || !$user->isActiveUser()) {
            return redirect()->back()
                ->withErrors(['email' => 'No active admin account found with this email address.'])
                ->withInput();
        }

        // Check if user is a Google user
        if ($user->isGoogleUser()) {
            return redirect()->back()
                ->withErrors(['email' => 'This account is linked with Google. Please use "Continue with Google" to sign in, or contact support if you need assistance.'])
                ->withInput();
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? redirect()->back()->with('status', __($status))
            : redirect()->back()->withErrors(['email' => __($status)]);
    }

    /**
     * Check if an email belongs to a Google user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkGoogleUser(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        return response()->json([
            'is_google_user' => $user ? $user->isGoogleUser() : false
        ]);
    }

    /**
     * Show the reset password form.
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => null, // Email not needed for security reasons
            'schema_type' => 'webpage',
            'schema_data' => [
                'title' => 'Reset Password',
                'description' => 'Create a new password for your account.',
            ],
        ]);
    }

    /**
     * Reset the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(Request $request)
    {
        $minPasswordLength = password_min_length();

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'min:' . $minPasswordLength, 'confirmed'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'password_changed_at' => now(),
                    'force_password_change' => false,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('auth.login')->with('status', 'Your password has been reset successfully. You can now log in with your new password.')
            : redirect()->back()->withErrors(['password' => 'Unable to reset password. The reset link may have expired or is invalid.']);
    }

    /**
     * Redirect to Google OAuth.
     *
     * @param  \App\Services\GoogleAuthService  $googleAuth
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle(GoogleAuthService $googleAuth)
    {
        if (!$googleAuth->isEnabled()) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Google authentication is not enabled.']);
        }

        $state = csrf_token();
        session(['google_auth_state' => $state]);

        return redirect($googleAuth->getAuthUrl($state));
    }

    /**
     * Handle Google OAuth callback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\GoogleAuthService  $googleAuth
     * @param  \App\Services\LoginTrackingService  $loginTracker
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback(Request $request, GoogleAuthService $googleAuth, LoginTrackingService $loginTracker)
    {
        if (!$googleAuth->isEnabled()) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Google authentication is not enabled.']);
        }

        // Verify state parameter
        if ($request->state !== session('google_auth_state')) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Invalid authentication state.']);
        }

        // Clear state from session
        session()->forget('google_auth_state');

        if ($request->has('error')) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'Google authentication was cancelled.']);
        }

        if (!$request->has('code')) {
            return redirect()->route('auth.login')
                ->withErrors(['error' => 'No authorization code received from Google.']);
        }

        // Exchange code for access token
        $tokenData = $googleAuth->getAccessToken($request->code);
        if (!$tokenData) {
            return redirect()->route('auth.login')
                ->withErrors(['email' => 'Google authentication failed. Please try again or use email/password login.']);
        }

        // Get user info from Google
        $googleUser = $googleAuth->getUserInfo($tokenData['access_token']);
        if (!$googleUser) {
            return redirect()->route('auth.login')
                ->withErrors(['email' => 'Unable to retrieve your Google account information. Please try again or contact support if the issue persists.']);
        }

        // Find or create user
        $user = $googleAuth->findOrCreateUser($googleUser);
        if (!$user) {
            return redirect()->route('auth.login')
                ->withErrors(['email' => 'Unable to create or access your account. Please try again or contact support for assistance.']);
        }

        // Check if account is active
        if (!$user->isActiveUser()) {
            if ($user->locked_until && $user->locked_until->isFuture()) {
                $lockTimeRemaining = $user->locked_until->diffInMinutes(now());
                $reason = 'Account is locked until ' . formatUserDateTime($user->locked_until);
                $errorMessage = "Your account is temporarily locked due to multiple failed login attempts. Please try again in {$lockTimeRemaining} minutes.";
            } else {
                $reason = 'Account has been deactivated';
                $errorMessage = 'Your Google account access has been deactivated by an administrator. Please contact support for assistance.';
            }

            $loginTracker->logBlockedLogin($user->email, $reason, $user);
            return redirect()->route('auth.login')
                ->withErrors(['email' => $errorMessage]);
        }

        // Record successful login
        $user->recordLogin($request->ip());
        $loginTracker->logSuccessfulLogin($user, $user->email);

        // Login the user
        Auth::login($user, true); // Remember the user
        $request->session()->regenerate();

        return redirect()->intended('/admin/dashboard');
    }
}
