<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Role;
use App\Models\User;
use App\Services\EmailService;
use App\Services\LoginTrackingService;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends BaseApiController
{
    protected string $version = 'v1';
    protected TwoFactorAuthService $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Login user and create token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();
        $loginTracker = app(LoginTrackingService::class);

        // Check if user exists
        if (!$user) {
            $loginTracker->logFailedLogin($email, 'User not found');
            return $this->errorResponse('No account found with this email address. Please register first.', 404);
        }

        // Check if account is locked
        if ($user->locked_until && now()->isBefore($user->locked_until)) {
            $remainingMinutes = (int) ceil(now()->diffInMinutes($user->locked_until, true));
            $timeMessage = $remainingMinutes > 0 ? "{$remainingMinutes} minute" . ($remainingMinutes > 1 ? 's' : '') : 'less than 1 minute';
            $loginTracker->logBlockedLogin($email, 'Account is locked until ' . formatUserDateTime($user->locked_until), $user);
            return $this->errorResponse("Your account is temporarily locked due to too many failed login attempts. Please try again in {$timeMessage}.", 423);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            // Increment failed login attempts
            $user->incrementLoginAttempts();

            $maxAttempts = max_login_attempts();
            $lockoutDuration = lockout_duration();
            $remainingAttempts = max(0, $maxAttempts - $user->login_attempts);

            if ($remainingAttempts > 0) {
                return $this->errorResponse("Invalid password. You have {$remainingAttempts} attempt" . ($remainingAttempts > 1 ? 's' : '') . " remaining before your account is locked.", 401);
            } else {
                return $this->errorResponse("Invalid password. Your account has been locked for {$lockoutDuration} minutes due to too many failed attempts.", 423);
            }
        }

        // Check if account is active
        if (!$user->is_active) {
            $loginTracker->logBlockedLogin($email, 'Account has been deactivated', $user);
            return $this->forbiddenResponse('Your account has been deactivated. Please contact support for assistance.');
        }

        // Check if email is verified
        if (!$user->hasVerifiedEmail()) {
            // Generate and send new OTP
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

            $loginTracker->logBlockedLogin($email, 'Email not verified', $user);
            return $this->errorResponse('Please verify your email address before logging in. We have sent a verification code to your email.', 403);
        }

        // Reset login attempts on successful login
        $user->update(['login_attempts' => 0, 'locked_until' => null]);

        // Create token
        $deviceName = $request->device_name ?? 'api_token';
        $token = $user->createToken($deviceName)->plainTextToken;

        // Record successful login in both systems
        $user->recordLogin($request->ip());
        $loginTracker->logSuccessfulLogin($user, $email);

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'role' => $user->role?->name,
                'email_verified' => $user->hasVerifiedEmail(),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    /**
     * Register a new user
     */
    public function register(Request $request): JsonResponse
    {
        $minPasswordLength = password_min_length();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'unique:users,email', new \App\Rules\NotDisposableEmail()],
            'password' => ['required', 'string', 'min:' . $minPasswordLength, 'confirmed'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
            'role_id' => Role::USER_ID,
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

        return $this->createdResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified' => false,
            ],
        ], 'Registration successful. Please check your email for verification code.');
    }

    /**
     * Logout user (revoke token)
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Logout from all devices (revoke all tokens except current)
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $request->user()->currentAccessToken()->id;

        // Revoke all tokens except the current one
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return $this->successResponse([
            'revoked_count' => $user->tokens()->where('id', '!=', $currentTokenId)->count(),
        ], 'Logged out from all other devices successfully');
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $deviceName = $request->device_name ?? 'api_token';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Token refreshed successfully');
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return $this->successResponse(null, 'Password reset link sent to your email');
        }

        return $this->errorResponse('Unable to send password reset link', 400);
    }

    /**
     * Verify email with OTP code
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return $this->notFoundResponse('User not found');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email is already verified');
        }

        // Check if OTP is valid
        if ($user->email_verification_code !== $request->code) {
            return $this->errorResponse('Invalid verification code', 400);
        }

        // Check if OTP has expired
        if ($user->email_verification_code_expires_at && now()->isAfter($user->email_verification_code_expires_at)) {
            return $this->errorResponse('Verification code has expired. Please request a new one.', 400);
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        // Clear verification code
        $user->update([
            'email_verification_code' => null,
            'email_verification_code_expires_at' => null,
        ]);

        // Create token for auto-login
        $deviceName = $request->device_name ?? 'api_token';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar_url,
                'role' => $user->role?->name,
                'email_verified' => true,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Email verified successfully');
    }

    /**
     * Resend email verification OTP
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email is already verified');
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
            return $this->errorResponse('Failed to send verification code. Please try again.', 500);
        }

        return $this->successResponse(null, 'Verification code sent to your email');
    }

    /**
     * Verify two-factor authentication code during login
     */
    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::find($request->user_id);

        if (!$user->two_factor_enabled) {
            return $this->errorResponse('Two-factor authentication is not enabled for this user', 400);
        }

        if (!$this->twoFactorService->verifyTwoFactor($user, $request->code)) {
            return $this->errorResponse('Invalid verification code', 400);
        }

        // Create token after successful 2FA verification
        $deviceName = $request->device_name ?? 'api_token';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar_url,
                'role' => $user->role?->name,
                'email_verified' => $user->hasVerifiedEmail(),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Two-factor authentication verified');
    }

    /**
     * Send two-factor email code (for email method during login)
     */
    public function sendTwoFactorEmailCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = User::find($request->user_id);

        if (!$user->two_factor_enabled || $user->two_factor_method !== 'email') {
            return $this->errorResponse('Email-based two-factor authentication is not enabled for this user', 400);
        }

        $this->twoFactorService->sendEmailCode($user);

        return $this->successResponse(null, 'Verification code sent to your email');
    }
}
