@extends('admin.layout.master')

@section('title', $title ?? 'Page View Analytics')
@section('description', $description ?? 'Track and analyze page views across all platforms and devices.')
@section('keywords', $keywords ?? 'page views, analytics, traffic tracking, visitor data')

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
        border-left-color: #0d6efd;
    }

    .stats-card.users {
        border-left-color: #198754;
    }

    .stats-card.guests {
        border-left-color: #ffc107;
    }

    .stats-card.today {
        border-left-color: #0dcaf0;
    }

    .user-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
    }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item">Analytics</li>
        <li class="breadcrumb-item active" aria-current="page">Page Views</li>
    </ol>
</nav>

<!-- Statistics Section -->
<div class="card mb-3">
    <div class="card-header p-0">
        <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
            type="button" data-bs-toggle="collapse" data-bs-target="#statisticsCollapse"
            aria-expanded="true" aria-controls="statisticsCollapse">
            <span>
                <i data-lucide="bar-chart-3" class="icon-sm me-2"></i>Page View Statistics
            </span>
            <i data-lucide="chevron-up" class="icon-sm stats-chevron"></i>
        </button>
    </div>
    <div class="collapse show" id="statisticsCollapse">
        <div class="card-body">
            <div class="row">
                <div class="col-6 col-sm-6 col-md-3 mb-3">
                    <div class="card stats-card total">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Total Visits</h6>
                                    <h4 class="mb-0">{{ number_format($stats['total_visits']) }}</h4>
                                </div>
                                <i data-lucide="eye" class="icon-lg text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-3 mb-3">
                    <div class="card stats-card users">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Unique Users</h6>
                                    <h4 class="mb-0">{{ number_format($stats['unique_users']) }}</h4>
                                </div>
                                <i data-lucide="users" class="icon-lg text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-3 mb-3">
                    <div class="card stats-card guests">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Unique Guests</h6>
                                    <h4 class="mb-0">{{ number_format($stats['unique_guests']) }}</h4>
                                </div>
                                <i data-lucide="user-x" class="icon-lg text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-3 mb-3">
                    <div class="card stats-card today">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Visits Today</h6>
                                    <h4 class="mb-0">{{ number_format($stats['today_visits']) }}</h4>
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

<!-- Main Content -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                    <h6 class="card-title mb-0">Page View History</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="refreshBtn">
                            <i data-lucide="refresh-cw" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Refresh</span>
                            <span class="d-sm-none">Refresh</span>
                        </button>
                    </div>
                </div>

                <!-- Filters Section -->
                <div class="card mb-3">
                    <div class="card-header p-0">
                        <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
                            type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse"
                            aria-expanded="false" aria-controls="filtersCollapse">
                            <span>
                                <i data-lucide="filter" class="icon-sm me-2"></i>Filters
                                <span id="activeFiltersCount" class="badge bg-primary ms-2" style="display: none;">0</span>
                            </span>
                            <i data-lucide="chevron-down" class="icon-sm filter-chevron"></i>
                        </button>
                    </div>
                    <div class="collapse" id="filtersCollapse">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                                    <label for="customSearchBox" class="form-label">Search</label>
                                    <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search pages or IPs...">
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                                    <label for="platformFilter" class="form-label">Platform</label>
                                    <select id="platformFilter" class="form-select form-select-sm">
                                        <option value="">All Platforms</option>
                                        <option value="web">Web</option>
                                        <option value="app">App</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                                    <label for="deviceFilter" class="form-label">Device</label>
                                    <select id="deviceFilter" class="form-select form-select-sm">
                                        <option value="">All Devices</option>
                                        <option value="desktop">Desktop</option>
                                        <option value="mobile">Mobile</option>
                                        <option value="tablet">Tablet</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                                    <label for="userTypeFilter" class="form-label">User Type</label>
                                    <select id="userTypeFilter" class="form-select form-select-sm">
                                        <option value="">All Types</option>
                                        <option value="authenticated">Authenticated</option>
                                        <option value="guest">Guest</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                                    <x-admin.date-filter />
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2 align-self-end">
                                    <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-sm">
                                        <i data-lucide="x" class="icon-sm me-1"></i>Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date Range Modal Component -->
                <x-admin.date-range-modal />

                <div class="table-responsive mb-3">
                    <table id="pageViewsTable" class="table table-hover">
                        <thead>
                            <tr class="bg-dark">
                                <th>User/Guest</th>
                                <th>Page Path</th>
                                <th>Platform</th>
                                <th>Device</th>
                                <th>IP Address</th>
                                <th>Visited At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via DataTables -->
                        </tbody>
                    </table>
                </div>
                <div class="row gap-2 gap-sm-0">
                    <div class="col-12 col-sm-6 col-md-6 d-flex align-items-center justify-content-center justify-content-sm-start gap-2 flex-wrap">
                        <select id="customLength" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                            @foreach(setting_options('admin_items_per_page') as $value => $label)
                            <option value="{{ $value }}" {{ admin_items_per_page() == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div id="customTableInfo" class="text-muted"></div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-6 d-flex align-items-center justify-content-center justify-content-sm-end">
                        <nav id="customPaginationWrapper" aria-label="Page navigation">
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
<script src="{{ asset('build/plugins/datatables.net/dataTables.min.js') }}"></script>
<script src="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@vite(['resources/js/admin/admin-date-filter.js'])
@endpush

@push('custom-scripts')
<script>
    $(document).ready(function() {
        // Initialize Admin Date Filter
        const dateFilter = new AdminDateFilter({
            onFilterChange: function() {
                updateActiveFiltersCount();
                table.ajax.reload();
            }
        });

        // Initialize DataTable
        const table = $('#pageViewsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.analytics.page-views') }}",
                data: function(d) {
                    d.platform = $('#platformFilter').val();
                    d.device = $('#deviceFilter').val();
                    d.user_type = $('#userTypeFilter').val();
                    Object.assign(d, dateFilter.getAjaxData());
                }
            },
            columns: [{
                    data: 'user_info',
                    name: 'user_info',
                    className: 'align-content-center'
                },
                {
                    data: 'page',
                    name: 'page',
                    className: 'align-content-center',
                    render: function(data) {
                        return `<code>${data}</code>`;
                    }
                },
                {
                    data: 'platform',
                    name: 'platform',
                    className: 'align-content-center'
                },
                {
                    data: 'device_info',
                    name: 'device',
                    className: 'align-content-center'
                },
                {
                    data: 'ip',
                    name: 'ip',
                    className: 'align-content-center'
                },
                {
                    data: 'visited_at',
                    name: 'visited_at',
                    className: 'align-content-center'
                }
            ],
            drawCallback: function() {
                var pageInfo = table.page.info();
                $('#customTableInfo').html(
                    `Showing ${pageInfo.start + 1} to ${pageInfo.end} of ${pageInfo.recordsTotal} entries`
                );
                var currentPage = pageInfo.page;
                var totalPages = pageInfo.pages;

                let paginationHtml = `
                    <li class="page-item ${currentPage === 0 ? 'disabled' : ''}">
                        <a class="page-link prev-page" href="#" aria-label="Previous">
                            <i data-lucide="chevron-left"></i>
                        </a>
                    </li>
                `;

                let rangeStart = Math.max(currentPage - 2, 0);
                let rangeEnd = Math.min(rangeStart + 5, totalPages);

                for (let i = rangeStart; i < rangeEnd; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link page-btn" href="#" data-page="${i}">${i + 1}</a>
                        </li>
                    `;
                }

                paginationHtml += `
                    <li class="page-item ${currentPage === totalPages - 1 ? 'disabled' : ''}">
                        <a class="page-link next-page" href="#" aria-label="Next">
                            <i data-lucide="chevron-right"></i>
                        </a>
                    </li>
                `;

                $('#customPagination').html(paginationHtml);
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            },
            order: [
                [5, 'desc']
            ],
            pageLength: <?= admin_items_per_page() ?>,
            responsive: true,
            dom: 'rt'
        });

        // Statistics accordion functionality
        $('#statisticsCollapse').on('show.bs.collapse', function() {
            $('.stats-chevron').attr('data-lucide', 'chevron-up');
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        $('#statisticsCollapse').on('hide.bs.collapse', function() {
            $('.stats-chevron').attr('data-lucide', 'chevron-down');
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        // Filter accordion functionality
        $('#filtersCollapse').on('show.bs.collapse', function() {
            $('.filter-chevron').attr('data-lucide', 'chevron-up');
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        $('#filtersCollapse').on('hide.bs.collapse', function() {
            $('.filter-chevron').attr('data-lucide', 'chevron-down');
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        // Search functionality
        $('#customSearchBox').on('keyup', function() {
            table.search(this.value).draw();
        });

        function updateActiveFiltersCount() {
            let count = 0;
            if ($('#platformFilter').val()) count++;
            if ($('#deviceFilter').val()) count++;
            if ($('#userTypeFilter').val()) count++;
            if (dateFilter.getValue()) count++;

            const badge = $('#activeFiltersCount');
            if (count > 0) {
                badge.text(count).show();
            } else {
                badge.hide();
            }
        }

        // Filter functionality
        $('#platformFilter, #deviceFilter, #userTypeFilter').on('change', function() {
            updateActiveFiltersCount();
            table.ajax.reload();
        });

        // Refresh button
        $('#refreshBtn').on('click', function() {
            table.ajax.reload();
        });

        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#platformFilter').val('');
            $('#deviceFilter').val('');
            $('#userTypeFilter').val('');
            $('#customSearchBox').val('');
            dateFilter.clear();
            updateActiveFiltersCount();
            table.search('').ajax.reload();
        });

        // Custom pagination functionality
        $(document).on('click', '.page-btn', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            table.page(page).draw('page');
        });

        $(document).on('click', '.prev-page', function(e) {
            e.preventDefault();
            table.page('previous').draw('page');
        });

        $(document).on('click', '.next-page', function(e) {
            e.preventDefault();
            table.page('next').draw('page');
        });

        // Custom length functionality
        $('#customLength').on('change', function() {
            table.page.len($(this).val()).draw();
        });
    });
</script>
@endpush