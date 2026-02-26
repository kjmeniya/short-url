<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageVisit;
use App\Jobs\ProcessPageVisit;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;

class AnalyticsController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    protected $socketServerUrl;

    public function __construct()
    {
        $this->socketServerUrl = env('SOCKET_SERVER_URL', 'http://localhost:3000');
    }

    /**
     * Display the live users page (moved from LiveController)
     */
    public function live()
    {
        $title = 'Live Users';
        $description = 'Monitor live users accessing your application in real-time.';
        $keywords = 'live users, real-time monitoring, active sessions, user tracking';

        return view('admin.analytics.live', compact('title', 'description', 'keywords'));
    }

    /**
     * Get live user stats from Socket.IO server (moved from LiveController)
     */
    public function getLiveStats()
    {
        try {
            $response = Http::timeout(5)->get("{$this->socketServerUrl}/api/stats");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'total' => 0,
                'web' => 0,
                'mobile' => 0,
                'authenticated' => 0,
                'guest' => 0,
                'users' => []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch stats from Socket.IO server',
                'message' => $e->getMessage(),
                'total' => 0,
                'web' => 0,
                'mobile' => 0,
                'authenticated' => 0,
                'guest' => 0,
                'users' => []
            ], 500);
        }
    }

    /**
     * Display a listing of page visits (moved from PageVisitController)
     */
    public function pageViews(Request $request)
    {
        if ($request->ajax()) {
            $query = PageVisit::with('user:id,name,email,avatar')
                ->select(['id', 'user_id', 'guest_id', 'page', 'platform', 'device', 'ip', 'visited_at']);

            // Apply filters
            if ($request->filled('platform')) {
                $query->where('platform', $request->platform);
            }
            if ($request->filled('device')) {
                $query->where('device', $request->device);
            }
            if ($request->filled('user_type')) {
                if ($request->user_type === 'authenticated') {
                    $query->whereNotNull('user_id');
                } else {
                    $query->whereNull('user_id');
                }
            }

            // Apply date filter
            $query = $this->applyDateFilter($query, $request, 'visited_at');

            return DataTables::of($query)
                ->filterColumn('user_info', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->whereHas('user', function ($uq) use ($keyword) {
                            $uq->where('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%");
                        })
                            ->orWhere('guest_id', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('user_info', function ($visit) {
                    $placeholder = asset('build/images/others/placeholder.jpg');
                    if ($visit->user) {
                        $avatar = $visit->user->avatar ? asset($visit->user->avatar) : $placeholder;
                        return '<div class="d-flex align-items-center">
                                    <img src="' . $avatar . '" class="user-avatar me-2" alt="' . e($visit->user->name) . '">
                                    <div class="d-flex flex-column text-start">
                                        <div class="fw-bold">' . e($visit->user->name) . '</div>
                                        <small class="text-muted">' . e($visit->user->email) . '</small>
                                    </div>
                                </div>';
                    }
                    return '<div class="d-flex align-items-center">
                                <img src="' . $placeholder . '" class="user-avatar me-2" alt="Guest">
                                <div class="d-flex flex-column text-start">
                                    <div class="fw-bold">Guest</div>
                                    <small class="text-muted">' . substr($visit->guest_id, 0, 8) . '...</small>
                                </div>
                            </div>';
                })
                ->editColumn('visited_at', function ($visit) {
                    return formatUserDateTime($visit->visited_at);
                })
                ->editColumn('platform', function ($visit) {
                    $badges = [
                        'web' => 'bg-info',
                        'admin' => 'bg-danger',
                        'app' => 'bg-success'
                    ];
                    $badgeClass = $badges[$visit->platform] ?? 'bg-secondary';
                    return '<span class="badge ' . $badgeClass . '">' . ucfirst($visit->platform) . '</span>';
                })
                ->addColumn('device_info', function ($visit) {
                    $icons = [
                        'desktop' => 'monitor',
                        'mobile' => 'smartphone',
                        'tablet' => 'tablet'
                    ];
                    $icon = $icons[$visit->device] ?? 'help-circle';
                    return '<div class="d-flex align-items-center"><i data-lucide="' . $icon . '" class="icon-sm me-2"></i>' . ucfirst($visit->device) . '</div>';
                })
                ->rawColumns(['user_info', 'platform', 'device_info'])
                ->make(true);
        }

        $stats = [
            'total_visits' => PageVisit::count(),
            'unique_users' => PageVisit::distinct('user_id')->whereNotNull('user_id')->count(),
            'unique_guests' => PageVisit::distinct('guest_id')->whereNull('user_id')->count(),
            'today_visits' => PageVisit::whereDate('visited_at', today())->count()
        ];

        $viewData = $this->withSeo(
            compact('stats'),
            'Page Views',
            'Track and analyze page views across all platforms and devices.',
            'page views, analytics, traffic tracking, visitor data'
        );

        return view('admin.analytics.page-views', $viewData);
    }

    /**
     * Handle page visit tracking from Socket Server (Original method)
     */
    public function pageVisit(Request $request)
    {
        // Simple internal token check
        $token = $request->header('X-Internal-Token');
        if ($token !== config('services.internal_analytics_token', 'secret')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if pageview storage is enabled
        if (!analytics_store_pageview()) {
            return response()->json([
                'success' => true,
                'message' => 'Pageview tracking is disabled'
            ]);
        }

        // Check for excluded IP addresses
        $excludeIps = analytics_exclude_ips();
        if ($excludeIps) {
            $ipList = array_map('trim', explode(',', $excludeIps));
            if (in_array($request->ip(), $ipList)) {
                return response()->json([
                    'success' => true,
                    'message' => 'IP address is excluded from tracking'
                ]);
            }
        }

        $data = $request->all();

        // Dispatch the job to handle deduplication and storage
        ProcessPageVisit::dispatch($data);

        return response()->json([
            'success' => true,
            'message' => 'Page visit queued'
        ]);
    }
}
