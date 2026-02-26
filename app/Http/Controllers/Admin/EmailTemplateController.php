<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\NotificationService;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class EmailTemplateController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $templates = EmailTemplate::with(['creator:id,name', 'updater:id,name'])
                ->select(['id', 'name', 'subject', 'type', 'is_active', 'created_by', 'updated_by', 'created_at', 'updated_at']);

            // Apply filters
            if ($request->filled('type')) {
                $templates->where('type', $request->type);
            }

            if ($request->filled('status')) {
                $templates->where('is_active', $request->status === 'active');
            }

            // Apply date filter
            $templates = $this->applyDateFilter($templates, $request);

            return DataTables::of($templates)
                ->addColumn('type_badge', function ($template) {
                    $types = EmailTemplate::getTypes();
                    $typeName = $types[$template->type] ?? ucfirst($template->type);
                    $badgeClass = match ($template->type) {
                        'password_reset' => 'bg-warning',
                        'welcome' => 'bg-success',
                        'notification' => 'bg-info',
                        'reminder' => 'bg-secondary',
                        default => 'bg-primary'
                    };
                    return '<span class="badge ' . $badgeClass . '">' . $typeName . '</span>';
                })
                ->addColumn('status', function ($template) {
                    if ($template->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('creator_name', function ($template) {
                    return $template->creator ? $template->creator->name : 'N/A';
                })
                ->addColumn('action', function ($template) {
                    return '<div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="icon-sm" data-lucide="more-horizontal"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="' . route('admin.email-templates.show', $template->id) . '">
                                        <i data-lucide="eye" class="icon-sm me-2 text-success"></i>View
                                    </a></li>
                                    <li><a class="dropdown-item" href="' . route('admin.email-templates.edit', $template->id) . '">
                                        <i data-lucide="edit" class="icon-sm me-2 text-primary"></i>Edit
                                    </a></li>
                                    <li><a class="dropdown-item delete-template" href="#" data-id="' . $template->id . '">
                                        <i data-lucide="trash-2" class="icon-sm me-2 text-danger"></i>Delete
                                    </a></li>
                                </ul>
                            </div>';
                })
                ->editColumn('created_at', function ($template) {
                    return formatUserDateTime($template->created_at);
                })
                ->rawColumns(['type_badge', 'status', 'action'])
                ->make(true);
        }

        $types = EmailTemplate::getTypes();

        // Get statistics
        $stats = $this->getEmailTemplateStats();

        $viewData = $this->withSeo(
            compact('types', 'stats'),
            'Email Templates',
            'Manage email templates for system notifications, password resets, and other automated communications.',
            'email templates, notifications, automated emails, template management'
        );

        return view('admin.email-templates.index', $viewData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = EmailTemplate::getTypes();

        $viewData = $this->withSeo(
            compact('types'),
            'Create Email Template',
            'Create new email templates for system notifications and automated communications.',
            'create email template, new template, email design, template creation'
        );

        return view('admin.email-templates.create', $viewData);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:email_templates,name',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|string|in:' . implode(',', array_keys(EmailTemplate::getTypes())),
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $template = EmailTemplate::create($validated);

        // Send notification to admins
        $currentUser = Auth::user();
        $this->notificationService->sendToAdmins(
            'email_template_created',
            'Email Template Created',
            "Email template '{$template->name}' has been created by {$currentUser->name}",
            [
                'template_id' => $template->id,
                'template_name' => $template->name,
                'created_by' => $currentUser->name,
                'url' => route('admin.email-templates.show', $template->id)
            ]
        );

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(EmailTemplate $emailTemplate)
    {
        $viewData = $this->withSeo(
            compact('emailTemplate'),
            'Email Template Details',
            "View details for {$emailTemplate->name} email template including content and configuration.",
            'email template details, template view, template information'
        );

        return view('admin.email-templates.show', $viewData);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        $types = EmailTemplate::getTypes();
        $viewData = $this->withSeo(
            compact('emailTemplate', 'types'),
            'Edit Email Template',
            "Edit {$emailTemplate->name} email template content, settings and configuration.",
            'edit email template, modify template, update template, template settings'
        );
        return view('admin.email-templates.edit', $viewData);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:email_templates,name,' . $emailTemplate->id,
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|string|in:' . implode(',', array_keys(EmailTemplate::getTypes())),
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $validated['updated_by'] = Auth::id();

        $emailTemplate->update($validated);

        // Send notification to admins
        $currentUser = Auth::user();
        $this->notificationService->sendToAdmins(
            'email_template_updated',
            'Email Template Updated',
            "Email template '{$emailTemplate->name}' has been updated by {$currentUser->name}",
            [
                'template_id' => $emailTemplate->id,
                'template_name' => $emailTemplate->name,
                'updated_by' => $currentUser->name,
                'url' => route('admin.email-templates.show', $emailTemplate->id)
            ]
        );

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        // Store template info before deletion
        $templateName = $emailTemplate->name;
        $templateId = $emailTemplate->id;

        $emailTemplate->delete();

        // Send notification to admins
        $currentUser = Auth::user();
        $this->notificationService->sendToAdmins(
            'email_template_deleted',
            'Email Template Deleted',
            "Email template '{$templateName}' has been deleted by {$currentUser->name}",
            [
                'template_id' => $templateId,
                'template_name' => $templateName,
                'deleted_by' => $currentUser->name,
                'url' => route('admin.email-templates.index')
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Email template deleted successfully.'
        ]);
    }

    /**
     * Preview email template
     */
    public function preview(Request $request)
    {
        $content = $request->input('body', '');
        $subject = $request->input('subject', 'Email Preview');

        $html = view('emails.custom-template', [
            'content' => $content,
            'subject' => $subject
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Get email template statistics.
     */
    private function getEmailTemplateStats(): array
    {
        $total = EmailTemplate::count();
        $active = EmailTemplate::where('is_active', true)->count();
        $inactive = EmailTemplate::where('is_active', false)->count();
        $types = EmailTemplate::distinct('type')->count('type');

        // Get usage statistics from email logs if available
        $totalSent = 0;
        $recentSent = 0;

        if (class_exists(\App\Models\EmailLog::class)) {
            // Since email_logs table doesn't have template_id column yet,
            // we'll count all emails for now
            $totalSent = \App\Models\EmailLog::count();
            $recentSent = \App\Models\EmailLog::where('created_at', '>=', now()->subDays(30))
                ->count();
        }

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'types' => $types,
            'total_sent' => $totalSent,
            'recent_sent' => $recentSent,
            'active_rate' => $total > 0 ? round($active / $total * 100, 2) : 0,
        ];
    }
}
