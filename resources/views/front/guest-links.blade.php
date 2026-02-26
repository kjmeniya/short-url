@extends('front.layout.master', [
'page_title' => 'My Shortened Links',
'page_description' => 'View and manage all the short links you have created in this browser.',
])

@push('style')
<link href="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<style>
    .gl-hero {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), .06) 0%, transparent 60%);
        border-bottom: 1px solid rgba(var(--bs-primary-rgb), .08);
        padding: 64px 0 48px;
    }

    .gl-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: .82rem;
        font-weight: 600;
        background: rgba(var(--bs-primary-rgb), .08);
        color: var(--bs-primary);
    }

    /* DataTable overrides */
    #guestLinksTable_wrapper .dataTables_filter input {
        border-radius: 8px;
        padding: 6px 12px;
        border: 1px solid rgba(128, 128, 128, .2);
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
    }

    #guestLinksTable_wrapper .dataTables_length select {
        border-radius: 8px;
        padding: 5px 10px;
        border: 1px solid rgba(128, 128, 128, .2);
        background: var(--bs-body-bg);
        color: var(--bs-body-color);
    }

    #guestLinksTable thead th {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        white-space: nowrap;
        background: var(--bs-body-bg);
        border-bottom: 2px solid rgba(128, 128, 128, .12);
        padding: 12px 14px;
    }

    #guestLinksTable tbody td {
        vertical-align: middle;
        padding: 13px 14px;
        font-size: .88rem;
    }

    #guestLinksTable tbody tr {
        transition: background .15s;
    }

    #guestLinksTable tbody tr:hover {
        background: rgba(var(--bs-primary-rgb), .03);
    }

    .gl-short-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 6px;
        background: rgba(var(--bs-primary-rgb), .1);
        color: var(--bs-primary);
        font-weight: 600;
        font-size: .8rem;
        white-space: nowrap;
    }

    .gl-dest {
        max-width: 320px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }

    .gl-actions {
        display: flex;
        gap: 6px;
    }

    .gl-actions a,
    .gl-actions button {
        border-radius: 8px;
    }

    .gl-empty {
        padding: 80px 0;
        text-align: center;
    }

    .gl-empty-icon {
        width: 56px;
        height: 56px;
        opacity: .15;
        margin: 0 auto 16px;
        display: block;
    }
</style>
@endpush

@section('content')

{{-- Hero --}}
<div class="gl-hero">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <a href="{{ route('front.home') }}" class="text-muted small d-inline-flex align-items-center gap-1 mb-3 text-decoration-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    Back to Home
                </a>
                <h1 class="h3 fw-bold mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-primary" style="vertical-align:-3px">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                    </svg>
                    My Shortened Links
                </h1>
                <p class="text-muted mb-0">All links you created in this browser session are listed below.</p>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="gl-stat-pill">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                    </svg>
                    <span id="glTotalCount">{{ number_format($total) }}</span> link{{ $total !== 1 ? 's' : '' }}
                </div>
                <a href="{{ route('auth.register') }}" class="btn btn-primary rounded-pill btn-sm px-4">
                    Create Free Account →
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Main content --}}
<div class="container py-5">

    @if($total === 0)
    {{-- Empty state --}}
    <div class="gl-empty">
        <svg class="gl-empty-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
        </svg>
        <h4 class="fw-semibold mb-2">No links yet</h4>
        <p class="text-muted mb-4">Shorten your first URL on the homepage.</p>
        <a href="{{ route('front.home') }}" class="btn btn-primary rounded-pill px-5">Get Started →</a>
    </div>
    @else
    {{-- Tip banner --}}
    <div class="alert alert-primary alert-dismissible d-flex align-items-start gap-3 rounded-3 mb-4" role="alert" style="border:none;background:rgba(var(--bs-primary-rgb),.07);">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0 mt-1 text-primary">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <div class="small">
            These links are tied to <strong>this browser</strong> via a cookie. Clearing cookies will lose access to this list.
            <a href="{{ route('auth.register') }}" class="text-primary fw-semibold">Create a free account</a> to save them permanently and unlock analytics.
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    {{-- DataTable card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="p-4 pb-0">
                <table id="guestLinksTable" class="table table-hover w-100 mb-0">
                    <thead>
                        <tr>
                            <th>Created</th>
                            <th>Short URL</th>
                            <th>Destination</th>
                            <th>Clicks</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Filled by DataTables AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Bottom nudge --}}
    <div class="text-center mt-5 pt-2">
        <p class="text-muted small mb-2">Want custom aliases, link expiry, password protection, and full analytics?</p>
        <a href="{{ route('auth.register') }}" class="btn btn-primary rounded-pill px-5">Create a Free Account →</a>
    </div>
</div>

@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/datatables.net/dataTables.min.js') }}"></script>
<script src="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.getElementById('guestLinksTable')) return;

        const statusColors = {
            active: 'success',
            inactive: 'secondary',
            expired: 'danger'
        };
        const copyIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>`;
        const openIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>`;

        const table = new DataTable('#guestLinksTable', {
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route("front.guest-links.data") }}',
                type: 'GET',
                error: function() {
                    console.warn('Could not load guest links data.');
                }
            },
            order: [
                [0, 'desc']
            ],
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            columns: [{
                    data: 'created_at',
                    title: 'Created',
                    width: '140px',
                    render: function(d) {
                        return `<span class="text-muted small">${d}</span>`;
                    }
                },
                {
                    data: 'short_url',
                    title: 'Short URL',
                    orderable: false,
                    render: function(d) {
                        return `<span class="gl-short-badge">${d}</span>`;
                    }
                },
                {
                    data: 'original_url',
                    title: 'Destination',
                    render: function(d) {
                        const truncated = d.length > 60 ? d.slice(0, 57) + '…' : d;
                        return `<span class="gl-dest text-muted" title="${d}">${truncated}</span>`;
                    }
                },
                {
                    data: 'clicks',
                    title: 'Clicks',
                    width: '80px',
                    className: 'text-center',
                    render: function(d) {
                        return `<span class="fw-semibold">${d}</span>`;
                    }
                },
                {
                    data: 'status',
                    title: 'Status',
                    width: '90px',
                    render: function(d) {
                        const c = statusColors[d] || 'secondary';
                        const label = d.charAt(0).toUpperCase() + d.slice(1);
                        return `<span class="badge bg-${c} bg-opacity-15 text-${c}" style="font-size:.72rem;">${label}</span>`;
                    }
                },
                {
                    data: 'short_url',
                    title: '',
                    orderable: false,
                    width: '100px',
                    className: 'text-end',
                    render: function(d) {
                        return `<div class="gl-actions justify-content-end">
            <button class="btn btn-sm btn-outline-secondary gl-copy" data-url="${d}" title="Copy">${copyIcon}</button>
            <a href="${d}" target="_blank" class="btn btn-sm btn-outline-primary" title="Open">${openIcon}</a>
          </div>`;
                    }
                }
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading…</span></div>',
                emptyTable: '<div class="py-4 text-muted">No links found.</div>',
                zeroRecords: '<div class="py-4 text-muted">No links match your search.</div>',
            },
            dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6 text-end'f>>" +
                "<'row'<'col-12'tr>>" +
                "<'row mt-3'<'col-sm-5'i><'col-sm-7 text-end'p>>",
            responsive: true,
        });

        // Update total count after each draw
        table.on('draw', function() {
            const info = table.page.info();
            const totalEl = document.getElementById('glTotalCount');
            if (totalEl) totalEl.textContent = info.recordsTotal.toLocaleString();
        });

        // Copy delegation
        document.getElementById('guestLinksTable').addEventListener('click', function(e) {
            const btn = e.target.closest('.gl-copy');
            if (!btn) return;
            const url = btn.dataset.url;
            navigator.clipboard.writeText(url).then(function() {
                const orig = btn.innerHTML;
                btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
                btn.style.color = 'var(--bs-success)';
                setTimeout(function() {
                    btn.innerHTML = orig;
                    btn.style.color = '';
                }, 2000);
            });
        });
    });
</script>
@endpush