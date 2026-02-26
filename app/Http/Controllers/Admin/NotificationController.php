<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Models\Notification;
use App\Models\User;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of the notifications.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();

            // Super admins can see all notifications, regular users see only their own
            if ($user->isSuperAdmin()) {
                $notifications = Notification::with('user:id,name,email,avatar')->select(['id', 'type', 'data', 'read_at', 'created_at', 'notifiable_type', 'notifiable_id']);
            } else {
                $notifications = $user->notifications()
                    ->with('user:id,name,email,avatar')
                    ->select(['id', 'type', 'data', 'read_at', 'created_at']);
            }

            // Apply type filter
            if ($request->filled('type')) {
                $notifications->where('data->type', $request->type);
            }

            // Apply status filter
            if ($request->filled('status')) {
                if ($request->status === 'read') {
                    $notifications->whereNotNull('read_at');
                } elseif ($request->status === 'unread') {
                    $notifications->whereNull('read_at');
                }
            }

            // Apply date filter
            $notifications = $this->applyDateFilter($notifications, $request);



            // Add row number for readable ID
            $notifications = $notifications->orderBy('created_at', 'desc')->get();
            $notifications = $notifications->map(function ($notification, $index) {
                $notification->readable_id = $index + 1;
                return $notification;
            });

            return DataTables::of($notifications)
                ->addColumn('checkbox', function ($notification) {
                    return '<div class="form-check">
                                <input class="form-check-input row-checkbox" type="checkbox" value="' . $notification->id . '">
                                <label class="form-check-label"></label>
                            </div>';
                })
                ->addColumn('readable_id', function ($notification) {
                    return $notification->readable_id;
                })
                ->addColumn('type', function ($notification) {
                    $type = $notification->data['type'] ?? 'info';
                    $color = $notification->data['color'] ?? 'primary';
                    $colorClass = $this->getColorClass($color);
                    return '<span class="badge ' . $colorClass . '">' . ucfirst(str_replace('_', ' ', $type)) . '</span>';
                })
                ->addColumn('title', function ($notification) {
                    $title = $notification->data['title'] ?? 'Notification';
                    $fontWeight = $notification->read_at ? 'fw-normal' : 'fw-bold';
                    $showUrl = route('admin.notifications.show', $notification->id);
                    return '<a href="' . $showUrl . '" class="text-decoration-none ' . $fontWeight . '">' . $title . '</a>';
                })
                ->addColumn('message', function ($notification) {
                    $message = $notification->data['message'] ?? '';
                    return strlen($message) > 100 ? substr($message, 0, 100) . '...' : $message;
                })
                ->addColumn('user', function ($notification) use ($user) {
                    // Only show user column for super admins
                    if (!$user->isSuperAdmin()) {
                        return '';
                    }

                    $placeholder = asset('build/images/others/placeholder.jpg');
                    if ($notification->user) {
                        $avatar = $notification->user->avatar ? asset($notification->user->avatar) : $placeholder;
                        return '<div class="d-flex align-items-center">
                                    <img src="' . $avatar . '" class="user-avatar me-2" alt="' . e($notification->user->name) . '">
                                    <div class="d-flex flex-column text-start">
                                        <div class="fw-bold">' . e($notification->user->name) . '</div>
                                        <small class="text-muted">' . e($notification->user->email) . '</small>
                                    </div>
                                </div>';
                    }
                    return '<div class="d-flex align-items-center">
                                <img src="' . $placeholder . '" class="user-avatar me-2" alt="Guest">
                                <div class="d-flex flex-column text-start">
                                    <div class="fw-bold">Guest</div>
                                    <small class="text-muted">' . substr($notification->user->id, 0, 8) . '...</small>
                                </div>
                            </div>';
                })
                ->addColumn('status', function ($notification) {
                    if ($notification->read_at) {
                        return '<span class="badge bg-success">Read</span>';
                    } else {
                        return '<span class="badge bg-warning">Unread</span>';
                    }
                })
                ->addColumn('action', function ($notification) {
                    return '<div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="icon-sm" data-lucide="more-horizontal"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="' . route('admin.notifications.show', $notification->id) . '">
                                        <i data-lucide="eye" class="icon-sm me-2 text-success"></i>View Details
                                    </a></li>
                                    ' . (isset($notification->data['url']) && $notification->data['url'] ?
                        '<li><a class="dropdown-item" href="' . $notification->data['url'] . '">
                                            <i data-lucide="external-link" class="icon-sm me-2 text-info"></i>View Related
                                        </a></li>' : '') . '
                                    ' . (!$notification->read_at ?
                        '<li><a class="dropdown-item mark-read-btn" href="#" data-id="' . $notification->id . '">
                                            <i data-lucide="check" class="icon-sm me-2 text-primary"></i>Mark as Read
                                        </a></li>' : '') . '
                                    <li><a class="dropdown-item delete-btn" href="#" data-id="' . $notification->id . '">
                                        <i data-lucide="trash-2" class="icon-sm me-2 text-danger"></i>Delete
                                    </a></li>
                                </ul>
                            </div>';
                })
                ->editColumn('created_at', function ($notification) {
                    return formatUserDateTime($notification->created_at);
                })
                ->rawColumns(['checkbox', 'type', 'title', 'status', 'action', 'user'])
                ->make(true);
        }

        // Get statistics
        $stats = $this->getNotificationStats();

        // Get notification types for filter
        $notificationTypes = $this->notificationService->getNotificationTypes();

        // Check if user is super admin
        $isSuperAdmin = Auth::user()->isSuperAdmin();

        $viewData = $this->withSeo(
            compact('stats', 'notificationTypes', 'isSuperAdmin'),
            'Notifications',
            'View and manage your admin notifications and system alerts.',
            'notifications, alerts, admin notifications, system messages'
        );

        return view('admin.notifications.index', $viewData);
    }

    /**
     * Display the specified notification.
     */
    public function show($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);

        // Format dates using timezone service
        $formattedDates = [
            'created_at' => formatUserDateTime($notification->created_at),
            'created_at_date' => formatUserDateTime($notification->created_at, 'd-m-Y'),
            'created_at_time' => formatUserDateTime($notification->created_at, 'H:i:s A'),
            'read_at' => $notification->read_at ? formatUserDateTime($notification->read_at) : null,
            'read_at_date' => $notification->read_at ? formatUserDateTime($notification->read_at, 'd-m-Y') : null,
            'read_at_time' => $notification->read_at ? formatUserDateTime($notification->read_at, 'H:i:s A') : null,
            'time_ago' => timeAgo($notification->created_at),
        ];

        $viewData = $this->withSeo(
            compact('notification', 'formattedDates'),
            'Notification Details',
            'View detailed information about this notification.',
            'notification details, admin notification, system alert'
        );

        return view('admin.notifications.show', $viewData);
    }

    /**
     * Handle bulk actions for notifications.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:read,unread,delete',
            'ids' => 'required|array|min:1|max:100',
            'ids.*' => 'required|string|exists:notifications,id'
        ], [
            'action.required' => 'Action is required.',
            'action.in' => 'Invalid action selected.',
            'ids.required' => 'Please select at least one notification.',
            'ids.min' => 'Please select at least one notification.',
            'ids.max' => 'You can only process 100 notifications at once.',
            'ids.*.exists' => 'One or more selected notifications do not exist.'
        ]);

        $user = Auth::user();
        $action = $request->action;
        $ids = $request->ids;
        $processed = 0;

        try {
            $notifications = $user->notifications()->whereIn('id', $ids);

            switch ($action) {
                case 'read':
                    $notifications->whereNull('read_at')->update(['read_at' => now()]);
                    $processed = $notifications->count();
                    $message = "{$processed} notifications marked as read.";
                    break;

                case 'unread':
                    $notifications->whereNotNull('read_at')->update(['read_at' => null]);
                    $processed = $notifications->count();
                    $message = "{$processed} notifications marked as unread.";
                    break;

                case 'delete':
                    $processed = $notifications->count();
                    foreach ($notifications->get() as $notification) {
                        $notification->update(['deleted_by' => $user->id]);
                        $notification->delete();
                    }
                    $message = "{$processed} notifications deleted.";
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action.'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'processed' => $processed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk action failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export notifications.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'json');
        $user = Auth::user();
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->get();

        if ($format === 'csv') {
            $filename = 'notifications_' . now()->format('Y_m_d_H_i_s') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($notifications) {
                $file = fopen('php://output', 'w');

                // CSV headers
                fputcsv($file, [
                    'ID',
                    'Type',
                    'Title',
                    'Message',
                    'Status',
                    'Created At',
                    'Read At',
                    'URL'
                ]);

                foreach ($notifications as $notification) {
                    fputcsv($file, [
                        $notification->id,
                        $notification->data['type'] ?? '',
                        $notification->data['title'] ?? '',
                        $notification->data['message'] ?? '',
                        $notification->read_at ? 'Read' : 'Unread',
                        formatUserDateTime($notification->created_at),
                        $notification->read_at ? formatUserDateTime($notification->read_at) : '',
                        $notification->data['url'] ?? ''
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // JSON export
        $filename = 'notifications_' . now()->format('Y_m_d_H_i_s') . '.json';

        $data = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? '',
                'title' => $notification->data['title'] ?? '',
                'message' => $notification->data['message'] ?? '',
                'status' => $notification->read_at ? 'read' : 'unread',
                'created_at' => formatUserDateTime($notification->created_at),
                'read_at' => $notification->read_at ? formatUserDateTime($notification->read_at) : null,
                'url' => $notification->data['url'] ?? null,
                'data' => $notification->data
            ];
        });

        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import notifications.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'overwrite' => 'nullable|boolean'
        ], [
            'file.required' => 'Please select a file to import.',
            'file.file' => 'The uploaded file is not valid.',
            'file.max' => 'The file size must not exceed 10MB.',
        ]);

        $file = $request->file('file');
        $overwrite = $request->boolean('overwrite');
        $errors = [];
        $imported = 0;
        $user = Auth::user();

        try {
            if ($file->getClientOriginalExtension() === 'csv') {
                $data = array_map('str_getcsv', file($file->path()));
                $headers = array_shift($data);

                foreach ($data as $row) {
                    if (count($row) === count($headers)) {
                        $notificationData = array_combine($headers, $row);

                        $result = $this->importNotification($user, $notificationData, $overwrite);
                        if ($result['success']) {
                            $imported++;
                        } else {
                            $errors[] = $result['error'];
                        }
                    } else {
                        $errors[] = 'CSV row has mismatched column count';
                    }
                }
            } else {
                $jsonData = json_decode(file_get_contents($file->path()), true);

                foreach ($jsonData as $notificationData) {
                    $result = $this->importNotification($user, $notificationData, $overwrite);
                    if ($result['success']) {
                        $imported++;
                    } else {
                        $errors[] = $result['error'];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$imported} notifications imported successfully.",
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import a single notification.
     */
    private function importNotification($user, array $data, bool $overwrite): array
    {
        try {
            // Validate required fields
            if (empty($data['title'])) {
                return ['success' => false, 'error' => 'Title is required'];
            }

            // Check if notification exists (by title and type)
            $existingNotification = $user->notifications()
                ->where('data->title', $data['title'])
                ->where('data->type', $data['type'] ?? 'info')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($existingNotification && !$overwrite) {
                return ['success' => false, 'error' => 'Notification already exists: ' . $data['title']];
            }

            if ($existingNotification && $overwrite) {
                $existingNotification->delete();
            }

            // Create notification data
            $notificationData = [
                'type' => $data['type'] ?? 'info',
                'title' => $data['title'],
                'message' => $data['message'] ?? '',
                'icon' => $data['icon'] ?? 'bell',
                'color' => $data['color'] ?? 'primary',
                'url' => $data['url'] ?? null
            ];

            // Create notification
            // $user->notify(new AdminNotification($notificationData));

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Import error: ' . $e->getMessage()];
        }
    }

    /**
     * Get notification statistics.
     */
    private function getNotificationStats(): array
    {
        $user = Auth::user();
        return $this->notificationService->getNotificationStats($user);
    }

    /**
     * Get color class for badge.
     */
    private function getColorClass($color)
    {
        $colorMap = [
            'primary' => 'bg-primary',
            'success' => 'bg-success',
            'danger' => 'bg-danger',
            'warning' => 'bg-warning',
            'info' => 'bg-info',
            'secondary' => 'bg-secondary'
        ];
        return $colorMap[$color] ?? 'bg-primary';
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(string $id)
    {
        $success = $this->notificationService->markAsRead($id);

        if ($success) {
            return response()->json(['message' => 'Notification marked as read']);
        }

        return response()->json(['message' => 'Notification not found'], 404);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'message' => "Marked {$count} notifications as read",
            'count' => $count
        ]);
    }

    /**
     * Soft delete notification.
     */
    public function delete(string $id)
    {
        $success = $this->notificationService->deleteNotification($id);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found.'
        ], 404);
    }

    /**
     * Get notification count for navbar.
     */
    public function count()
    {
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'has_unread' => $unreadCount > 0
        ]);
    }

    /**
     * Get notifications for navbar dropdown.
     */
    public function navbar()
    {
        $user = Auth::user();

        // Get notifications using proper model which handles soft deletes automatically
        $notifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'icon' => $notification->data['icon'] ?? 'bell',
                    'color' => $notification->data['color'] ?? 'primary',
                    'url' => $notification->data['url'] ?? null,
                    'read_at' => $notification->read_at,
                    'time_ago' => timeAgo($notification->created_at),
                    'is_unread' => is_null($notification->read_at),
                ];
            }),
            'unread_count' => $unreadCount,
            'has_unread' => $unreadCount > 0,
            'view_all_url' => route('admin.notifications.index'),
        ]);
    }

    /**
     * Display deleted notifications.
     */
    public function trashed(Request $request)
    {
        $this->setSeoData('Deleted Notifications', 'Manage soft-deleted notifications with restore options');

        if ($request->ajax()) {
            $query = Notification::onlyTrashed()
                ->orderBy('deleted_at', 'desc')
                ->with(['notifiable' => function ($query) {
                    $query->select('id', 'name', 'email');
                }]);

            // Apply filters
            if ($request->filled('type')) {
                $query->whereJsonContains('data->type', $request->type);
            }

            if ($request->filled('deleted_by')) {
                if ($request->deleted_by === 'system') {
                    $query->whereNull('deleted_by');
                } else {
                    $query->whereNotNull('deleted_by');
                }
            }

            // Apply date filter
            $query = $this->applyDateFilter($query, $request, 'deleted_at');

            return DataTables::of($query)
                ->addColumn('icon', function ($notification) {
                    $icon = $notification->data['icon'] ?? 'bell';
                    $color = $notification->data['color'] ?? 'primary';
                    return '<div class="d-flex align-items-center justify-content-center bg-light rounded-circle" style="width: 40px; height: 40px;">
                        <i data-lucide="' . $icon . '" class="icon-sm text-' . $color . '"></i>
                    </div>';
                })
                ->addColumn('title', function ($notification) {
                    $title = $notification->data['title'] ?? 'Notification';
                    $message = $notification->data['message'] ?? '';
                    $truncatedMessage = strlen($message) > 50 ? substr($message, 0, 50) . '...' : $message;

                    return '<div>
                        <div class="fw-bold">' . e($title) . '</div>
                        <small class="text-muted">' . e($truncatedMessage) . '</small>
                    </div>';
                })
                ->addColumn('type', function ($notification) {
                    $type = $notification->data['type'] ?? 'info';
                    $color = $notification->data['color'] ?? 'primary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst(str_replace('_', ' ', $type)) . '</span>';
                })
                ->addColumn('recipient', function ($notification) {
                    if ($notification->notifiable) {
                        return '<div>
                            <div class="fw-bold">' . e($notification->notifiable->name) . '</div>
                            <small class="text-muted">' . e($notification->notifiable->email) . '</small>
                        </div>';
                    }
                    return '<span class="text-muted">Unknown</span>';
                })
                ->addColumn('deleted_info', function ($notification) {
                    $deletedBy = $notification->deleted_by ? 'Admin' : 'System';
                    $formattedDate = formatUserDateTime($notification->deleted_at);

                    return '<div class="text-center">
                        <small class="text-muted d-block">' . $formattedDate . '</small>
                        <span class="badge bg-secondary mt-1">' . $deletedBy . '</span>
                    </div>';
                })
                ->addColumn('action', function ($notification) {
                    $currentUser = Auth::user();
                    $canPermanentlyDelete = $currentUser->isSuperAdmin();

                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="icon-sm" data-lucide="more-horizontal"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item restore-notification" href="#" data-id="' . $notification->id . '">
                                <i data-lucide="undo" class="icon-sm me-2 text-success"></i>Restore
                            </a></li>';

                    if ($canPermanentlyDelete) {
                        $actions .= '<li><a class="dropdown-item force-delete-notification" href="#" data-id="' . $notification->id . '">
                                <i data-lucide="trash-2" class="icon-sm me-2 text-danger"></i>Permanently Delete
                            </a></li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->rawColumns(['icon', 'title', 'type', 'recipient', 'deleted_info', 'action'])
                ->make(true);
        }

        return view('admin.notifications.trashed');
    }

    /**
     * Restore a soft-deleted notification.
     */
    public function restore(string $id)
    {
        $notification = Notification::whereNotNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found or not deleted.'
            ], 404);
        }

        // Restore the notification
        $notification->restore();
        $notification->update(['deleted_by' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Notification restored successfully.'
        ]);
    }

    /**
     * Permanently delete a notification.
     */
    public function forceDelete(string $id)
    {
        $notification = Notification::onlyTrashed()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found or not deleted.'
            ], 404);
        }

        // Permanently delete the notification
        $notification->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Notification permanently deleted.'
        ]);
    }

    /**
     * Show form to send custom notification
     */
    public function create()
    {
        // Get all users for dropdown
        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        // Get all roles for dropdown
        $roles = \App\Models\Role::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get notification types from service
        $notificationTypes = $this->notificationService->getNotificationTypes();

        $viewData = $this->withSeo(
            compact('users', 'roles', 'notificationTypes'),
            'Send Notification',
            'Send custom notifications to users',
            'notifications, alerts, admin notifications, system messages'
        );

        return view('admin.notifications.send', $viewData);
    }

    /**
     * Send custom notification
     */
    public function send(Request $request)
    {
        $request->validate([
            'notification_type' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,success,warning,error',
            'target_type' => 'required|in:all,users,roles',
            'platform' => 'required|in:web,mobile,both',
            'user_ids' => 'required_if:target_type,users|array',
            'user_ids.*' => 'exists:users,id',
            'role_ids' => 'required_if:target_type,roles|array',
            'role_ids.*' => 'exists:roles,id',
            'url' => 'nullable|url',
            'icon' => 'nullable|string|max:50',
        ]);

        $notificationType = $request->notification_type;
        $title = $request->title;
        $message = $request->message;
        $type = $request->type;
        $platform = $request->platform;
        $data = [
            'icon' => $request->icon ?? 'bell',
            'url' => $request->url,
        ];

        $sentCount = 0;

        // Determine recipients
        if ($request->target_type === 'all') {
            // Send to all users
            $users = User::all();

            foreach ($users as $user) {
                // Database notification
                $this->notificationService->sendToUser(
                    $user,
                    $notificationType,
                    $title,
                    $message,
                    $data
                );

                // Real-time notification
                if ($platform === 'web' || $platform === 'both') {
                    if ($user->isAdmin()) {
                        send_admin_notification($title, $message, $type, $data);
                    }
                }

                if ($platform === 'mobile' || $platform === 'both') {
                    send_app_notification($title, $message, $type, $data, $user->id);
                }

                $sentCount++;
            }
        } elseif ($request->target_type === 'users') {
            // Send to specific users
            $users = User::whereIn('id', $request->user_ids)->get();

            foreach ($users as $user) {
                // Database notification
                $this->notificationService->sendToUser(
                    $user,
                    $notificationType,
                    $title,
                    $message,
                    $data
                );

                // Real-time notification
                if ($platform === 'web' || $platform === 'both') {
                    if ($user->isAdmin()) {
                        send_admin_notification($title, $message, $type, $data);
                    }
                }

                if ($platform === 'mobile' || $platform === 'both') {
                    send_app_notification($title, $message, $type, $data, $user->id);
                }

                $sentCount++;
            }
        } elseif ($request->target_type === 'roles') {
            // Send to users with specific roles
            $users = User::whereHas('role', function ($query) use ($request) {
                $query->whereIn('id', $request->role_ids);
            })->get();

            foreach ($users as $user) {
                // Database notification
                $this->notificationService->sendToUser(
                    $user,
                    $notificationType,
                    $title,
                    $message,
                    $data
                );

                // Real-time notification
                if ($platform === 'web' || $platform === 'both') {
                    if ($user->isAdmin()) {
                        send_admin_notification($title, $message, $type, $data);
                    }
                }

                if ($platform === 'mobile' || $platform === 'both') {
                    send_app_notification($title, $message, $type, $data, $user->id);
                }

                $sentCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Notification sent to {$sentCount} user(s) successfully!",
            'sent_count' => $sentCount
        ]);
    }
}
