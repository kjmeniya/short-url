<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ContactRequest;
use App\Models\Contact;
use App\Models\ShortUrl;
use App\Services\EmailService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    protected NotificationService $notificationService;
    protected EmailService $emailService;

    public function __construct(NotificationService $notificationService, EmailService $emailService)
    {
        $this->notificationService = $notificationService;
        $this->emailService = $emailService;
    }

    /**
     * Display the landing page.
     */
    public function index()
    {
        return view('front.home');
    }

    /**
     * Guest URL shortening via AJAX.
     */
    public function shorten(Request $request)
    {
        $request->validate([
            'url' => 'required|url|max:2083',
        ], [
            'url.required' => 'Please enter a URL to shorten.',
            'url.url'      => 'Please enter a valid URL (include https://).',
        ]);

        // Check if this URL was already shortened recently (same session)
        $existing = ShortUrl::where('original_url', $request->url)
            ->whereNull('created_by')
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($existing) {
            return response()->json([
                'success'   => true,
                'short_url' => $existing->short_url,
                'code'      => $existing->custom_alias ?: $existing->code,
                'clicks'    => $existing->clicks,
                'message'   => 'Short URL already exists.',
            ]);
        }

        $shortUrl = ShortUrl::create([
            'original_url' => $request->url,
            'status'       => 'active',
            'created_by'   => null,
        ]);

        return response()->json([
            'success'   => true,
            'short_url' => $shortUrl->short_url,
            'code'      => $shortUrl->custom_alias ?: $shortUrl->code,
            'clicks'    => 0,
            'message'   => 'Short URL created successfully!',
        ]);
    }

    /**
     * Redirect a short code / alias to the original URL.
     */
    public function redirect(string $code)
    {
        // Check custom alias first, then code
        $shortUrl = ShortUrl::where('custom_alias', $code)
            ->orWhere('code', $code)
            ->first();

        if (!$shortUrl || $shortUrl->status !== 'active') {
            abort(404);
        }

        if ($shortUrl->isExpired()) {
            $shortUrl->update(['status' => 'expired']);
            abort(410, 'This short URL has expired.');
        }

        // Increment click counter without triggering model events
        $shortUrl->increment('clicks');

        return redirect()->away($shortUrl->original_url);
    }

    /**
     * Handle contact form submission.
     */
    public function sendContact(ContactRequest $request)
    {
        try {
            $contact = Contact::create([
                'name'    => $request->name,
                'email'   => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'status'  => 'new',
                'is_spam' => false,
            ]);

            $this->emailService->sendContactThankYou(
                $contact->email,
                $contact->name,
                $contact->subject,
                $contact->message
            );

            $this->notificationService->sendToSuperAdmins(
                'contact_received',
                "New Contact Message from {$contact->name}",
                "{$contact->name} ({$contact->email}) sent a message with subject: {$contact->subject}",
                [
                    'contact_id'    => $contact->id,
                    'contact_name'  => $contact->name,
                    'contact_email' => $contact->email,
                    'subject'       => $contact->subject,
                    'url'           => route('admin.contacts.show', $contact->id),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your message! We will get back to you soon.',
            ]);
        } catch (\Exception $e) {
            Log::error('Contact form submission error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sorry, there was an error sending your message. Please try again later.',
            ], 500);
        }
    }
}
