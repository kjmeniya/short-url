<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShortUrlRequest;
use App\Http\Requests\UpdateShortUrlRequest;
use App\Models\ShortUrl;
use App\Models\ShortUrlClick;
use App\Services\ShortUrlService;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ShortUrlController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    public function __construct(protected ShortUrlService $service)
    {
    }

    // ── Index (DataTables) ─────────────────────────────────────────────────────

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Admin sees ALL links — no ownerId filter
            $query = ShortUrl::with('creator')->select('short_urls.*');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $query = $this->applyDateFilter($query, $request);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="icon-sm" data-lucide="more-horizontal"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="' . route('admin.short-urls.show', $row->id) . '">
                                    <i class="icon-sm me-2" data-lucide="eye"></i>View
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="' . route('admin.short-urls.analytics', $row->id) . '">
                                    <i class="icon-sm me-2" data-lucide="bar-chart-3"></i>Analytics
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="' . route('admin.short-urls.edit', $row->id) . '">
                                    <i class="icon-sm me-2" data-lucide="edit"></i>Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item copy-url" href="javascript:void(0)" data-url="' . $row->short_url . '">
                                    <i class="icon-sm me-2" data-lucide="copy"></i>Copy Short URL
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger delete-short-url" href="javascript:void(0)" data-id="' . $row->id . '">
                                    <i class="icon-sm me-2" data-lucide="trash-2"></i>Delete
                                </a>
                            </li>
                        </ul>
                    </div>';
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'active'   => 'success',
                        'inactive' => 'secondary',
                        'expired'  => 'danger',
                    ];
                    $color = $badges[$row->status] ?? 'secondary';
                    $html  = '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
                    
                    if ($row->isPrivate()) {
                        $html .= '<div class="mt-1"><span class="badge bg-danger bg-opacity-15 text-danger border border-danger shadow-sm" style="font-size:0.65rem;"><i data-lucide="shield" style="width:10px;height:10px;"></i> Private</span></div>';
                    }
                    if ($row->is24hStory()) {
                        $html .= '<div class="mt-1"><span class="badge bg-primary bg-opacity-15 text-primary border border-primary shadow-sm" style="font-size:0.65rem;"><i data-lucide="clock" style="width:10px;height:10px;"></i> 24h</span></div>';
                    }
                    if ($row->isOneTime()) {
                        $html .= '<div class="mt-1"><span class="badge bg-secondary bg-opacity-25 text-dark border border-secondary shadow-sm" style="font-size:0.65rem;"><i data-lucide="zap" style="width:10px;height:10px;"></i> One-time</span></div>';
                    }
                    if ($row->password) {
                        $html .= '<div class="mt-1"><span class="badge bg-warning bg-opacity-15 text-warning border border-warning shadow-sm" style="font-size:0.65rem;"><i data-lucide="lock" style="width:10px;height:10px;"></i> Password</span></div>';
                    }
                    
                    if ($row->ipBlocks->count()) {
                        $html .= '<div class="mt-1"><span class="badge bg-danger bg-opacity-15 text-danger border border-danger shadow-sm" style="font-size:0.65rem;"><i data-lucide="shield-alert" style="width:10px;height:10px;"></i> IP Blocks</span></div>';
                    }
                    
                    if ($row->redirect_delay > 0) {
                        $html .= '<div class="mt-1"><span class="badge bg-dark bg-opacity-10 text-dark border border-dark shadow-sm" style="font-size:0.65rem;"><i data-lucide="timer" style="width:10px;height:10px;"></i> Delay: ' . $row->redirect_delay . 's</span></div>';
                    }
                    
                    if ($row->mobile_url || $row->tablet_url || $row->desktop_url || $row->office_url || $row->after_hours_url) {
                        $html .= '<div class="mt-1"><span class="badge bg-success bg-opacity-15 text-success border border-success shadow-sm" style="font-size:0.65rem;"><i data-lucide="git-branch" style="width:10px;height:10px;"></i> Smart Rules</span></div>';
                    }
                    
                    if ($row->og_title || $row->og_description || $row->og_image) {
                        $html .= '<div class="mt-1"><span class="badge bg-info bg-opacity-15 text-info border border-info shadow-sm" style="font-size:0.65rem;"><i data-lucide="image" style="width:10px;height:10px;"></i> Social</span></div>';
                    }
                    
                    return $html;
                })
                ->addColumn('short_url_link', function ($row) {
                    $url = $row->short_url;
                    return '<a href="' . $url . '" target="_blank" class="text-primary">' . $url . '</a>';
                })
                ->addColumn('original_url_truncated', function ($row) {
                    return '<span title="' . e($row->original_url) . '">' . Str::limit($row->original_url, 50) . '</span>';
                })
                ->addColumn('creator_name', fn($row) => $row->creator?->name ?? 'N/A')
                ->editColumn('clicks', fn($row) => number_format($row->clicks))
                ->editColumn('created_at', fn($row) => $row->created_at->format('M d, Y g:i A'))
                ->editColumn('expires_at', fn($row) => $row->expires_at
                    ? $row->expires_at->format('M d, Y')
                    : '<span class="text-muted">Never</span>')
                ->rawColumns(['action', 'status_badge', 'short_url_link', 'original_url_truncated', 'expires_at'])
                ->make(true);
        }

        // Admin stats — all links
        $stats = $this->service->getStats();

        $viewData = $this->withSeo(
            compact('stats'),
            'Short URL Management',
            'Manage and track your shortened URLs.',
            'short urls, url shortener, link management'
        );

        return view('admin.short-urls.index', $viewData);
    }

    // ── Create ─────────────────────────────────────────────────────────────────

    public function create()
    {
        $viewData = $this->withSeo(
            [],
            'Create Short URL',
            'Create a new shortened URL.',
            'create short url, new link, url shortener'
        );

        return view('admin.short-urls.create', $viewData);
    }

    // ── Store ──────────────────────────────────────────────────────────────────

    public function store(StoreShortUrlRequest $request)
    {
        // Admin can explicitly set status; default to 'active' handled by service.
        $data = $request->validated();

        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        $this->service->create($data, Auth::id());

        return redirect()->route('admin.short-urls.index')
            ->with('success', 'Short URL created successfully.');
    }

    // ── Show ───────────────────────────────────────────────────────────────────

    public function show(Request $request, $id)
    {
        $shortUrl = $this->service->findOrFail((int) $id);
        $devices  = \App\Models\ShortUrlClick::topBy($shortUrl->id, 'device_type', 4);

        $viewData = $this->withSeo(
            compact('shortUrl', 'devices'),
            'Short URL — ' . ($shortUrl->title ?: $shortUrl->code),
            "Details for short URL #{$shortUrl->code}.",
            'short url details, link info'
        );

        return view('admin.short-urls.show', $viewData);
    }

    // ── Slug Availability Check (AJAX) ─────────────────────────────────────────

    /**
     * AJAX: check if a slug is available (and not reserved).
     * GET /admin/short-urls/check-slug?slug=foo&exclude_id=5
     */
    public function checkSlug(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->service->checkSlugAvailability(
            slug:      (string) $request->input('slug', ''),
            excludeId: $request->integer('exclude_id') ?: null,
        );
    }


    public function analytics(Request $request, $id)
    {
        $shortUrl = $this->service->findOrFail((int) $id);

        $days = (int) $request->get('days', 30);
        $days = in_array($days, [7, 30, 90]) ? $days : 30;

        $clicksOverTime = ShortUrlClick::clicksOverTime($id, $days);
        $browsers       = ShortUrlClick::topBy($id, 'browser');
        $operatingSys   = ShortUrlClick::topBy($id, 'os');
        $devices        = ShortUrlClick::topBy($id, 'device_type');
        $countries      = ShortUrlClick::topBy($id, 'country');
        $referrers      = ShortUrlClick::topBy($id, 'referrer_domain');

        $totalClicks  = ShortUrlClick::where('short_url_id', $id)->count();
        $todayClicks  = ShortUrlClick::where('short_url_id', $id)
            ->whereDate('clicked_at', today())->count();
        $uniqueIPs    = ShortUrlClick::where('short_url_id', $id)
            ->distinct('ip_address')->count('ip_address');
        $mobileClicks = ShortUrlClick::where('short_url_id', $id)
            ->where('device_type', 'mobile')->count();

        $recentClicks = ShortUrlClick::where('short_url_id', $id)
            ->orderByDesc('clicked_at')
            ->limit(50)
            ->get();

        $viewData = $this->withSeo(
            compact(
                'shortUrl', 'days', 'clicksOverTime', 'browsers', 'operatingSys',
                'devices', 'countries', 'referrers', 'totalClicks', 'todayClicks',
                'uniqueIPs', 'mobileClicks', 'recentClicks'
            ),
            'Analytics — ' . ($shortUrl->title ?: $shortUrl->code),
            "Click analytics for short URL #{$shortUrl->code}.",
            'short url analytics, link clicks, tracking'
        );

        return view('admin.short-urls.analytics', $viewData);
    }

    // ── Edit ───────────────────────────────────────────────────────────────────

    public function edit($id)
    {
        $shortUrl = $this->service->findOrFail((int) $id);

        $viewData = $this->withSeo(
            compact('shortUrl'),
            'Edit Short URL',
            "Edit short URL #{$shortUrl->code}.",
            'edit short url, update link'
        );

        return view('admin.short-urls.edit', $viewData);
    }

    // ── Update ─────────────────────────────────────────────────────────────────

    public function update(UpdateShortUrlRequest $request, $id)
    {
        $shortUrl = $this->service->findOrFail((int) $id);

        $this->service->update($shortUrl, $request->validated(), Auth::id());

        return redirect()->route('admin.short-urls.index')
            ->with('success', 'Short URL updated successfully.');
    }

    // ── Destroy ────────────────────────────────────────────────────────────────

    public function destroy($id)
    {
        try {
            $shortUrl = $this->service->findOrFail((int) $id);
            $this->service->delete($shortUrl);

            return response()->json(['success' => true, 'message' => 'Short URL deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting short URL: ' . $e->getMessage()], 500);
        }
    }

    // ── Bulk Actions ───────────────────────────────────────────────────────────

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:activate,deactivate,delete',
            'ids'    => 'required|array|min:1|max:100',
            'ids.*'  => 'required|integer|exists:short_urls,id',
        ]);

        $ids   = $request->ids;
        $count = count($ids);

        try {
            switch ($request->action) {
                case 'activate':
                    ShortUrl::whereIn('id', $ids)->update([
                        'status'     => 'active',
                        'updated_by' => Auth::id(),
                    ]);
                    $message = "{$count} short URLs activated.";
                    break;

                case 'deactivate':
                    ShortUrl::whereIn('id', $ids)->update([
                        'status'     => 'inactive',
                        'updated_by' => Auth::id(),
                    ]);
                    $message = "{$count} short URLs deactivated.";
                    break;

                case 'delete':
                    ShortUrl::whereIn('id', $ids)->each->delete();
                    $message = "{$count} short URLs deleted.";
                    break;
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ── Export ─────────────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $shortUrls = ShortUrl::with('creator')->get();
        $columns   = Schema::getColumnListing((new ShortUrl())->getTable());
        $filename  = 'short_urls_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return response()->stream(function () use ($shortUrls, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, array_map('ucfirst', $columns));

            foreach ($shortUrls as $url) {
                $row = [];
                foreach ($columns as $col) {
                    $val = $url->getAttribute($col);
                    if ($val instanceof \Carbon\Carbon) {
                        $row[] = $val->format('Y-m-d H:i:s');
                    } elseif (is_bool($val)) {
                        $row[] = $val ? 'Yes' : 'No';
                    } else {
                        $row[] = $val;
                    }
                }
                fputcsv($file, $row);
            }

            fclose($file);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
