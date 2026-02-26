<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ContactRequest;
use App\Models\Contact;
use App\Services\EmailService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    protected NotificationService $notificationService;
    protected EmailService $emailService;

    public function __construct(NotificationService $notificationService, \App\Services\EmailService $emailService)
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
     * Handle contact form submission.
     */
    public function sendContact(ContactRequest $request)
    {
        try {
            // Create contact record in database
            $contact = Contact::create([
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'new',
                'is_spam' => false,
            ]);

            // Send thank you email to the user using EmailService
            $this->emailService->sendContactThankYou(
                $contact->email,
                $contact->name,
                $contact->subject,
                $contact->message
            );

            // Send notification to super admin
            $this->notificationService->sendToSuperAdmins(
                'contact_received',
                "New Contact Message from {$contact->name}",
                "{$contact->name} ({$contact->email}) sent a message with subject: {$contact->subject}",
                [
                    'contact_id' => $contact->id,
                    'contact_name' => $contact->name,
                    'contact_email' => $contact->email,
                    'subject' => $contact->subject,
                    'url' => route('admin.contacts.show', $contact->id)
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your message! We will get back to you soon.'
            ]);
        } catch (\Exception $e) {
            Log::error('Contact form submission error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Sorry, there was an error sending your message. Please try again later.'
            ], 500);
        }
    }
}
