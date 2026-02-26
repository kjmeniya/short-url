<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl;
use App\Models\ShortUrlClick;
use App\Traits\AdminSeoTrait;
use App\Traits\HasDateFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class ShortUrlController extends Controller
{
    use AdminSeoTrait, HasDateFilter;

    /**
     * Display a listing with DataTables support.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
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
                    return '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
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
                ->editColumn('expires_at', fn($row) => $row->expires_at ? $row->expires_at->format('M d, Y') : '<span class="text-muted">Never</span>')
                ->rawColumns(['action', 'status_badge', 'short_url_link', 'original_url_truncated', 'expires_at'])
                ->make(true);
        }

        $stats = ShortUrl::getStats();

        $viewData = $this->withSeo(
            compact('stats'),
            'Short URL Management',
            'Manage and track your shortened URLs.',
            'short urls, url shortener, link management'
        );

        return view('admin.short-urls.index', $viewData);
    }

    /**
     * Show the form for creating a new short URL.
     */
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

    /**
     * Store a newly created short URL.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'original_url'  => 'required|url|max:2083',
            'title'         => 'nullable|string|max:255',
            'custom_alias'  => 'nullable|string|max:100|unique:short_urls,custom_alias|regex:/^[a-zA-Z0-9_-]+$/',
            'status'        => 'required|in:active,inactive',
            'expires_at'    => 'nullable|date|after:now',
            'password'      => 'nullable|string|min:4|max:100',
        ], [
            'original_url.required' => 'The destination URL is required.',
            'original_url.url'      => 'Please enter a valid URL.',
            'custom_alias.unique'   => 'This custom alias is already taken.',
            'custom_alias.regex'    => 'The alias may only contain letters, numbers, hyphens, and underscores.',
            'expires_at.after'      => 'The expiry date must be in the future.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()
                ->with('error', 'Please correct the errors below.');
        }

        $validated = $validator->validated();
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        ShortUrl::create($validated);

        return redirect()->route('admin.short-urls.index')
            ->with('success', 'Short URL created successfully.');
    }

    /**
     * Display the specified short URL.
     */
    public function show(Request $request, $id)
    {
        $shortUrl = ShortUrl::findOrFail($id);

        $viewData = $this->withSeo(
            compact('shortUrl'),
            'Short URL — ' . ($shortUrl->title ?: $shortUrl->code),
            "Details for short URL #{$shortUrl->code}.",
            'short url details, link info'
        );

        return view('admin.short-urls.show', $viewData);
    }

    /**
     * Full analytics dashboard for a short URL.
     */
    public function analytics(Request $request, $id)
    {
        $shortUrl = ShortUrl::findOrFail($id);

        $days = (int) $request->get('days', 30);
        $days = in_array($days, [7, 30, 90]) ? $days : 30;

        // ── Chart data ────────────────────────────────────────────────────────
        $clicksOverTime = ShortUrlClick::clicksOverTime($id, $days);
        $browsers       = ShortUrlClick::topBy($id, 'browser');
        $operatingSys   = ShortUrlClick::topBy($id, 'os');
        $devices        = ShortUrlClick::topBy($id, 'device_type');
        $countries      = ShortUrlClick::topBy($id, 'country');
        $referrers      = ShortUrlClick::topBy($id, 'referrer_domain');

        // ── Quick stats ───────────────────────────────────────────────────────
        $totalClicks  = ShortUrlClick::where('short_url_id', $id)->count();
        $todayClicks  = ShortUrlClick::where('short_url_id', $id)
            ->whereDate('clicked_at', today())->count();
        $uniqueIPs    = ShortUrlClick::where('short_url_id', $id)
            ->distinct('ip_address')->count('ip_address');
        $mobileClicks = ShortUrlClick::where('short_url_id', $id)
            ->where('device_type', 'mobile')->count();

        // ── Recent clicks ─────────────────────────────────────────────────────
        $recentClicks = ShortUrlClick::where('short_url_id', $id)
            ->orderByDesc('clicked_at')
            ->limit(50)
            ->get();

        $viewData = $this->withSeo(
            compact(
                'shortUrl',
                'days',
                'clicksOverTime',
                'browsers',
                'operatingSys',
                'devices',
                'countries',
                'referrers',
                'totalClicks',
                'todayClicks',
                'uniqueIPs',
                'mobileClicks',
                'recentClicks'
            ),
            'Analytics — ' . ($shortUrl->title ?: $shortUrl->code),
            "Click analytics for short URL #{$shortUrl->code}.",
            'short url analytics, link clicks, tracking'
        );

        return view('admin.short-urls.analytics', $viewData);
    }


    /**
     * Show the form for editing the specified short URL.
     */
    public function edit($id)
    {
        $shortUrl = ShortUrl::findOrFail($id);

        $viewData = $this->withSeo(
            compact('shortUrl'),
            'Edit Short URL',
            "Edit short URL #{$shortUrl->code}.",
            'edit short url, update link'
        );

        return view('admin.short-urls.edit', $viewData);
    }

    /**
     * Update the specified short URL.
     */
    public function update(Request $request, $id)
    {
        $shortUrl = ShortUrl::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'original_url'  => 'required|url|max:2083',
            'title'         => 'nullable|string|max:255',
            'custom_alias'  => 'nullable|string|max:100|unique:short_urls,custom_alias,' . $shortUrl->id . '|regex:/^[a-zA-Z0-9_-]+$/',
            'status'        => 'required|in:active,inactive,expired',
            'expires_at'    => 'nullable|date',
            'password'      => 'nullable|string|min:4|max:100',
        ], [
            'original_url.required' => 'The destination URL is required.',
            'original_url.url'      => 'Please enter a valid URL.',
            'custom_alias.unique'   => 'This custom alias is already taken.',
            'custom_alias.regex'    => 'The alias may only contain letters, numbers, hyphens, and underscores.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()
                ->with('error', 'Please correct the errors below.');
        }

        $validated = $validator->validated();
        $validated['updated_by'] = Auth::id();

        // Only re-hash password if it was changed
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $shortUrl->update($validated);

        return redirect()->route('admin.short-urls.index')
            ->with('success', 'Short URL updated successfully.');
    }

    /**
     * Remove the specified short URL.
     */
    public function destroy($id)
    {
        try {
            ShortUrl::findOrFail($id)->delete();

            return response()->json(['success' => true, 'message' => 'Short URL deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting short URL: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Bulk actions.
     */
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
                    ShortUrl::whereIn('id', $ids)->update(['status' => 'active']);
                    $message = "{$count} short URLs activated.";
                    break;

                case 'deactivate':
                    ShortUrl::whereIn('id', $ids)->update(['status' => 'inactive']);
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

    /**
     * Export short URLs as CSV.
     */
    public function export(Request $request)
    {
        $shortUrls = ShortUrl::with('creator')->get();
        $columns   = Schema::getColumnListing((new ShortUrl())->getTable());

        $filename = 'short_urls_' . now()->format('Y_m_d_H_i_s') . '.csv';

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
