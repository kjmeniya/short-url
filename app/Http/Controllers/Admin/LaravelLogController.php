<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaravelLog;
use App\Services\LaravelLogParsingService;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;

class LaravelLogController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    protected LaravelLogParsingService $logParsingService;

    public function __construct(LaravelLogParsingService $logParsingService)
    {
        $this->logParsingService = $logParsingService;
    }

    /**
     * Display a listing of Laravel logs.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = LaravelLog::with(['user'])
                ->select('laravel_logs.*');

            // Apply filters
            if ($request->filled('level')) {
                $query->where('level', $request->level);
            }

            if ($request->filled('channel')) {
                $query->where('channel', $request->channel);
            }

            if ($request->filled('environment')) {
                $query->where('environment', $request->environment);
            }

            if ($request->filled('month')) {
                $query->where('log_month', $request->month);
            }

            // Apply date filter
            $query = $this->applyDateFilter($query, $request, 'logged_at');

            return DataTables::of($query)
                ->addColumn('level_badge', function ($log) {
                    return $log->level_badge;
                })
                ->addColumn('channel_badge', function ($log) {
                    return $log->channel_badge;
                })
                ->addColumn('environment_badge', function ($log) {
                    return $log->environment_badge;
                })
                ->addColumn('message_preview', function ($log) {
                    return $log->message_preview;
                })
                ->addColumn('user_info', function ($log) {
                    if ($log->user) {
                        return '<strong>' . $log->user->name . '</strong><br><small class="text-muted">ID: ' . $log->user_id . '</small>';
                    } elseif ($log->user_id) {
                        return '<small class="text-muted">User ID: ' . $log->user_id . '</small>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->editColumn('logged_at', function ($log) {
                    return formatUserDateTime($log->logged_at);
                })
                ->addColumn('action', function ($log) {
                    $actions = '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="icon-sm" data-lucide="more-horizontal"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="' . route('admin.laravel-logs.show', $log->id) . '">
                                <i class="icon-sm me-2" data-lucide="eye"></i>View Details
                            </a></li>';

                    if ($log->exception_class) {
                        $actions .= '<li><a class="dropdown-item text-danger" href="' . route('admin.laravel-logs.show', $log->id) . '#exception">
                                <i class="icon-sm me-2" data-lucide="alert-triangle"></i>View Exception
                            </a></li>';
                    }

                    if ($log->user) {
                        $actions .= '<li><a class="dropdown-item text-info" href="' . route('admin.users.show', $log->user->id) . '">
                                <i class="icon-sm me-2" data-lucide="user"></i>View User
                            </a></li>';
                    }

                    $actions .= '</ul></div>';

                    return $actions;
                })
                ->rawColumns(['level_badge', 'channel_badge', 'environment_badge', 'message_preview', 'user_info', 'action'])
                ->make(true);
        }

        // Get filter options
        $levels = LaravelLog::getLevels();
        $channels = LaravelLog::getChannels();
        $environments = LaravelLog::getEnvironments();
        $months = $this->logParsingService->getAvailableMonths();

        // Get statistics
        $stats = $this->logParsingService->getLogStats();

        $viewData = $this->withSeo(
            compact('levels', 'channels', 'environments', 'months', 'stats'),
            'Laravel Logs',
            'View and manage Laravel application logs, monitor errors, warnings, and system events.',
            'laravel logs, application logs, error monitoring, system logs'
        );

        return view('admin.laravel-logs.index', $viewData);
    }

    /**
     * Display the specified Laravel log.
     */
    public function show(LaravelLog $laravelLog)
    {
        $laravelLog->load(['user']);

        $viewData = $this->withSeo(
            compact('laravelLog'),
            'Laravel Log Details - ' . ucfirst($laravelLog->level),
            'View detailed information about Laravel log entry including context, stack trace, and metadata.',
            'laravel log details, error details, log analysis'
        );

        return view('admin.laravel-logs.show', $viewData);
    }

    /**
     * Parse and import Laravel logs.
     */
    public function parse()
    {
        try {
            $results = $this->logParsingService->parseLogFiles();

            return response()->json([
                'success' => true,
                'message' => "Successfully processed {$results['processed']} log entries, imported {$results['imported']} new entries.",
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error parsing logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get log statistics for dashboard.
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

        if ($request->filled('level')) {
            $filters['level'] = $request->level;
        }

        if ($request->filled('environment')) {
            $filters['environment'] = $request->environment;
        }

        $stats = $this->logParsingService->getLogStats($filters);

        return response()->json($stats);
    }

    /**
     * Export Laravel logs.
     */
    public function export(Request $request)
    {
        $query = LaravelLog::with(['user']);

        // Apply same filters as index
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('environment')) {
            $query->where('environment', $request->environment);
        }

        if ($request->filled('month')) {
            $query->where('log_month', $request->month);
        }

        // Apply date filter
        $query = $this->applyDateFilter($query, $request, 'logged_at');

        $logs = $query->orderBy('logged_at', 'desc')->get();

        $filename = 'laravel_logs_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'ID',
                'Level',
                'Channel',
                'Environment',
                'Message',
                'Exception Class',
                'User ID',
                'User Name',
                'IP Address',
                'URL',
                'Method',
                'Logged At',
                'Created At'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->level,
                    $log->channel,
                    $log->environment,
                    $log->message,
                    $log->exception_class,
                    $log->user_id,
                    $log->user?->name,
                    $log->ip_address,
                    $log->url,
                    $log->method,
                    formatUserDateTime($log->logged_at),
                    formatUserDateTime($log->created_at),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download original log file.
     */
    public function downloadLogFile(Request $request)
    {
        $month = $request->get('month');
        $logType = $request->get('type', 'laravel');

        if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            abort(400, 'Invalid month format');
        }

        // Construct possible log file names for the month
        $logPath = storage_path('logs');
        $possibleFiles = [
            "{$logPath}/laravel-{$month}.log",
            "{$logPath}/{$logType}-{$month}.log",
            "{$logPath}/laravel.log", // Current log file
        ];

        $logFile = null;
        foreach ($possibleFiles as $file) {
            if (File::exists($file)) {
                $logFile = $file;
                break;
            }
        }

        if (!$logFile) {
            abort(404, 'Log file not found for the specified month');
        }

        $filename = basename($logFile);

        return Response::download($logFile, $filename, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
