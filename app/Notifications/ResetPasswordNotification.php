<?php

namespace App\Notifications;

use App\Models\EmailTemplate;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The password reset token.
     */
    public $token;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run before timing out.
     */
    public int $timeout = 60;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->onQueue('emails'); // Use emails queue
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Try to get the password reset email template
        $template = EmailTemplate::findBySlug('password-reset');

        if ($template) {
            // Use custom template - only include token for security
            $resetUrl = url(route('auth.reset-password', [
                'token' => $this->token,
            ], false));

            $data = [
                'name' => $notifiable->name,
                'reset_link' => $resetUrl,
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
            ];

            return (new MailMessage)
                ->subject($template->renderSubject($data))
                ->view('emails.custom-template', [
                    'content' => $template->renderBody($data)
                ]);
        }

        // Fallback to default Laravel template - only include token for security
        $resetUrl = url(route('auth.reset-password', [
            'token' => $this->token,
        ], false));

        return (new MailMessage)
            ->subject('Reset Password - ' . config('app.name'))
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $resetUrl)
            ->line('This password reset link will expire in 60 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
