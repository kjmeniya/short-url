<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\EmailService;
use App\Services\FileUploadService;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProfileController extends BaseApiController
{
    protected string $version = 'v1';
    protected EmailService $emailService;
    protected FileUploadService $fileUploadService;
    protected TwoFactorAuthService $twoFactorService;

    public function __construct(
        EmailService $emailService,
        FileUploadService $fileUploadService,
        TwoFactorAuthService $twoFactorService
    ) {
        $this->emailService = $emailService;
        $this->fileUploadService = $fileUploadService;
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Get authenticated user profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'role' => $user->role?->name,
            'phone' => $user->phone,
            'date_of_birth' => $user->date_of_birth,
            'address' => $user->address,
            'timezone' => $user->timezone,
            'email_verified' => $user->hasVerifiedEmail(),
            'two_factor_enabled' => $user->two_factor_enabled,
            'created_at' => $user->created_at->toIso8601String(),
            'updated_at' => $user->updated_at->toIso8601String(),
        ], 'Profile retrieved successfully');
    }

    /**
     * Update user profile
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'avatar' => 'sometimes|nullable|string', // base64 image
            'date_of_birth' => 'sometimes|nullable|date|before:today',
            'timezone' => 'sometimes|nullable|string|max:255',
            'address' => 'sometimes|nullable|string|max:500',
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $data = [];

        if ($request->has('name')) {
            $data['name'] = $request->name;
        }

        if ($request->has('email') && $request->email !== $user->email) {
            $data['email'] = $request->email;
            $data['email_verified_at'] = null; // Reset verification
        }

        if ($request->has('date_of_birth')) {
            $data['date_of_birth'] = $request->date_of_birth;
        }

        if ($request->has('timezone')) {
            $data['timezone'] = $request->timezone;
        }

        if ($request->has('address')) {
            $data['address'] = $request->address;
        }

        if ($request->has('phone')) {
            $data['phone'] = $request->phone;
        }

        if ($request->has('avatar')) {
            if ($request->avatar === null || $request->avatar === '') {
                // Remove avatar
                if ($user->avatar) {
                    $this->fileUploadService->delete($user->avatar);
                }
                $data['avatar'] = null;
            } else {
                // Upload new avatar
                $uploadedPath = $this->fileUploadService->uploadBase64($request->avatar, 'avatars');

                if ($uploadedPath === null) {
                    return $this->errorResponse('Failed to upload avatar. Please check the image format and size.', 400);
                }

                // Delete old avatar only after successful upload
                if ($user->avatar) {
                    $this->fileUploadService->delete($user->avatar);
                }

                $data['avatar'] = $uploadedPath;
            }
        }

        if (!empty($data)) {
            $user->update($data);
        }

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'date_of_birth' => $user->date_of_birth,
            'timezone' => $user->timezone,
            'address' => $user->address,
            'phone' => $user->phone,
            'email_verified' => $user->hasVerifiedEmail(),
        ], 'Profile updated successfully');
    }


    /**
     * Change password for authenticated user
     */
    public function changePassword(Request $request): JsonResponse
    {
        $minPasswordLength = password_min_length();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'min:' . $minPasswordLength, 'confirmed', 'different:current_password'],
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->errorResponse('Current password is incorrect', 400);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->remember_token = Str::random(60);
        $user->save();

        // Optionally revoke all tokens except current one
        $user->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return $this->successResponse(null, 'Password changed successfully');
    }

    /**
     * Send email verification link
     */
    public function sendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->successResponse(null, 'Email is already verified');
        }

        $verificationUrl = $this->generateVerificationUrl($user);

        $this->emailService->sendTemplateEmail(
            'email-verification',
            $user->email,
            [
                'name' => $user->name,
                'email' => $user->email,
                'verification_link' => $verificationUrl,
                'app_name' => site_name(),
            ]
        );

        return $this->successResponse(null, 'Verification link sent to your email');
    }

    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(null, 'Logged out from all devices');
    }

    /**
     * Get two-factor authentication status
     */
    public function twoFactorStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'enabled' => $user->two_factor_enabled,
            'method' => $user->two_factor_method,
            'confirmed' => $user->two_factor_confirmed_at !== null,
            'recovery_codes_count' => $user->two_factor_recovery_codes ? count($user->two_factor_recovery_codes) : 0,
        ], 'Two-factor authentication status');
    }

    /**
     * Generate QR code secret for 2FA setup
     */
    public function generateTwoFactorSecret(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->two_factor_enabled) {
            return $this->errorResponse('Two-factor authentication is already enabled', 400);
        }

        $secret = $this->twoFactorService->generateSecretKey();
        $qrCodeUrl = $this->twoFactorService->generateQRCodeUrl($user, $secret);

        return $this->successResponse([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ], 'Two-factor secret generated');
    }

    /**
     * Enable two-factor authentication
     */
    public function enableTwoFactor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'method' => 'required|in:qr_code,email',
            'code' => 'required_if:method,qr_code|string',
            'secret' => 'required_if:method,qr_code|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        if ($user->two_factor_enabled) {
            return $this->errorResponse('Two-factor authentication is already enabled', 400);
        }

        $method = $request->method;

        if ($method === 'qr_code') {
            $enabled = $this->twoFactorService->enableTwoFactor(
                $user,
                $method,
                $request->secret,
                $request->code
            );

            if (!$enabled) {
                return $this->errorResponse('Invalid verification code', 400);
            }
        } else {
            $this->twoFactorService->enableTwoFactor($user, $method);
        }

        $recoveryCodes = $this->twoFactorService->regenerateRecoveryCodes($user);

        return $this->successResponse([
            'enabled' => true,
            'method' => $method,
            'recovery_codes' => $recoveryCodes,
        ], 'Two-factor authentication enabled');
    }

    /**
     * Disable two-factor authentication
     */
    public function disableTwoFactor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Password is incorrect', 400);
        }

        if (!$user->two_factor_enabled) {
            return $this->errorResponse('Two-factor authentication is not enabled', 400);
        }

        $this->twoFactorService->disableTwoFactor($user);

        return $this->successResponse(null, 'Two-factor authentication disabled');
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Password is incorrect', 400);
        }

        if (!$user->two_factor_enabled) {
            return $this->errorResponse('Two-factor authentication is not enabled', 400);
        }

        $recoveryCodes = $this->twoFactorService->regenerateRecoveryCodes($user);

        return $this->successResponse([
            'recovery_codes' => $recoveryCodes,
        ], 'Recovery codes regenerated');
    }

    /**
     * Generate email verification URL
     */
    protected function generateVerificationUrl($user): string
    {
        return URL::temporarySignedRoute(
            'auth.verification.verify',
            Carbon::now()->addHours(24),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get avatar path for API response
     * Returns relative path like /storage/avatars/filename.png instead of full URL
     */
    protected function getApiAvatarPath($user): ?string
    {
        if (!$user->avatar) {
            return null;
        }

        // Check if it's a URL (Google avatar) - return as is
        if (filter_var($user->avatar, FILTER_VALIDATE_URL)) {
            return $user->avatar;
        }

        // Return relative storage path
        return '/storage/' . $user->avatar;
    }
}
