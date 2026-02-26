<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Models\User;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use App\Services\TwoFactorAuthService;
use App\Services\EmailService;
use App\Traits\AdminSeoTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

class ProfileController extends Controller
{
    use AdminSeoTrait;

    protected NotificationService $notificationService;
    protected TwoFactorAuthService $twoFactorService;
    protected FileUploadService $fileUploadService;

    public function __construct(
        NotificationService $notificationService,
        TwoFactorAuthService $twoFactorService,
        FileUploadService $fileUploadService
    ) {
        $this->notificationService = $notificationService;
        $this->twoFactorService = $twoFactorService;
        $this->fileUploadService = $fileUploadService;
    }
    /**
     * Display the admin profile.
     */
    public function show()
    {
        /** @var User $admin */
        $admin = Auth::user();
        $admin->load(['role', 'loginLogs', 'emailLogs']);

        $viewData = $this->withSeo(
            compact('admin'),
            'Profile',
            'View and manage your admin profile information, account settings and preferences.',
            'admin profile, account settings, personal information, profile management'
        );

        return view('admin.profile.show', $viewData);
    }

    /**
     * Show the form for editing the admin profile.
     */
    public function edit()
    {
        /** @var User $admin */
        $admin = Auth::user();
        $admin->load('role');

        $viewData = $this->withSeo(
            compact('admin'),
            'Edit Profile',
            'Edit your admin profile information, update personal details and account preferences.',
            'edit profile, update account, profile settings, account management, personal information'
        );

        return view('admin.profile.edit', $viewData);
    }

    /**
     * Update the admin profile.
     */
    public function update(UpdateProfileRequest $request)
    {
        try {
            /** @var User $admin */
            $admin = Auth::user();
            $validated = $request->validated();

            // Handle password for Google users and regular users
            if (empty($validated['password'])) {
                unset($validated['password']);
            } else {
                $validated['password'] = Hash::make($validated['password']);
                // Mark password as changed for Google users
                if ($admin->isGoogleUser()) {
                    $validated['password_changed_at'] = now();
                }
            }

            // Handle avatar removal
            if ($request->input('avatar_remove') === '1') {
                // Delete old avatar if exists
                if ($admin->avatar) {
                    $this->fileUploadService->delete($admin->avatar);
                }
                $validated['avatar'] = null;
            }
            // Handle cropped avatar (only if not removing)
            elseif ($request->filled('avatar_cropped')) {
                // Delete old avatar if exists
                if ($admin->avatar) {
                    $this->fileUploadService->delete($admin->avatar);
                }
                $validated['avatar'] = $this->fileUploadService->uploadBase64($request->input('avatar_cropped'), 'avatars');
            }

            // Remove the base64 data from update
            unset($validated['avatar_cropped'], $validated['avatar_remove']);

            $admin->update($validated);

            // Send notification for profile updates
            $updateTypes = [];
            if (!empty($validated['password'])) {
                $updateTypes[] = 'password';
            }
            if (isset($validated['avatar']) || $request->input('avatar_remove') === '1') {
                $updateTypes[] = 'avatar';
            }
            if (isset($validated['name']) || isset($validated['email'])) {
                $updateTypes[] = 'basic info';
            }

            if (!empty($updateTypes)) {
                $this->notificationService->sendToSuperAdmins(
                    'profile_updated',
                    'Admin Profile Updated',
                    "Admin {$admin->name} has updated their profile (" . implode(', ', $updateTypes) . ")",
                    [
                        'user_id' => $admin->id,
                        'user_name' => $admin->name,
                        'update_types' => $updateTypes,
                        'updated_by' => $admin->name,
                        'url' => route('admin.users.show', $admin->id)
                    ]
                );
            }

            // Special message for Google users who just set their password
            $message = 'Profile updated successfully.';
            if ($admin->isGoogleUser() && !empty($validated['password'])) {
                $message = 'Profile updated successfully! You can now sign in with your email and password or continue using Google.';
            }

            return redirect()->route('admin.profile')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while updating your profile. Please try again.'])
                ->withInput();
        }
    }



    /**
     * Generate QR code for two-factor authentication setup.
     */
    public function generateTwoFactorQR(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Generate new secret
            $secret = $this->twoFactorService->generateSecretKey();

            // Store secret in session temporarily
            session(['temp_2fa_secret' => $secret]);

            // Generate QR code SVG
            $qrCodeSvg = $this->twoFactorService->generateQRCodeSvg($user, $secret);

            return response()->json([
                'success' => true,
                'qr_code' => $qrCodeSvg,
                'secret' => $secret
            ]);
        } catch (\Exception $e) {
            Log::error('Two-factor QR generation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code. Please try again.'
            ], 500);
        }
    }

    /**
     * Enable two-factor authentication.
     */
    public function enableTwoFactor(Request $request): JsonResponse
    {
        $request->validate([
            'method' => 'required|string|in:email,qr_code',
            'verification_code' => 'required_if:method,qr_code|string|size:6',
            'email_verification_code' => 'required_if:method,email|string|size:6'
        ]);

        try {
            /** @var User $user */
            $user = Auth::user();
            $method = $request->input('method');



            if ($method === 'qr_code') {
                $secret = session('temp_2fa_secret');

                if (!$secret) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No setup session found. Please generate a new QR code.'
                    ], 400);
                }

                $verificationCode = $request->input('verification_code');
            } elseif ($method === 'email') {
                $secret = null;
                $verificationCode = $request->input('email_verification_code');

                // Verify the email code first
                if (!$this->twoFactorService->verifyCode($user, $verificationCode)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or expired verification code. Please try again.'
                    ], 400);
                }
            } else {
                $secret = null;
                $verificationCode = null;
            }

            // Enable 2FA
            if ($this->twoFactorService->enableTwoFactor($user, $method, $secret, $verificationCode)) {
                // Clear temporary secret
                session()->forget('temp_2fa_secret');

                // Get recovery codes
                $recoveryCodes = $user->getTwoFactorRecoveryCodes();



                return response()->json([
                    'success' => true,
                    'message' => 'Two-factor authentication has been enabled successfully.',
                    'recovery_codes' => $recoveryCodes,
                    'method' => $method
                ]);
            } else {

                return response()->json([
                    'success' => false,
                    'message' => $method === 'qr_code' ? 'Invalid verification code. Please try again.' : 'Failed to enable two-factor authentication.'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Two-factor enable error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to enable two-factor authentication. Please try again.'
            ], 500);
        }
    }

    /**
     * Disable two-factor authentication.
     */
    public function disableTwoFactor(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $this->twoFactorService->disableTwoFactor($user);

            return response()->json([
                'success' => true,
                'message' => 'Two-factor authentication has been disabled successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Two-factor disable error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to disable two-factor authentication. Please try again.'
            ], 500);
        }
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if (!$user->two_factor_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Two-factor authentication is not enabled.'
                ], 400);
            }

            // Check if this is just a view request
            $viewOnly = $request->boolean('view_only', false);

            if ($viewOnly) {
                // Return existing recovery codes
                $recoveryCodes = $user->getTwoFactorRecoveryCodes();
                $message = 'Recovery codes loaded successfully.';
            } else {
                // Generate new recovery codes
                $recoveryCodes = $this->twoFactorService->regenerateRecoveryCodes($user);
                $message = 'Recovery codes have been regenerated successfully.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'recovery_codes' => $recoveryCodes
            ]);
        } catch (\Exception $e) {
            Log::error('Recovery codes regeneration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate recovery codes. Please try again.'
            ], 500);
        }
    }

    /**
     * Send email verification code for login or setup.
     */
    public function sendEmailCode(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Check if this is for setup (from profile edit) or login verification
            $isSetup = $request->has('setup') || !$user->two_factor_enabled;

            if (!$isSetup && $user->two_factor_method !== 'email') {
                return response()->json([
                    'success' => false,
                    'message' => 'Email two-factor authentication is not enabled.'
                ], 400);
            }

            if ($this->twoFactorService->sendEmailCode($user)) {
                $message = $isSetup
                    ? 'Verification code sent to your email address for setup verification.'
                    : 'Verification code sent to your email address.';

                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send verification code. Please try again.'
                ], 500);
            }
        } catch (\Exception $e) {
            //Log::error('Email code send error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.'
            ], 500);
        }
    }

    /**
     * Request Google account disconnection with OTP verification.
     */
    public function requestGoogleDisconnect(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Check if user is a Google user
            if (!$user->isGoogleUser()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This account is not linked with Google.'
                ], 400);
            }

            // Check if user has a password set
            if ($user->needsPasswordSetup()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must set a password before disconnecting your Google account.'
                ], 400);
            }

            // Generate OTP
            $otpCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $cacheKey = 'google_disconnect_otp_' . $user->id;

            // Store OTP in cache for 10 minutes
            Cache::put($cacheKey, $otpCode, 600);

            // Send OTP email
            $emailService = app(EmailService::class);
            $emailService->sendTemplateEmail(
                'google-disconnect-otp',
                $user->email,
                [
                    'name' => $user->name,
                    'otp_code' => $otpCode,
                    'app_name' => site_name(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your email address. Please check your inbox.'
            ]);

        } catch (\Exception $e) {
            Log::error('Google disconnect request failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify OTP and disconnect Google account.
     */
    public function verifyGoogleDisconnect(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'otp_code' => 'required|string|size:6'
            ]);

            /** @var User $user */
            $user = Auth::user();
            $cacheKey = 'google_disconnect_otp_' . $user->id;
            $storedOtp = Cache::get($cacheKey);

            if (!$storedOtp || $storedOtp !== $request->otp_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification code.'
                ], 400);
            }

            // Disconnect Google account
            $user->update([
                'google_id' => null,
                'is_google_user' => false
            ]);

            // Clear OTP from cache
            Cache::forget($cacheKey);

            // Send confirmation email
            $emailService = app(EmailService::class);
            $emailService->sendTemplateEmail(
                'google-disconnect-confirmation',
                $user->email,
                [
                    'name' => $user->name,
                    'app_name' => site_name(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Google account disconnected successfully. You can now only sign in with your password.'
            ]);

        } catch (\Exception $e) {
            Log::error('Google disconnect verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to disconnect Google account. Please try again.'
            ], 500);
        }
    }

    /**
     * Request account deletion with OTP verification.
     */
    public function requestAccountDeletion(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // Generate OTP
            $otpCode = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $cacheKey = 'account_deletion_otp_' . $user->id;

            // Store OTP in cache for 10 minutes
            Cache::put($cacheKey, $otpCode, 600);

            // Send OTP email
            $emailService = app(EmailService::class);
            $emailService->sendTemplateEmail(
                'account-deletion-otp',
                $user->email,
                [
                    'name' => $user->name,
                    'otp_code' => $otpCode,
                    'app_name' => site_name(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Verification code sent to your email address. <br/>Please check your inbox.'
            ]);

        } catch (\Exception $e) {
            Log::error('Account deletion request failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification email. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify OTP and delete account.
     */
    public function verifyAccountDeletion(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'otp_code' => 'required|string|size:6'
            ]);

            /** @var User $user */
            $user = Auth::user();
            $cacheKey = 'account_deletion_otp_' . $user->id;
            $storedOtp = Cache::get($cacheKey);

            if (!$storedOtp || $storedOtp !== $request->otp_code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired verification code.'
                ], 400);
            }

            // Send confirmation email before deletion
            $emailService = app(EmailService::class);
            $emailService->sendTemplateEmail(
                'account-deletion-confirmation',
                $user->email,
                [
                    'name' => $user->name,
                    'app_name' => site_name(),
                ]
            );

            // Clear OTP from cache
            Cache::forget($cacheKey);

            // Delete user avatar if exists
            if ($user->avatar) {
                $this->fileUploadService->delete($user->avatar);
            }

            // Logout user from all devices
            $user->logoutAllDevices();

            // Soft delete the user
            $user->delete();

            // Logout current session
            Auth::logout();

            return response()->json([
                'success' => true,
                'message' => 'Your account has been deleted successfully. You will be redirected to the login page.'
            ]);

        } catch (\Exception $e) {
            Log::error('Account deletion verification failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account. Please try again.'
            ], 500);
        }
    }

    /**
     * Get login history data for DataTable.
     */
    public function loginHistory(Request $request)
    {
        /** @var User $admin */
        $admin = Auth::user();

        $loginLogs = $admin->loginLogs()->latest();

        return DataTables::of($loginLogs)
            ->addColumn('login_at', function ($log) {
                return formatUserDateTime($log->login_at ?? $log->created_at);
            })
            ->addColumn('status', function ($log) {
                if ($log->status === 'success') {
                    return '<span class="badge bg-success"><i data-lucide="check" class="icon-xs me-1"></i>Success</span>';
                } else {
                    return '<span class="badge bg-danger"><i data-lucide="x" class="icon-xs me-1"></i>Failed</span>';
                }
            })
            ->addColumn('ip_address', function ($log) {
                return '<span class="font-monospace small">' . $log->ip_address . '</span>';
            })
            ->addColumn('device_type', function ($log) {
                return $log->device_type ?? 'Unknown';
            })
            ->addColumn('browser', function ($log) {
                return $log->browser ?? 'Unknown';
            })
            ->addColumn('location', function ($log) {
                return $log->location ?? ($log->city ? $log->city . ', ' . $log->country : 'Unknown');
            })
            ->addColumn('type', function ($log) {
                $typeColors = [
                    'login' => 'bg-primary',
                    'logout' => 'bg-secondary',
                    'failed' => 'bg-danger',
                    'locked' => 'bg-warning'
                ];
                $color = $typeColors[$log->type] ?? 'bg-info';
                return '<span class="badge ' . $color . '">' . ucfirst($log->type) . '</span>';
            })
            ->rawColumns(['status', 'ip_address', 'type'])
            ->make(true);
    }

    /**
     * Get email history data for DataTable.
     */
    public function emailHistory(Request $request)
    {
        /** @var User $admin */
        $admin = Auth::user();

        // Check if user has permission to view email history
        if (!$admin->isSuperAdmin() && !$admin->hasPermission('admin.profile.email-history')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $emailLogs = $admin->emailLogs()->latest();

        return DataTables::of($emailLogs)
            ->addColumn('sent_at', function ($email) {
                return formatUserDateTime($email->sent_at ?? $email->created_at);
            })
            ->addColumn('subject', function ($email) {
                return '<div class="text-truncate" style="max-width: 200px;" title="' . htmlspecialchars($email->subject) . '">' .
                       htmlspecialchars($email->subject) . '</div>';
            })
            ->addColumn('type', function ($email) {
                return '<span class="badge bg-info">' . ucfirst($email->type) . '</span>';
            })
            ->addColumn('status', function ($email) {
                $statusConfig = [
                    'sent' => ['color' => 'bg-success', 'icon' => 'check', 'text' => 'Sent'],
                    'delivered' => ['color' => 'bg-primary', 'icon' => 'check-check', 'text' => 'Delivered'],
                    'failed' => ['color' => 'bg-danger', 'icon' => 'x', 'text' => 'Failed'],
                    'pending' => ['color' => 'bg-warning', 'icon' => 'clock', 'text' => 'Pending'],
                ];

                $config = $statusConfig[$email->status] ?? ['color' => 'bg-secondary', 'icon' => 'help-circle', 'text' => ucfirst($email->status)];

                return '<span class="badge ' . $config['color'] . '">
                    <i data-lucide="' . $config['icon'] . '" class="icon-xs me-1"></i>
                    ' . $config['text'] . '
                </span>';
            })
            ->addColumn('recipient_email', function ($email) {
                return '<small>' . htmlspecialchars($email->recipient_email) . '</small>';
            })
            ->addColumn('actions', function ($email) {
                $actions = '<div class="d-flex gap-1">';

                if ($email->opened_at) {
                    $actions .= '<span class="badge bg-success" title="Opened at ' . formatUserDateTime($email->opened_at) . '">
                        <i data-lucide="eye" class="icon-xs"></i>
                    </span>';
                }

                if ($email->clicked_at) {
                    $actions .= '<span class="badge bg-info" title="Clicked at ' . formatUserDateTime($email->clicked_at) . '">
                        <i data-lucide="mouse-pointer" class="icon-xs"></i>
                    </span>';
                }

                if (!$email->opened_at && !$email->clicked_at) {
                    $actions .= '<span class="text-muted small">No activity</span>';
                }

                $actions .= '</div>';

                return $actions;
            })
            ->rawColumns(['subject', 'type', 'status', 'recipient_email', 'actions'])
            ->make(true);
    }
}
