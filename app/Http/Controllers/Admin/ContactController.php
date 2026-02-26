<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\User;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ContactController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    protected \App\Services\EmailService $emailService;

    public function __construct(\App\Services\EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Display a listing of contacts.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Contact::with(['repliedBy'])
                ->select('contacts.*');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('is_spam')) {
                $query->where('is_spam', $request->is_spam === 'true');
            }

            // Apply date filter
            $query = $this->applyDateFilter($query, $request);

            return DataTables::of($query)
                ->addColumn('contact_info', function ($contact) {
                    return '<strong>' . e($contact->name) . '</strong><br><small class="text-muted">' . e($contact->email) . '</small>';
                })
                ->addColumn('subject_preview', function ($contact) {
                    return '<strong>' . e(Str::limit($contact->subject, 50)) . '</strong><br><small class="text-muted">' . e($contact->message_preview) . '</small>';
                })
                ->addColumn('status_badge', function ($contact) {
                    return $contact->status_badge;
                })
                ->addColumn('spam_badge', function ($contact) {
                    return $contact->spam_badge;
                })
                ->editColumn('created_at', function ($contact) {
                    return $contact->created_at ? formatUserDateTime($contact->created_at) : '<span class="text-muted">Unknown</span>';
                })
                ->addColumn('action', function ($contact) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="icon-sm" data-lucide="more-horizontal"></i>
                        </button>
                        <ul class="dropdown-menu">';

                    $actions .= '<li><a class="dropdown-item" href="' . route('admin.contacts.show', $contact->id) . '">
                            <i data-lucide="eye" class="icon-sm me-2"></i>View Details
                        </a></li>';

                    if ($contact->status === 'new') {
                        $actions .= '<li><a class="dropdown-item text-info" href="#" onclick="markAsRead(' . $contact->id . ')">
                                <i data-lucide="check" class="icon-sm me-2"></i>Mark as Read
                            </a></li>';
                    }

                    if (!$contact->is_spam) {
                        $actions .= '<li><a class="dropdown-item text-warning" href="#" onclick="markAsSpam(' . $contact->id . ')">
                                <i data-lucide="alert-triangle" class="icon-sm me-2"></i>Mark as Spam
                            </a></li>';
                    } else {
                        $actions .= '<li><a class="dropdown-item text-success" href="#" onclick="markAsNotSpam(' . $contact->id . ')">
                                <i data-lucide="shield-check" class="icon-sm me-2"></i>Mark as Not Spam
                            </a></li>';
                    }

                    $actions .= '<li><hr class="dropdown-divider"></li>';
                    $actions .= '<li><a class="dropdown-item text-danger" href="#" onclick="deleteContact(' . $contact->id . ')">
                            <i data-lucide="trash-2" class="icon-sm me-2"></i>Delete
                        </a></li>';

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->rawColumns(['contact_info', 'subject_preview', 'status_badge', 'spam_badge', 'created_at', 'action'])
                ->make(true);
        }

        // Get filter options
        $statuses = Contact::getStatuses();

        // Get statistics
        $stats = [
            'total' => Contact::count(),
            'new' => Contact::where('status', 'new')->count(),
            'read' => Contact::where('status', 'read')->count(),
            'replied' => Contact::where('status', 'replied')->count(),
            'archived' => Contact::where('status', 'archived')->count(),
            'spam' => Contact::where('is_spam', true)->count(),
        ];

        $viewData = $this->withSeo(
            compact('statuses', 'stats'),
            'Contact Messages',
            'View and manage all contact form submissions from website visitors.',
            'contacts, messages, inquiries, customer support'
        );

        return view('admin.contacts.index', $viewData);
    }

    /**
     * Display the specified contact.
     */
    public function show(Contact $contact)
    {
        $contact->load(['repliedBy']);

        // Mark as read if it's new
        if ($contact->status === 'new') {
            $contact->markAsRead();
        }

        $viewData = $this->withSeo(
            compact('contact'),
            'Contact Details - ' . $contact->name,
            'View detailed information about contact message from ' . $contact->email,
            'contact details, message, inquiry'
        );

        return view('admin.contacts.show', $viewData);
    }

    /**
     * Mark a contact as read.
     */
    public function markAsRead(Contact $contact)
    {
        $contact->markAsRead();

        return response()->json(['message' => 'Contact marked as read']);
    }

    /**
     * Mark a contact as spam.
     */
    public function markAsSpam(Contact $contact)
    {
        $contact->markAsSpam();

        return response()->json(['message' => 'Contact marked as spam']);
    }

    /**
     * Mark a contact as not spam.
     */
    public function markAsNotSpam(Contact $contact)
    {
        $contact->markAsNotSpam();

        return response()->json(['message' => 'Contact marked as not spam']);
    }


    /**
     * Mark contact as replied.
     */
    public function reply(Request $request, Contact $contact)
    {
        $request->validate([
            'reply_message' => 'required|string|max:2000',
        ]);

        // Mark as replied
        $contact->markAsReplied(Auth::id(), $request->reply_message);

        // Send email notification to the contact submitter via EmailService
        try {
            $this->emailService->sendContactReply(
                $contact->email,
                $contact->name,
                $contact->subject,
                $contact->message,
                $request->reply_message,
                $contact->created_at->format('M d, Y H:i')
            );
        } catch (\Exception $e) {
            Log::error('Failed to send reply email: ' . $e->getMessage());
        }

        return back()->with('success', 'Contact marked as replied successfully and email sent to ' . $contact->email);
    }

    /**
     * Archive a contact.
     */
    public function archive(Contact $contact)
    {
        $contact->archive();

        return response()->json(['message' => 'Contact archived successfully']);
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully']);
    }

    /**
     * Export contacts to CSV.
     */
    public function export(Request $request)
    {
        $query = Contact::with(['repliedBy']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_spam')) {
            $query->where('is_spam', $request->is_spam === 'true');
        }

        // Apply date filter
        $query = $this->applyDateFilter($query, $request);

        $contacts = $query->orderBy('created_at', 'desc')->get();

        $filename = 'contacts_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($contacts) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Subject',
                'Message',
                'Status',
                'Is Spam',
                'Replied At',
                'Replied By',
                'Reply Message',
                'Created At'
            ]);

            foreach ($contacts as $contact) {
                fputcsv($file, [
                    $contact->id,
                    $contact->name,
                    $contact->email,
                    $contact->subject,
                    $contact->message,
                    $contact->status,
                    $contact->is_spam ? 'Yes' : 'No',
                    $contact->replied_at ? formatUserDateTime($contact->replied_at) : '',
                    $contact->repliedBy?->name ?? '',
                    $contact->reply_message ?? '',
                    $contact->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
