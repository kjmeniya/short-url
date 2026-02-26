<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Services\EmailService;
use App\Services\NotificationService;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class EmailLogController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    protected NotificationService $notificationService;
    protected EmailService $emailService;

    public function __construct(NotificationService $notificationService, EmailService $emailService)
    {
        $this->notificationService = $notificationService;
        $this->emailService = $emailService;
    }

    /**
     * Display a listing of email logs.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = EmailLog::with(['emailTemplate:id,name', 'user:id,name'])
                ->select([
                    'id',
                    'email_template_id',
                    'user_id',
                    'recipient_email',
                    'recipient_name',
                    'subject',
                    'body',
                    'type',
                    'status',
                    'sent_at',
                    'created_at',
                ]);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('template_id')) {
                $query->where('email_template_id', $request->template_id);
            }



            // Apply date filter
            $query = $this->applyDateFilter($query, $request);

            return DataTables::of($query)
                ->addColumn('recipient', function ($log) {
                    $name = $log->recipient_name ? '<strong>' . $log->recipient_name . '</strong><br>' : '';
                    return $name . '<small class="text-muted">' . $log->recipient_email . '</small>';
                })
                ->addColumn('subject_preview', function ($log) {
                    return '<strong>' . substr($log->subject, 0, 50) . (strlen($log->subject) > 50 ? '...' : '') . '</strong>';
                })
                ->addColumn('status_badge', function ($log) {
                    return $log->status_badge;
                })
                ->addColumn('type_badge', function ($log) {
                    return $log->type_badge;
                })
                ->addColumn('date', function ($log) {
                    $html = '<div class="text-nowrap">';

                    // Created at
                    $html .= '<div class="d-flex align-items-center gap-1 small">';
                    $html .= '<i data-lucide="calendar-plus" class="icon-sm text-muted"></i>';
                    $html .= '<span class="text-dark">' . formatUserDateTime($log->created_at) . '</span>';
                    $html .= '</div>';

                    // Sent at
                    if ($log->sent_at) {
                        $html .= '<div class="d-flex align-items-center gap-1 small">';
                        $html .= '<i data-lucide="send" class="icon-sm text-success"></i>';
                        $html .= '<span class="text-success">' . formatUserDateTime($log->sent_at) . '</span>';
                        $html .= '</div>';
                    } else {
                        $html .= '<div class="d-flex align-items-center gap-1 small">';
                        $html .= '<i data-lucide="clock" class="icon-sm text-muted"></i>';
                        $html .= '<span class="text-muted">Not sent</span>';
                        $html .= '</div>';
                    }

                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('action', function ($log) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="icon-sm" data-lucide="more-horizontal"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="' . route('admin.email-logs.show', $log->id) . '">
                                <i data-lucide="eye" class="icon-sm me-2"></i>View Details
                            </a></li>';

                    if ($log->body) {
                        $actions .= '<li><a class="dropdown-item" href="#" onclick="previewEmail(' . $log->id . ')">
                                <i data-lucide="mail" class="icon-sm me-2"></i>Preview Email
                            </a></li>';
                    }

                    if ($log->status === 'failed') {
                        $actions .= '<li><a class="dropdown-item text-primary" href="#" onclick="retryEmail(' . $log->id . ')">
                                <i data-lucide="refresh-cw" class="icon-sm me-2 text-primary"></i>Retry Send
                            </a></li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->rawColumns(['recipient', 'subject_preview', 'status_badge', 'type_badge', 'date', 'action'])
                ->make(true);
        }

        // Get filter options
        $statuses = EmailLog::getStatuses();
        $types = EmailLog::getTypes();
        $templates = EmailTemplate::where('is_active', true)->pluck('name', 'id');

        // Get statistics
        $stats = $this->getEmailStats();

        $viewData = $this->withSeo(
            compact('statuses', 'types', 'templates', 'stats'),
            'Email Logs',
            'View and manage all email logs, track email delivery status, and monitor email performance.',
            'email logs, email tracking, email delivery, email monitoring'
        );

        return view('admin.email-logs.index', $viewData);
    }

    /**
     * Display the specified email log.
     */
    public function show(EmailLog $emailLog)
    {
        $emailLog->load(['emailTemplate', 'user']);

        $viewData = $this->withSeo(
            compact('emailLog'),
            'Email Log Details - ' . $emailLog->subject,
            'View detailed information about email log including content, delivery status, and metadata.',
            'email log details, email tracking, email delivery'
        );

        return view('admin.email-logs.show', $viewData);
    }

    /**
     * Preview email content.
     */
    public function preview(EmailLog $emailLog)
    {
        if (!$emailLog->body) {
            return response()->json(['error' => 'No email content available'], 404);
        }

        // Render the full email with custom template
        $renderedEmailBody = view('emails.custom-template', [
            'content' => $emailLog->body,
            'subject' => $emailLog->subject
        ])->render();

        return response()->json([
            'subject' => $emailLog->subject,
            'body' => $renderedEmailBody,
            'recipient' => $emailLog->recipient_email,
            'sender' => $emailLog->sender_email,
        ]);
    }

    /**
     * Retry sending a failed email.
     */
    public function retry(EmailLog $emailLog)
    {
        try {
            // Delegate retry logic to EmailService
            $this->emailService->retryEmail($emailLog);

            return response()->json([
                'success' => true,
                'message' => 'Email has been successfully resent'
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Email retry failed', [
                'email_log_id' => $emailLog->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get email statistics for dashboard.
     */
    public function stats(Request $request)
    {
        $filters = [];

        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->date_from;
        }

        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->date_to;
        }

        if ($request->filled('type')) {
            $filters['type'] = $request->type;
        }

        $stats = $this->getEmailStats($filters);

        return response()->json($stats);
    }

    /**
     * Export email logs.
     */
    public function export(Request $request)
    {
        $query = EmailLog::with(['emailTemplate', 'user']);

        // Send notification to admins
        $currentUser = Auth::user();
        $this->notificationService->sendToAdmins(
            'email_log_exported',
            'Email Logs Exported',
            "Email logs have been exported by {$currentUser->name}",
            [
                'exported_by' => $currentUser->name,
                'url' => route('admin.email-logs.index')
            ]
        );

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('template_id')) {
            $query->where('email_template_id', $request->template_id);
        }

        if ($request->filled('recipient_email')) {
            $query->where('recipient_email', 'like', '%' . $request->recipient_email . '%');
        }

        // Apply date filter
        $query = $this->applyDateFilter($query, $request);

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'email_logs_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'Recipient Email',
                'Recipient Name',
                'Sender Email',
                'Sender Name',
                'Subject',
                'Type',
                'Status',
                'Template',
                'Sent At',
                'Created At'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->recipient_email,
                    $log->recipient_name,
                    $log->sender_email,
                    $log->sender_name,
                    $log->subject,
                    $log->type,
                    $log->status,
                    $log->emailTemplate?->name,
                    formatUserDateTime($log->sent_at),
                    formatUserDateTime($log->created_at),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    /**
     * Get email statistics.
     */
    public function getEmailStats(array $filters = []): array
    {
        $query = EmailLog::query();

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $total = $query->count();
        $sent = $query->where('status', 'sent')->count();
        $delivered = $query->where('status', 'delivered')->count();
        $failed = $query->whereIn('status', ['failed', 'bounced'])->count();
        $opened = $query->where('status', 'opened')->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'delivered' => $delivered,
            'failed' => $failed,
            'opened' => $opened,
            'success_rate' => $total > 0 ? round(($sent + $delivered) / $total * 100, 2) : 0,
            'open_rate' => $delivered > 0 ? round($opened / $delivered * 100, 2) : 0,
        ];
    }
}
