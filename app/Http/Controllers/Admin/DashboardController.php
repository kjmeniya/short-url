<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\LoginLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /** @var User|null $user */
        $user = Auth::user();

        // Show user dashboard for regular users
        if ($user && $user->isUser()) {
            return $this->userDashboard();
        }

        $userStats = $this->getUserStatistics();
        $systemStats = $this->getSystemStatistics();
        $loginStats = $this->getLoginStatistics();
        $emailStats = $this->getEmailStatistics();
        $chartData = $this->getChartData();
        $recentActivity = $this->getRecentActivity();

        return view('admin.dashboard.index', compact(
            'userStats',
            'systemStats',
            'loginStats',
            'emailStats',
            'chartData',
            'recentActivity'
        ));
    }

    /**
     * Show the user dashboard for regular users.
     *
     * @return \Illuminate\View\View
     */
    protected function userDashboard()
    {
        $userId = Auth::id();

        $stats = $this->getUserLookupStats($userId);
        $chartData = $this->getUserChartData($userId);
        $recentLookups = $this->getRecentLookups($userId);

        return view('admin.dashboard.user', compact('stats', 'chartData', 'recentLookups'));
    }

    /**
     * Refresh dashboard statistics via AJAX.
     */
    public function refresh(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        $type = $request->get('type', 'all');
        $period = $request->get('period');

        // Handle user dashboard refresh
        if ($user && $user->isUser()) {
            $userId = $user->id;
            $data = match ($type) {
                'lookup_chart' => ['lookup_chart' => $this->getUserLookupChartData($userId, $period ?? 'week')],
                'stats' => $this->getUserLookupStats($userId),
                default => [
                    'stats' => $this->getUserLookupStats($userId),
                    'lookup_chart' => $this->getUserLookupChartData($userId, $period ?? 'week'),
                ]
            };

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
        }

        // Admin dashboard refresh
        $data = match ($type) {
            'user_growth' => ['user_growth' => $this->getUserGrowthData($period)],
            'users' => $this->getUserStatistics(),
            'system' => $this->getSystemStatistics(),
            'charts' => $this->getChartData(),
            default => [
                'users' => $this->getUserStatistics(),
                'system' => $this->getSystemStatistics(),
                'charts' => $this->getChartData(),
            ]
        };

        return response()->json([
            'success' => true,
            'data' => $data,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }



    /**
     * Export dashboard data.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'json');

        $data = [
            'user_statistics' => $this->getUserStatistics(),
            'system_statistics' => $this->getSystemStatistics(),
            'chart_data' => $this->getChartData(),
            'exported_at' => now()->format('Y-m-d H:i:s'),
            'exported_by' => auth()->user()->name ?? 'System'
        ];

        if ($format === 'csv') {
            return $this->exportToCsv($data);
        } elseif ($format === 'pdf') {
            return $this->exportToPdf($data);
        }

        // Default JSON export
        $filename = 'dashboard_statistics_' . now()->format('Y_m_d_H_i_s') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json');
    }

    /**
     * Print dashboard data.
     */
    public function print(Request $request)
    {
        $data = [
            'users' => $this->getUserStatistics(),
            'system' => $this->getSystemStatistics(),
            'charts' => $this->getChartData(),
        ];

        return view('admin.dashboard.print', compact('data'));
    }

    /**
     * Get detailed user statistics.
     */
    private function getUserStatistics(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'super_admins' => User::whereHas('role', function ($query) {
                $query->where('name', 'super_admin');
            })->count(),
            'recent_logins' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
        ];
    }

    /**
     * Get system statistics.
     */
    private function getSystemStatistics(): array
    {
        return [
            'roles' => Role::count(),
            'permissions' => Permission::count(),
            'email_templates' => EmailTemplate::count(),
            'database_size' => $this->getDatabaseSize(),
            'storage_usage' => $this->getStorageUsage(),
        ];
    }

    /**
     * Get login statistics.
     */
    private function getLoginStatistics(): array
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();

        return [
            'total' => LoginLog::count(),
            'today' => LoginLog::where('login_at', '>=', $today)->count(),
            'this_week' => LoginLog::where('login_at', '>=', $thisWeek)->count(),
            'this_month' => LoginLog::where('login_at', '>=', $thisMonth)->count(),
            'successful' => LoginLog::where('status', 'success')->count(),
            'failed' => LoginLog::where('status', 'failed')->count(),
            'suspicious' => LoginLog::where('is_suspicious', true)->count(),
            'success_rate' => $this->calculateLoginSuccessRate(),
        ];
    }

    /**
     * Get email statistics.
     */
    private function getEmailStatistics(): array
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();

        return [
            'total' => EmailLog::count(),
            'today' => EmailLog::where('created_at', '>=', $today)->count(),
            'this_week' => EmailLog::where('created_at', '>=', $thisWeek)->count(),
            'sent' => EmailLog::where('status', 'sent')->count(),
            'failed' => EmailLog::where('status', 'failed')->count(),
            'pending' => EmailLog::where('status', 'pending')->count(),
            'templates' => EmailTemplate::where('is_active', true)->count(),
        ];
    }

    /**
     * Get recent activity for dashboard.
     */
    private function getRecentActivity(): array
    {
        $recentLogins = LoginLog::with('user')
            ->where('status', 'success')
            ->orderBy('login_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($log) => [
                'type' => 'login',
                'user' => $log->user?->name ?? $log->name ?? 'Unknown',
                'email' => $log->email,
                'action' => 'Logged in',
                'time' => $log->login_at,
                'icon' => 'log-in',
                'color' => 'success',
            ]);

        $recentUsers = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn($user) => [
                'type' => 'user',
                'user' => $user->name,
                'email' => $user->email,
                'action' => 'Account created',
                'time' => $user->created_at,
                'icon' => 'user-plus',
                'color' => 'primary',
            ]);

        return $recentLogins->merge($recentUsers)
            ->sortByDesc('time')
            ->take(8)
            ->values()
            ->toArray();
    }

    /**
     * Calculate login success rate.
     */
    private function calculateLoginSuccessRate(): float
    {
        $total = LoginLog::count();
        if ($total === 0) {
            return 100.0;
        }
        $successful = LoginLog::where('status', 'success')->count();
        return round(($successful / $total) * 100, 1);
    }

    /**
     * Get chart data for dashboard visualizations.
     */
    private function getChartData(): array
    {
        return [
            'user_growth' => $this->getUserGrowthData(),
            'role_distribution' => $this->getRoleDistributionData(),
        ];
    }

    /**
     * Get user growth data for chart based on period.
     */
    private function getUserGrowthData(?string $period = null): array
    {
        $period = $period ?? 'year';
        $data = [];
        $labels = [];

        switch ($period) {
            case 'today':
                for ($i = 23; $i >= 0; $i--) {
                    $hour = now()->subHours($i);
                    $labels[] = $hour->format('H:00');
                    $data[] = User::whereDate('created_at', today())
                        ->whereRaw('HOUR(created_at) = ?', [$hour->hour])
                        ->count();
                }
                break;

            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->format('M j');
                    $data[] = User::whereDate('created_at', $date->toDateString())->count();
                }
                break;

            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->format('M j');
                    $data[] = User::whereDate('created_at', $date->toDateString())->count();
                }
                break;

            case 'year':
            default:
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $labels[] = $date->format('M Y');
                    $data[] = User::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count();
                }
                break;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }





    /**
     * Get role distribution data.
     */
    private function getRoleDistributionData(): array
    {
        $roles = Role::withCount('users')->get();

        return [
            'labels' => $roles->pluck('display_name')->toArray(),
            'data' => $roles->pluck('users_count')->toArray(),
        ];
    }

    /**
     * Get database size.
     */
    private function getDatabaseSize(): string
    {
        try {
            $size = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
            ")[0]->size_mb ?? 0;

            return $size . ' MB';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get storage usage information.
     */
    private function getStorageUsage(): array
    {
        try {
            $totalSpace = disk_total_space(storage_path());
            $freeSpace = disk_free_space(storage_path());
            $usedSpace = $totalSpace - $freeSpace;

            return [
                'total' => $this->formatBytes($totalSpace),
                'used' => $this->formatBytes($usedSpace),
                'free' => $this->formatBytes($freeSpace),
                'usage_percentage' => round(($usedSpace / $totalSpace) * 100, 2),
            ];
        } catch (\Exception $e) {
            return [
                'total' => '100 GB',
                'used' => '25 GB',
                'free' => '75 GB',
                'usage_percentage' => 25,
            ];
        }
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Export data to CSV format.
     */
    private function exportToCsv(array $data): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'dashboard_statistics_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, ['Category', 'Metric', 'Value']);

            // User Statistics
            foreach ($data['user_statistics'] as $key => $value) {
                fputcsv($handle, ['Users', ucwords(str_replace('_', ' ', $key)), $value]);
            }

            // System Statistics
            foreach ($data['system_statistics'] as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        fputcsv($handle, ['System', ucwords(str_replace('_', ' ', $key . ' ' . $subKey)), $subValue]);
                    }
                } else {
                    fputcsv($handle, ['System', ucwords(str_replace('_', ' ', $key)), $value]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Export data to PDF format.
     */
    private function exportToPdf(array $data): \Illuminate\Http\Response
    {
        // For now, return a simple HTML response that can be printed as PDF
        // In a real implementation, you might use a library like DomPDF or wkhtmltopdf

        $html = view('admin.dashboard.pdf', compact('data'))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="dashboard_statistics_' . now()->format('Y_m_d_H_i_s') . '.html"'
        ]);
    }

    // ==========================================
    // User Dashboard Methods
    // ==========================================

    /**
     * Get user's IP lookup statistics.
     */
    protected function getUserLookupStats(int $userId): array
    {
        // IpRequestLog removed - returning placeholder data
        return [
            'total' => 0,
            'today' => 0,
            'this_week' => 0,
            'this_month' => 0,
            'successful' => 0,
            'failed' => 0,
            'cached' => 0,
            'success_rate' => 0,
            'cache_hit_rate' => 0,
            'unique_ips' => 0,
            'single_lookups' => 0,
            'bulk_lookups' => 0,
            'avg_response_time' => 0,
        ];
    }

    /**
     * Get chart data for user dashboard.
     */
    protected function getUserChartData(int $userId): array
    {
        return [
            'lookup_chart' => $this->getUserLookupChartData($userId),
            'request_type_distribution' => $this->getRequestTypeDistribution($userId),
            'status_distribution' => $this->getStatusDistribution($userId),
        ];
    }

    /**
     * Get lookup chart data for a specific period.
     */
    protected function getUserLookupChartData(int $userId, string $period = 'week'): array
    {
        // IpRequestLog removed - returning empty chart data
        $labels = [];
        $successData = [];
        $failedData = [];

        switch ($period) {
            case 'today':
                for ($i = 23; $i >= 0; $i--) {
                    $hour = now()->subHours($i);
                    $labels[] = $hour->format('H:00');
                    $successData[] = 0;
                    $failedData[] = 0;
                }
                break;

            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->format('M j');
                    $successData[] = 0;
                    $failedData[] = 0;
                }
                break;

            case 'week':
            default:
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $labels[] = $date->format('D');
                    $successData[] = 0;
                    $failedData[] = 0;
                }
                break;
        }

        return [
            'labels' => $labels,
            'success' => $successData,
            'failed' => $failedData,
        ];
    }

    /**
     * Get request type distribution for pie chart.
     */
    protected function getRequestTypeDistribution(int $userId): array
    {
        // IpRequestLog removed - returning empty distribution
        return [
            'labels' => ['Single', 'Bulk', 'Current IP'],
            'data' => [0, 0, 0],
        ];
    }

    /**
     * Get status distribution for donut chart.
     */
    protected function getStatusDistribution(int $userId): array
    {
        // IpRequestLog removed - returning empty distribution
        return [
            'labels' => ['Success', 'Failed', 'Not Found', 'Rate Limited'],
            'data' => [0, 0, 0, 0],
        ];
    }

    /**
     * Get recent lookups for history table.
     */
    protected function getRecentLookups(int $userId, int $limit = 10): \Illuminate\Support\Collection
    {
        // IpRequestLog removed - returning empty collection
        return collect([]);
    }
}
