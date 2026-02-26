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
        padding: 56px 0 40px;
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

    .gl-dest {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
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

    /* ── Card Design ── */
    .mock-result-row {
        background: rgba(var(--bs-primary-rgb), .01) !important;
        transition: box-shadow .2s;
    }

    .mock-result-row:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, .08) !important;
    }

    .mock-action-btn {
        width: 34px;
        height: 34px;
        border: 1px solid rgba(128, 128, 128, .15);
        background: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #555;
        cursor: pointer;
        transition: all .2s;
    }

    .mock-action-btn:hover {
        background: rgba(var(--bs-primary-rgb), .05);
        color: var(--bs-primary);
        border-color: rgba(var(--bs-primary-rgb), .3);
    }

    table.dataTable.table-borderless tbody tr td {
        padding: 0;
        border: none !important;
    }

    table.dataTable.table-borderless tbody tr {
        background: transparent !important;
        box-shadow: none !important;
    }

    div.dataTables_wrapper .row {
        margin: 0;
    }
</style>
@endpush

@section('content')

{{-- ── Hero ── --}}
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

{{-- ── Main content ── --}}
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

    {{-- ── Tip banner ── --}}
    <div class="alert alert-primary alert-dismissible d-flex align-items-start gap-3 rounded-3 mb-4"
        role="alert" style="border:none;background:rgba(var(--bs-primary-rgb),.07);">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="flex-shrink-0 mt-1 text-primary">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <div class="small">
            These links are tied to <strong>this browser</strong> via a cookie. Clearing cookies will lose access to this list.
            <a href="{{ route('auth.register') }}" class="text-primary fw-semibold">Create a free account</a>
            to save them permanently and unlock analytics.
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    {{-- ── Table Card ── --}}
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    {{-- Card header --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                        <h6 class="card-title mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="me-2 text-primary" style="vertical-align:-2px">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                            </svg>
                            My Short Links
                        </h6>
                        <a href="{{ route('front.home') }}" class="btn btn-primary btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="me-1" style="vertical-align:-2px">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                            Shorten New Link
                        </a>
                    </div>

                    {{-- ── Table ── --}}
                    <div class="mb-3">
                        <table id="guestLinksTable" class="table table-borderless w-100" style="border-spacing: 0 10px; border-collapse: separate;">
                            <thead class="d-none">
                                <tr>
                                    <th>Link</th>
                                    <th>Short URL</th>
                                    <th>Destination</th>
                                    <th>Clicks</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    {{-- ── Custom pagination row (identical to admin) ── --}}
                    <div class="row gap-2 gap-sm-0">
                        <div class="col-12 col-sm-6 d-flex align-items-center justify-content-center justify-content-sm-start gap-2 flex-wrap">
                            <select id="customLength" class="form-select form-select-sm" style="width:auto;display:inline-block;">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <div id="customTableInfo" class="text-muted small"></div>
                        </div>
                        <div class="col-12 col-sm-6 d-flex align-items-center justify-content-center justify-content-sm-end">
                            <nav aria-label="Page navigation">
                                <ul id="customPagination" class="pagination mb-0 pagination-sm"></ul>
                            </nav>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @endif

    {{-- ── Bottom nudge ── --}}
    <div class="text-center mt-4">
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold" id="qrCodeModalLabel">QR Code</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pb-4 pt-3">
                <div id="modalQrDisplay" class="d-inline-block bg-white p-2 rounded shadow-sm border mb-3" style="width:180px;height:180px;">
                    <!-- SVG rendered here -->
                </div>
                <p class="small text-muted mb-3">Scan this QR code or download it to share.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-primary btn-sm rounded-pill px-3" id="btnDownloadQrPng">Download PNG</button>
                    <button class="btn btn-outline-secondary btn-sm rounded-pill px-3" id="btnDownloadQrSvg">Download SVG</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/datatables.net/dataTables.min.js') }}"></script>
<script src="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
    $(document).ready(function() {

        if (!$('#guestLinksTable').length) return;

        var statusColors = {
            active: 'success',
            inactive: 'secondary',
            expired: 'danger'
        };

        var copyIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>';
        var openIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';
        var checkIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

        // ── DataTable ─────────────────────────────────────────────────────────────
        var table = $('#guestLinksTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("front.guest-links.data") }}',
                type: 'GET',
                error: function(xhr) {
                    console.error('DataTables AJAX error:', xhr.status, xhr.responseText);
                }
            },
            order: [
                [5, 'desc']
            ], // 'Created' column is now at index 5
            pageLength: 25,
            columns: [{
                    data: null,
                    orderable: false,
                    className: 'p-0',
                    render: function(d) {
                        var sc = statusColors[d.status] || 'secondary';
                        var shortDisplay = d.short_url;
                        var originHost = '';
                        try {
                            originHost = new URL(d.original_url).hostname || '';
                        } catch (e) {}
                        var originTrunc = d.original_url.length > 55 ? d.original_url.slice(0, 52) + '…' : d.original_url;
                        var qrHtml = (d.qr_code || '').replace(/'/g, "&apos;");

                        return `
                        <div class="col-12 w-100">
                          <div class="mock-result-row d-flex align-items-center gap-3 p-3 rounded-3 flex-column flex-sm-row bg-white border guest-link-card mb-2 w-100">
                            <div class="flex-shrink-0 bg-white p-1 rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width:48px;height:48px;border:1px solid rgba(0,0,0,.04);">
                              <img src="https://www.google.com/s2/favicons?domain=${originHost}&sz=128" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\\'http://www.w3.org/2000/svg\\' viewBox=\\'0 0 24 24\\' fill=\\'none\\' stroke=\\'%23999\\' stroke-width=\\'2\\'><circle cx=\\'12\\' cy=\\'12\\' r=\\'10\\'/><line x1=\\'2\\' y1=\\'12\\' x2=\\'22\\' y2=\\'12\\'/><path d=\\'M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z\\'/></svg>'" alt="icon" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
                            </div>
                            
                            <div class="flex-grow-1 text-center text-sm-start overflow-hidden">
                              <div style="font-size:.7rem;opacity:.5;margin-bottom:2px;" class="fw-semibold text-truncate" title="${d.original_url}">${originTrunc}</div>
                              <div class="fw-bold text-truncate text-primary" style="font-size:.9rem;">
                                ${shortDisplay}
                              </div>
                              <div class="d-flex align-items-center gap-2 mt-1 justify-content-center justify-content-sm-start flex-wrap">
                                <span class="badge bg-${sc} bg-opacity-15 font-monospace" style="font-size:.65rem;font-weight:700;">
                                  ${(d.status||'').toUpperCase()}
                                </span>
                                <span class="text-muted" style="font-size:.7rem;"><i data-lucide="bar-chart-2" style="width:11px;height:11px;display:inline-block;"></i> ${d.clicks} clicks</span>
                                <span class="text-muted" style="font-size:.7rem;">• ${d.created_at}</span>
                              </div>
                            </div>

                            <div class="d-flex gap-2 flex-shrink-0">
                              <button class="mock-action-btn gl-copy" data-url="${d.short_url}" title="Copy">
                                ${copyIcon}
                              </button>
                              <button type="button" class="mock-action-btn gl-qr" data-url="${d.short_url}" data-code="${d.code}" data-qr='${qrHtml}' title="QR Code" data-bs-toggle="modal" data-bs-target="#qrCodeModal">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                  <rect x="3" y="3" width="7" height="7" rx="1" />
                                  <rect x="14" y="3" width="7" height="7" rx="1" />
                                  <rect x="14" y="14" width="7" height="7" rx="1" />
                                  <rect x="3" y="14" width="7" height="7" rx="1" />
                                </svg>
                              </button>
                              <a href="${d.short_url}" target="_blank" class="mock-action-btn" title="Open">
                                ${openIcon}
                              </a>
                            </div>
                          </div>
                        </div>`;
                    }
                },
                {
                    data: 'short_url',
                    visible: false
                },
                {
                    data: 'original_url',
                    visible: false
                },
                {
                    data: 'clicks',
                    visible: false
                },
                {
                    data: 'status',
                    visible: false
                },
                {
                    data: 'created_at',
                    visible: false
                }
            ],
            drawCallback: function() {
                var info = table.page.info();
                // Update hero count badge
                var totalEl = document.getElementById('glTotalCount');
                if (totalEl) totalEl.textContent = info.recordsTotal.toLocaleString();
                // Custom info text (same as admin)
                $('#customTableInfo').html(
                    'Showing ' + (info.start + 1) + ' to ' + info.end + ' of ' + info.recordsTotal + ' entries'
                );
                renderPagination(info.page, info.pages);
            },
            language: {
                processing: '<div class="d-flex justify-content-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading…</span></div></div>',
                emptyTable: '<div class="py-4 text-center text-muted">No links found. <a href="{{ route("front.home") }}">Shorten your first URL →</a></div>',
                zeroRecords: '<div class="py-4 text-center text-muted">No records found.</div>',
            },
            dom: 'rt', // table only — custom controls handle everything
            responsive: true,
        });

        // ── Pagination renderer (identical to admin) ──────────────────────────────
        function renderPagination(currentPage, totalPages) {
            if (totalPages <= 1) {
                $('#customPagination').html('');
                return;
            }

            var html = '<li class="page-item ' + (currentPage === 0 ? 'disabled' : '') + '">' +
                '<a class="page-link prev-page" href="#" aria-label="Previous">‹</a></li>';

            var start = Math.max(currentPage - 2, 0);
            var end = Math.min(start + 5, totalPages);
            for (var i = start; i < end; i++) {
                html += '<li class="page-item ' + (i === currentPage ? 'active' : '') + '">' +
                    '<a class="page-link page-btn" href="#" data-page="' + i + '">' + (i + 1) + '</a></li>';
            }
            html += '<li class="page-item ' + (currentPage === totalPages - 1 ? 'disabled' : '') + '">' +
                '<a class="page-link next-page" href="#" aria-label="Next">›</a></li>';

            $('#customPagination').html(html);
        }

        // ── Pagination events (identical to admin) ────────────────────────────────
        $(document).on('click', '.page-btn', function(e) {
            e.preventDefault();
            table.page($(this).data('page')).draw('page');
        });
        $(document).on('click', '.prev-page', function(e) {
            e.preventDefault();
            table.page('previous').draw('page');
        });
        $(document).on('click', '.next-page', function(e) {
            e.preventDefault();
            table.page('next').draw('page');
        });

        // ── Length change ─────────────────────────────────────────────────────────
        $('#customLength').on('change', function() {
            table.page.len($(this).val()).draw();
        });

        // ── Copy button ───────────────────────────────────────────────────────────
        $('#guestLinksTable').on('click', '.gl-copy', function() {
            var btn = $(this);
            var url = btn.data('url');
            var orig = btn.html();
            navigator.clipboard.writeText(url).then(function() {
                btn.html(checkIcon).css('color', 'var(--bs-success)');
                setTimeout(function() {
                    btn.html(orig).css('color', '');
                }, 2000);
            });
        });

        // ── QR Modal Display ──────────────────────────────────────────────────────
        var currentShortCode = '';
        const modalQrDisplay = document.getElementById('modalQrDisplay');
        const btnDownloadQrSvg = document.getElementById('btnDownloadQrSvg');
        const btnDownloadQrPng = document.getElementById('btnDownloadQrPng');

        $('#guestLinksTable').on('click', '.gl-qr', function() {
            var btn = $(this);
            var qrSvgHtml = btn.data('qr');
            currentShortCode = btn.data('code') || '';
            if (modalQrDisplay && qrSvgHtml) {
                modalQrDisplay.innerHTML = qrSvgHtml;
                const svg = modalQrDisplay.querySelector('svg');
                if (svg) {
                    svg.setAttribute('width', '100%');
                    svg.setAttribute('height', '100%');
                }
            }
        });

        // ── QR Downloads ──────────────────────────────────────────────────────────
        function triggerDownload(url, filename) {
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        if (btnDownloadQrSvg) {
            btnDownloadQrSvg.addEventListener('click', function() {
                const svg = modalQrDisplay.querySelector('svg');
                if (!svg) return;
                const clonedSvg = svg.cloneNode(true);
                clonedSvg.setAttribute('width', '1000');
                clonedSvg.setAttribute('height', '1000');
                const svgData = new XMLSerializer().serializeToString(clonedSvg);
                const blob = new Blob([svgData], {
                    type: 'image/svg+xml;charset=utf-8'
                });
                const url = URL.createObjectURL(blob);
                const fileName = currentShortCode ? (currentShortCode + '.svg') : 'qrcode.svg';
                triggerDownload(url, fileName);
                setTimeout(() => URL.revokeObjectURL(url), 100);
            });
        }

        if (btnDownloadQrPng) {
            btnDownloadQrPng.addEventListener('click', function() {
                const svg = modalQrDisplay.querySelector('svg');
                if (!svg) return;
                const size = 1000;
                const clonedSvg = svg.cloneNode(true);
                clonedSvg.setAttribute('width', size);
                clonedSvg.setAttribute('height', size);

                const svgData = new XMLSerializer().serializeToString(clonedSvg);
                const blob = new Blob([svgData], {
                    type: 'image/svg+xml;charset=utf-8'
                });
                const url = URL.createObjectURL(blob);

                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = size;
                    canvas.height = size;
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, size, size);
                    ctx.drawImage(img, 0, 0, size, size);
                    URL.revokeObjectURL(url);

                    const fileName = currentShortCode ? (currentShortCode + '.png') : 'qrcode.png';
                    canvas.toBlob(function(pngBlob) {
                        const pngUrl = URL.createObjectURL(pngBlob);
                        triggerDownload(pngUrl, fileName);
                        setTimeout(() => URL.revokeObjectURL(pngUrl), 100);
                    }, 'image/png', 1.0);
                };
                img.src = url;
            });
        }

    });
</script>
@endpush