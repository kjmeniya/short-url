@extends('admin.layout.master')

@section('title', $title ?? 'Short URL Management')
@section('description', $description ?? 'Manage and track your shortened URLs.')
@section('keywords', $keywords ?? 'short urls, url shortener, link management')

@push('plugin-styles')
<link href="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
    .filter-chevron {
        transition: transform 0.2s ease-in-out;
    }

    .stats-card {
        border-left: 4px solid;
    }

    .stats-card.total {
        border-left-color: #6c757d;
    }

    .stats-card.active {
        border-left-color: #198754;
    }

    .stats-card.inactive {
        border-left-color: #ffc107;
    }

    .stats-card.expired {
        border-left-color: #dc3545;
    }

    .stats-card.clicks {
        border-left-color: #0d6efd;
    }

    .stats-card.this-month {
        border-left-color: #20c997;
    }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item active" aria-current="page">Short URL Management</li>
    </ol>
</nav>

{{-- ── Statistics ── --}}
<div class="card mb-3">
    <div class="card-header p-0">
        <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
            type="button" data-bs-toggle="collapse" data-bs-target="#statisticsCollapse" aria-expanded="true">
            <span><i data-lucide="bar-chart-3" class="icon-sm me-2"></i>Statistics</span>
            <i data-lucide="chevron-up" class="icon-sm stats-chevron"></i>
        </button>
    </div>
    <div class="collapse show" id="statisticsCollapse">
        <div class="card-body">
            <div class="row">
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
                    <div class="card stats-card total">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Total</h6>
                                    <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                                </div>
                                <i data-lucide="link" class="icon-lg text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
                    <div class="card stats-card active">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Active</h6>
                                    <h4 class="mb-0">{{ number_format($stats['active']) }}</h4>
                                </div>
                                <i data-lucide="check-circle" class="icon-lg text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
                    <div class="card stats-card inactive">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Inactive</h6>
                                    <h4 class="mb-0">{{ number_format($stats['inactive']) }}</h4>
                                </div>
                                <i data-lucide="pause-circle" class="icon-lg text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
                    <div class="card stats-card expired">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Expired</h6>
                                    <h4 class="mb-0">{{ number_format($stats['expired']) }}</h4>
                                </div>
                                <i data-lucide="clock" class="icon-lg text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
                    <div class="card stats-card clicks">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Total Clicks</h6>
                                    <h4 class="mb-0">{{ number_format($stats['total_clicks']) }}</h4>
                                </div>
                                <i data-lucide="mouse-pointer-click" class="icon-lg text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
                    <div class="card stats-card this-month">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">This Month</h6>
                                    <h4 class="mb-0">{{ number_format($stats['this_month']) }}</h4>
                                </div>
                                <i data-lucide="calendar" class="icon-lg text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Main Table Card ── --}}
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                    <h6 class="card-title mb-0">Short URL Management</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.short-urls.create') }}" class="btn btn-primary btn-sm">
                            <i data-lucide="plus" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Add Short URL</span>
                            <span class="d-sm-none">Add</span>
                        </a>
                        <a href="{{ route('admin.short-urls.export') }}" class="btn btn-outline-secondary btn-sm">
                            <i data-lucide="download" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Export CSV</span>
                            <span class="d-sm-none">Export</span>
                        </a>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-warning btn-sm dropdown-toggle"
                                data-bs-toggle="dropdown" id="bulkActionsBtn" disabled>
                                <i data-lucide="layers" class="icon-sm me-1"></i>
                                <span class="d-none d-sm-inline">Bulk Actions</span>
                                <span class="d-sm-none">Bulk</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item bulk-action" href="#" data-action="activate">
                                        <i data-lucide="check-circle" class="icon-sm me-2 text-success"></i>Activate Selected
                                    </a></li>
                                <li><a class="dropdown-item bulk-action" href="#" data-action="deactivate">
                                        <i data-lucide="pause-circle" class="icon-sm me-2 text-warning"></i>Deactivate Selected
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item bulk-action text-danger" href="#" data-action="delete">
                                        <i data-lucide="trash-2" class="icon-sm me-2"></i>Delete Selected
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                {{-- Filters --}}
                <div class="card mb-3">
                    <div class="card-header p-0">
                        <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
                            type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="false">
                            <span>
                                <i data-lucide="filter" class="icon-sm me-2"></i>Filters
                                <span id="activeFiltersCount" class="badge bg-primary ms-2" style="display:none;">0</span>
                            </span>
                            <i data-lucide="chevron-down" class="icon-sm filter-chevron"></i>
                        </button>
                    </div>
                    <div class="collapse" id="filtersCollapse">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label for="customSearchBox" class="form-label">Search</label>
                                    <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search URLs...">
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <label for="statusFilter" class="form-label">Status</label>
                                    <select id="statusFilter" class="form-select form-select-sm">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="expired">Expired</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <x-admin.date-filter />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-sm">
                                        <i data-lucide="x" class="icon-sm me-1"></i>Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <x-admin.date-range-modal />

                <div class="table-responsive mb-3">
                    <table id="shortUrlTable" class="table table-hover">
                        <thead>
                            <tr class="bg-dark">
                                <th width="40">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label" for="selectAll"></label>
                                    </div>
                                </th>
                                <th>ID</th>
                                <th>Short URL</th>
                                <th>Destination</th>
                                <th>Status</th>
                                <th>Clicks</th>
                                <th>Expires At</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div class="row gap-2 gap-sm-0">
                    <div class="col-12 col-sm-6 d-flex align-items-center justify-content-center justify-content-sm-start gap-2 flex-wrap">
                        <select id="customLength" class="form-select form-select-sm" style="width:auto;display:inline-block;">
                            @foreach(setting_options('admin_items_per_page') as $value => $label)
                            <option value="{{ $value }}" {{ admin_items_per_page() == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div id="customTableInfo" class="text-muted"></div>
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
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/datatables.net/dataTables.min.js') }}"></script>
<script src="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@vite(['resources/js/admin/admin-date-filter.js'])
@endpush

@push('custom-scripts')
<script>
    $(document).ready(function() {

        const dateFilter = new AdminDateFilter({
            onFilterChange: function() {
                updateActiveFiltersCount();
                table.ajax.reload();
            }
        });

        const table = $('#shortUrlTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.short-urls.index') }}",
                data: function(d) {
                    d.status = $('#statusFilter').val();
                    Object.assign(d, dateFilter.getAjaxData());
                }
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'align-content-center',
                    render: (data, type, row) =>
                        `<div class="form-check"><input class="form-check-input row-checkbox" type="checkbox" value="${row.id}"><label class="form-check-label"></label></div>`
                },
                {
                    data: 'id',
                    name: 'id',
                    className: 'align-content-center'
                },
                {
                    data: 'short_url_link',
                    name: 'code',
                    orderable: false,
                    searchable: false,
                    className: 'align-content-center'
                },
                {
                    data: 'original_url_truncated',
                    name: 'original_url',
                    orderable: false,
                    searchable: false,
                    className: 'align-content-center'
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    orderable: false,
                    searchable: false,
                    className: 'align-content-center'
                },
                {
                    data: 'clicks',
                    name: 'clicks',
                    className: 'align-content-center'
                },
                {
                    data: 'expires_at',
                    name: 'expires_at',
                    orderable: false,
                    searchable: false,
                    className: 'align-content-center'
                },
                {
                    data: 'creator_name',
                    name: 'creator_name',
                    orderable: false,
                    searchable: false,
                    className: 'align-content-center'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    className: 'align-content-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'align-content-center'
                },
            ],
            drawCallback: function() {
                const info = table.page.info();
                $('#customTableInfo').html(`Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} entries`);
                renderPagination(info.page, info.pages);
                if (typeof lucide !== 'undefined') lucide.createIcons();
            },
            order: [
                [1, 'desc']
            ],
            pageLength: <?= admin_items_per_page() ?>,
            responsive: true,
            dom: 'rt'
        });

        function renderPagination(currentPage, totalPages) {
            let html = `<li class="page-item ${currentPage === 0 ? 'disabled' : ''}">
            <a class="page-link prev-page" href="#" aria-label="Previous"><i data-lucide="chevron-left"></i></a></li>`;
            let start = Math.max(currentPage - 2, 0);
            let end = Math.min(start + 5, totalPages);
            for (let i = start; i < end; i++) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link page-btn" href="#" data-page="${i}">${i + 1}</a></li>`;
            }
            html += `<li class="page-item ${currentPage === totalPages - 1 ? 'disabled' : ''}">
            <a class="page-link next-page" href="#" aria-label="Next"><i data-lucide="chevron-right"></i></a></li>`;
            $('#customPagination').html(html);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // ── Statistics accordion ──
        $('#statisticsCollapse').on('show.bs.collapse', function() {
            $('.stats-chevron').attr('data-lucide', 'chevron-up');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
        $('#statisticsCollapse').on('hide.bs.collapse', function() {
            $('.stats-chevron').attr('data-lucide', 'chevron-down');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        // ── Filters accordion ──
        $('#filtersCollapse').on('show.bs.collapse', function() {
            $('.filter-chevron').attr('data-lucide', 'chevron-up');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
        $('#filtersCollapse').on('hide.bs.collapse', function() {
            $('.filter-chevron').attr('data-lucide', 'chevron-down');
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        // ── Search ──
        $('#customSearchBox').on('keyup', function() {
            table.search(this.value).draw();
        });

        // ── Active filter count ──
        function updateActiveFiltersCount() {
            let count = 0;
            if ($('#statusFilter').val()) count++;
            if (dateFilter.getValue()) count++;
            const badge = $('#activeFiltersCount');
            count > 0 ? badge.text(count).show() : badge.hide();
        }

        $('#statusFilter').on('change', function() {
            updateActiveFiltersCount();
            table.ajax.reload();
        });

        $('#clearFilters').on('click', function() {
            $('#statusFilter').val('');
            $('#customSearchBox').val('');
            dateFilter.clear();
            updateActiveFiltersCount();
            table.search('').ajax.reload();
        });

        // ── Pagination ──
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
        $('#customLength').on('change', function() {
            table.page.len($(this).val()).draw();
        });

        // ── Copy URL ──
        $(document).on('click', '.copy-url', function() {
            const url = $(this).data('url');
            navigator.clipboard.writeText(url).then(function() {
                window.Toast?.fire({
                    icon: 'success',
                    title: 'Short URL copied!'
                });
            });
        });

        // ── Delete ──
        $(document).on('click', '.delete-short-url', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i data-lucide="trash-2" class="icon-sm me-1"></i>Yes, delete it!',
                cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
                customClass: {
                    confirmButton: 'btn btn-sm btn-danger me-2',
                    cancelButton: 'btn btn-sm btn-secondary'
                },
                buttonsStyling: false,
                didOpen: () => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/short-urls/' + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    title: 'Deleted!',
                                    text: res.message,
                                    icon: 'success',
                                    confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
                                    customClass: {
                                        confirmButton: 'btn btn-sm btn-success'
                                    },
                                    buttonsStyling: false,
                                    didOpen: () => {
                                        if (typeof lucide !== 'undefined') lucide.createIcons();
                                    }
                                });
                                table.ajax.reload();
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });

        // ── Checkboxes ──
        $('#selectAll').on('change', function() {
            $('.row-checkbox').prop('checked', $(this).is(':checked'));
            updateBulkBtn();
        });
        $(document).on('change', '.row-checkbox', function() {
            $('#selectAll').prop('checked', $('.row-checkbox').length === $('.row-checkbox:checked').length);
            updateBulkBtn();
        });

        function updateBulkBtn() {
            $('#bulkActionsBtn').prop('disabled', $('.row-checkbox:checked').length === 0);
        }

        // ── Bulk Actions ──
        $('.bulk-action').on('click', function(e) {
            e.preventDefault();
            const action = $(this).data('action');
            const ids = $('.row-checkbox:checked').map(function() {
                return $(this).val();
            }).get();
            if (!ids.length) {
                Swal.fire('Warning!', 'Please select at least one item.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Confirm',
                text: `Are you sure you want to ${action} ${ids.length} item(s)?`,
                icon: action === 'delete' ? 'warning' : 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, proceed!',
                customClass: {
                    confirmButton: `btn btn-sm btn-${action === 'delete' ? 'danger' : 'primary'} me-2`,
                    cancelButton: 'btn btn-sm btn-secondary'
                },
                buttonsStyling: false,
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.short-urls.bulk-action') }}",
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            action,
                            ids
                        },
                        success: function(res) {
                            if (res.success) {
                                window.Toast?.fire({
                                    icon: 'success',
                                    title: res.message
                                });
                                table.ajax.reload();
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });

    });
</script>
@endpush