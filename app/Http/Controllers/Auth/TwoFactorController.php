<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    protected TwoFactorAuthService $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
        $this->middleware('auth');
    }

    /**
     * Show the two-factor verification form.
     */
    public function show()
    {
        $user = Auth::user();

        if (!$user->two_factor_enabled) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.two-factor-verify', compact('user'));
    }

    /**
     * Verify the two-factor authentication code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            $code = $request->input('code');

            if ($this->twoFactorService->verifyTwoFactor($user, $code)) {
                // Mark as verified in session
                session(['two_factor_verified' => true]);

                // Redirect to intended URL or dashboard
                $intendedUrl = session('url.intended');
                if ($intendedUrl) {
                    session()->forget('url.intended');
                    return redirect($intendedUrl);
                }

                if ($user->isSuperAdmin() || $user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                } else {
                    return redirect()->route('admin.profile');
                }
            } else {
                throw ValidationException::withMessages([
                    'code' => ['The verification code is invalid.']
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Two-factor verification error: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'code' => ['An error occurred during verification. Please try again.']
            ]);
        }
    }

    /**
     * Show the recovery code verification form.
     */
    public function showRecovery()
    {
        $user = Auth::user();

        if (!$user->two_factor_enabled) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.recovery', compact('user'));
    }

    /**
     * Verify the recovery code.
     */
    public function verifyRecovery(Request $request)
    {
        $request->validate([
            'recovery_code' => 'required|string'
        ]);

        try {
            $user = Auth::user();
            $recoveryCode = $request->input('recovery_code');

            if ($this->twoFactorService->verifyRecoveryCode($user, $recoveryCode)) {
                // Mark as verified in session
                session(['two_factor_verified' => true]);

                // Redirect to intended URL or dashboard
                $intendedUrl = session('url.intended');
                if ($intendedUrl) {
                    session()->forget('url.intended');
                    return redirect($intendedUrl);
                }

                if ($user->isSuperAdmin() || $user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                } else {
                    return redirect()->route('admin.profile');
                }
            } else {
                throw ValidationException::withMessages([
                    'recovery_code' => ['The recovery code is invalid or has already been used.']
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Recovery code verification error: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'recovery_code' => ['An error occurred during verification. Please try again.']
            ]);
        }
    }
}
