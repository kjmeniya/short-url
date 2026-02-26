<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class EmailVerificationController extends Controller
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Show the email verification notice.
     */
    public function notice()
    {
        if (Auth::check() && Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.verify-email');
    }

    /**
     * Send email verification notification.
     */
    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('admin.dashboard');
        }

        $this->sendVerificationEmail($request->user());

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Verify email address.
     */
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('auth.login')
                ->withErrors(['email' => 'Invalid verification link.']);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('auth.login')
                ->with('status', 'Email already verified. You can now login.');
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        // Send welcome email
        $this->sendWelcomeEmail($user);

        // Auto-login the user
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Email verified successfully! Welcome to ' . site_name() . '!');
    }

    /**
     * Resend verification email.
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'This email address is not registered in our system.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        if ($user->hasVerifiedEmail()) {
            return back()->withErrors(['email' => 'Email is already verified.']);
        }

        $this->sendVerificationEmail($user);

        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Send verification email to user.
     */
    protected function sendVerificationEmail(User $user): void
    {
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
    }

    /**
     * Send welcome email to user.
     */
    protected function sendWelcomeEmail(User $user): void
    {
        $dashboardUrl = route('admin.dashboard');

        $this->emailService->sendTemplateEmail(
            'welcome-email',
            $user->email,
            [
                'name' => $user->name,
                'email' => $user->email,
                'dashboard_link' => $dashboardUrl,
                'app_name' => site_name(),
            ]
        );
    }

    /**
     * Generate email verification URL.
     */
    protected function generateVerificationUrl(User $user): string
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
}
