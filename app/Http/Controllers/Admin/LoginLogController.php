<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use App\Services\LoginTrackingService;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LoginLogController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    protected $loginTrackingService;

    public function __construct(LoginTrackingService $loginTrackingService)
    {
        $this->loginTrackingService = $loginTrackingService;
    }

    /**
     * Display a listing of login logs.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = LoginLog::with(['user'])
                ->select('login_logs.*');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Apply date filter
            $query = $this->applyDateFilter($query, $request);

            return DataTables::of($query)
                ->addColumn('user_info', function ($log) {
                    if ($log->user) {
                        return '<strong>' . $log->user->name . '</strong><br><small class="text-muted">' . $log->email . '</small>';
                    }
                    return '<strong>' . ($log->name ?: 'Unknown') . '</strong><br><small class="text-muted">' . $log->email . '</small>';
                })
                ->addColumn('device_info', function ($log) {
                    return '<strong>' . $log->device_info . '</strong><br><small class="text-muted">' . $log->ip_address . '</small>';
                })
                ->addColumn('location_info', function ($log) {
                    return $log->location_summary;
                })
                ->addColumn('status_badge', function ($log) {
                    return $log->status_badge;
                })
                ->addColumn('type_badge', function ($log) {
                    return $log->type_badge;
                })
                ->editColumn('login_at', function ($log) {
                    return $log->login_at ? formatUserDateTime($log->login_at) : '<span class="text-muted">Not logged</span>';
                })
                ->addColumn('action', function ($log) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="icon-sm" data-lucide="more-horizontal"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="' . route('admin.login-logs.show', $log->id) . '">
                                <i data-lucide="eye" class="icon-sm me-2"></i>View Details
                            </a></li>';

                    if ($log->is_suspicious) {
                        $actions .= '<li><a class="dropdown-item text-warning" href="#" onclick="markAsSafe(' . $log->id . ')">
                                <i data-lucide="shield-check" class="icon-sm me-2"></i>Mark as Safe
                            </a></li>';
                    }

                    if ($log->user && $log->status === 'success') {
                        $actions .= '<li><a class="dropdown-item text-info" href="' . route('admin.users.show', $log->user->id) . '">
                                <i data-lucide="user" class="icon-sm me-2"></i>View User
                            </a></li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->rawColumns(['user_info', 'device_info', 'status_badge', 'type_badge', 'login_at', 'action'])
                ->make(true);
        }

        // Get filter options
        $statuses = LoginLog::getStatuses();
        $types = LoginLog::getTypes();
        $users = User::select('id', 'name', 'email')->orderBy('name')->get();

        // Get statistics
        $stats = $this->loginTrackingService->getLoginStats();

        $viewData = $this->withSeo(
            compact('statuses', 'types', 'users', 'stats'),
            'Login Logs',
            'View and manage all login logs, track user login activities, and monitor security events.',
            'login logs, user activity, security monitoring, login tracking'
        );

        return view('admin.login-logs.index', $viewData);
    }

    /**
     * Display the specified login log.
     */
    public function show(LoginLog $loginLog)
    {
        $loginLog->load(['user']);

        $viewData = $this->withSeo(
            compact('loginLog'),
            'Login Log Details - ' . $loginLog->email,
            'View detailed information about login attempt including device, location, and security details.',
            'login log details, user activity, security monitoring'
        );

        return view('admin.login-logs.show', $viewData);
    }

    /**
     * Mark a suspicious login as safe.
     */
    public function markAsSafe(LoginLog $loginLog)
    {
        $loginLog->update(['is_suspicious' => false]);

        return response()->json(['message' => 'Login marked as safe']);
    }

    /**
     * Get login statistics for dashboard.
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

        $stats = $this->loginTrackingService->getLoginStats($filters);

        return response()->json($stats);
    }

    /**
     * Export login logs.
     */
    public function export(Request $request)
    {
        $query = LoginLog::with(['user']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Apply date filter
        $query = $this->applyDateFilter($query, $request);

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'login_logs_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'User Name',
                'Email',
                'IP Address',
                'Device',
                'Browser',
                'Platform',
                'Status',
                'Type',
                'Location',
                'Login At',
                'Logout At',
                'Session Duration',
                'Created At'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->name ?? $log->name,
                    $log->email,
                    $log->ip_address,
                    $log->device_type,
                    $log->browser,
                    $log->platform,
                    $log->status,
                    $log->type,
                    $log->location_summary,
                    $log->login_at ? formatUserDateTime($log->login_at) : '',
                    $log->logout_at ? formatUserDateTime($log->logout_at) : '',
                    $log->session_duration_human,
                    formatUserDateTime($log->created_at),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
