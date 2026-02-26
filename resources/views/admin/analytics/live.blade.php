@extends('admin.layout.master')

@section('title', $title ?? 'Live Users')
@section('description', $description ?? 'Monitor live users accessing your application in real-time.')
@section('keywords', $keywords ?? 'live users, real-time monitoring, active sessions, user tracking')

@push('plugin-styles')
<link href="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<style>
    .stats-card {
        border-left: 4px solid;
    }

    .stats-card.total {
        border-left-color: #6c757d;
    }

    .stats-card.web {
        border-left-color: #0d6efd;
    }

    .stats-card.mobile {
        border-left-color: #20c997;
    }

    .stats-card.admin {
        border-left-color: #dc3545;
    }

    /* Added platform/device colors */
    .stats-card.app {
        border-left-color: #198754;
    }

    .stats-card.desktop {
        border-left-color: #0dcaf0;
    }

    .stats-card.tablet {
        border-left-color: #fd7e14;
    }

    .stats-card.authenticated {
        border-left-color: #198754;
    }

    .stats-card.guest {
        border-left-color: #ffc107;
    }

    .pulse-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #198754;
        animation: pulse 2s infinite;
        margin-right: 5px;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(25, 135, 84, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
        }
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
    }

    .platform-badge,
    .device-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .filter-chevron {
        transition: transform 0.2s ease-in-out;
    }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item active" aria-current="page">Live Users</li>
    </ol>
</nav>

<!-- Live User Statistics -->
<div class="card mb-3">
    <div class="card-header p-3">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i data-lucide="activity" class="icon-sm me-2"></i>
                Live Statistics
                <span class="ms-2 pulse-dot"></span>
            </h6>
            <h4 class="mb-0" id="stat-total">0</h4>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="col-12 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>
                            <i data-lucide="monitor" class="icon-lg me-2"></i>
                            Platform
                        </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-sm-6 col-md-4 col-lg-4 mb-3">
                        <div class="card stats-card web">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Web</h6>
                                        <h4 class="mb-0" id="stat-web">0</h4>
                                    </div>
                                    <i data-lucide="monitor" class="icon-lg text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6 col-md-4 col-lg-4 mb-3">
                        <div class="card stats-card admin">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Admin</h6>
                                        <h4 class="mb-0" id="stat-admin">0</h4>
                                    </div>
                                    <i data-lucide="shield" class="icon-lg text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6 col-md-4 col-lg-4 mb-3">
                        <div class="card stats-card app">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">App</h6>
                                        <h4 class="mb-0" id="stat-app">0</h4>
                                    </div>
                                    <i data-lucide="layers" class="icon-lg text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>
                                <i data-lucide="smartphone" class="icon-lg me-2"></i>
                                Device
                            </h4>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6 col-md-4 col-lg-4 mb-3">
                        <div class="card stats-card mobile">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Mobile</h6>
                                        <h4 class="mb-0" id="stat-mobile">0</h4>
                                    </div>
                                    <i data-lucide="smartphone" class="icon-lg text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6 col-md-4 col-lg-4 mb-3">
                        <div class="card stats-card tablet">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Tablet</h6>
                                        <h4 class="mb-0" id="stat-tablet">0</h4>
                                    </div>
                                    <i data-lucide="tablet" class="icon-lg text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6 col-md-4 col-lg-4 mb-3">
                        <div class="card stats-card desktop">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Desktop</h6>
                                        <h4 class="mb-0" id="stat-desktop">0</h4>
                                    </div>
                                    <i data-lucide="monitor" class="icon-lg text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4>
                                <i data-lucide="user-check" class="icon-lg me-2"></i>
                                Authenticated
                            </h4>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6 col-md-4 col-lg-4 mb-3">
                        <div class="card stats-card authenticated">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Authenticated</h6>
                                        <h4 class="mb-0" id="stat-authenticated">0</h4>
                                    </div>
                                    <i data-lucide="user-check" class="icon-lg text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-6 col-md-4 col-lg-4 mb-3">
                        <div class="card stats-card guest">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-0">Guests</h6>
                                        <h4 class="mb-0" id="stat-guest">0</h4>
                                    </div>
                                    <i data-lucide="user-x" class="icon-lg text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Users Table -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                    <h6 class="card-title mb-0">
                        <span class="pulse-dot me-2"></span>
                        Live Users
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="refreshBtn">
                            <i data-lucide="refresh-cw" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Refresh</span>
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
                                    <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search users...">
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
                                    <label for="authFilter" class="form-label">Authentication</label>
                                    <select id="authFilter" class="form-select form-select-sm">
                                        <option value="">All Users</option>
                                        <option value="authenticated">Authenticated</option>
                                        <option value="guest">Guest</option>
                                    </select>
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

                <div class="table-responsive mb-3">
                    <table id="liveUsersTable" class="table table-hover">
                        <thead>
                            <tr class="bg-dark text-white">
                                <th>User</th>
                                <th>Platform/Device</th>
                                <th>Current Page</th>
                                <th>IP Address</th>
                                <th>Connected At</th>
                                <th>Last Activity</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via Socket.IO -->
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
@endpush

@push('custom-scripts')
<script>
    $(document).ready(function() {
        let currentUsers = [];

        // Initialize DataTable
        const table = $('#liveUsersTable').DataTable({
            data: [],
            columns: [{
                    data: null,
                    className: 'align-content-center',
                    render: function(data, type, row) {
                        const avatar = "{{ config('app.url') }}/" + (row.userAvatar || 'build/images/others/placeholder.jpg');
                        const name = row.userName || 'Guest';
                        // Ensure email is a string, not an object
                        let email = 'N/A';
                        if (row.userEmail && typeof row.userEmail === 'string') {
                            email = row.userEmail;
                        } else if (row.userEmail && typeof row.userEmail === 'object') {
                            email = 'N/A';
                        }

                        return `
                            <div class="d-flex align-items-center">
                                <img src="${avatar}" class="user-avatar me-2" alt="${name}">
                                <div class="d-flex flex-column">
                                    <div class="fw-bold">${name}</div>
                                    <small class="text-muted">${email}</small>
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: null,
                    className: 'align-content-center',
                    render: function(data, type, row) {
                        let pIcon = 'monitor',
                            pColor = 'primary';
                        if (row.platform === 'app') {
                            pIcon = 'layers';
                            pColor = 'success';
                        } else if (row.platform === 'admin') {
                            pIcon = 'shield';
                            pColor = 'danger';
                        }

                        let dIcon = 'monitor';
                        if (row.device === 'mobile') dIcon = 'smartphone';
                        else if (row.device === 'tablet') dIcon = 'tablet';

                        return `
                            <div class="d-flex flex-column align-items-center text-uppercase" style="gap: 2px;">
                                <span class="badge bg-${pColor} platform-badge">
                                    <i data-lucide="${pIcon}" class="icon-xs me-1"></i>${row.platform}
                                </span>
                                <span class="badge bg-secondary device-badge">
                                    <i data-lucide="${dIcon}" class="icon-xs me-1"></i>${row.device}
                                </span>
                            </div>
                        `;
                    }
                },
                {
                    data: 'currentPage',
                    className: 'align-content-center',
                    render: function(data) {
                        return `<code>${data || '/'}</code>`;
                    }
                },
                {
                    data: 'ipAddress',
                    className: 'align-content-center',
                    render: function(data) {
                        return data || 'N/A';
                    }
                },
                {
                    data: 'connectedAt',
                    className: 'align-content-center',
                    render: function(data) {
                        if (!data) return 'N/A';
                        try {
                            return new Intl.DateTimeFormat('en-US', {
                                timeZone: window.appTimezone || 'UTC',
                                year: 'numeric',
                                month: 'short',
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit',
                                hour12: true
                            }).format(new Date(data));
                        } catch (e) {
                            return new Date(data).toLocaleString();
                        }
                    }
                },
                {
                    data: 'lastActivity',
                    className: 'align-content-center',
                    render: function(data) {
                        if (!data) return 'N/A';
                        const lastActivity = new Date(data);
                        const now = new Date();
                        const diff = Math.floor((now - lastActivity) / 1000);

                        if (diff < 60) return `${diff}s ago`;
                        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
                        return `${Math.floor(diff / 3600)}h ago`;
                    }
                },
                {
                    data: null,
                    className: 'align-content-center',
                    render: function(data) {
                        const lastActivity = new Date(data.lastActivity);
                        const now = new Date();
                        const diff = Math.floor((now - lastActivity) / 1000);
                        const isActive = diff < 60;

                        return `<span class="badge ${isActive ? 'bg-success' : 'bg-warning'}">
                            ${isActive ? 'Active' : 'Idle'}
                        </span>`;
                    }
                }
            ],
            drawCallback: function() {
                var api = this.api();
                var pageInfo = api.page.info();
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
                [4, 'desc']
            ],
            pageLength: <?= admin_items_per_page() ?>,
            responsive: true,
            dom: 'rt'
        });

        // Update statistics
        function updateStats(data) {
            $('#stat-total').text(data.total || 0);
            $('#stat-web').text(data.web || 0);
            $('#stat-mobile').text(data.mobile || 0);
            $('#stat-tablet').text(data.tablet || 0);
            $('#stat-desktop').text(data.desktop || 0);
            $('#stat-admin').text(data.admin || 0);
            $('#stat-app').text(data.app || 0);
            $('#stat-authenticated').text(data.authenticated || 0);
            $('#stat-guest').text(data.guest || 0);
        }

        // Listen to Socket.IO events using the global SocketManager
        if (window.SocketManager) {
            // Listen for users update
            window.SocketManager.on('users:update', function(data) {
                updateStats(data);
                currentUsers = data.users || [];
                table.clear().rows.add(currentUsers).draw();
            });

            // Wait for connection before requesting stats
            if (window.SocketManager.isConnected()) {
                // Already connected, request stats immediately
                window.SocketManager.requestAdminStats();
            } else {
                // Wait for connection
                window.SocketManager.on('connect', function() {
                    window.SocketManager.requestAdminStats();
                });
            }
        } else {
            console.error('SocketManager not available');
        }

        // Pagination
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

        // Custom length
        $('#customLength').on('change', function() {
            table.page.len($(this).val()).draw();
        });

        // Refresh button
        $('#refreshBtn').on('click', function() {
            if (window.SocketManager) {
                window.SocketManager.requestAdminStats();
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
            updateActiveFiltersCount();
        });

        // Filter functionality
        $('#platformFilter, #authFilter, #deviceFilter').on('change', function() {
            updateActiveFiltersCount();
            applyFilters();
        });

        function applyFilters() {
            const platform = $('#platformFilter').val();
            const device = $('#deviceFilter').val();
            const auth = $('#authFilter').val();

            let filteredUsers = currentUsers;

            if (platform) {
                filteredUsers = filteredUsers.filter(user => user.platform === platform);
            }

            if (device) {
                filteredUsers = filteredUsers.filter(user => user.device === device);
            }

            if (auth === 'authenticated') {
                filteredUsers = filteredUsers.filter(user => user.userId !== null);
            } else if (auth === 'guest') {
                filteredUsers = filteredUsers.filter(user => user.userId === null);
            }

            table.clear().rows.add(filteredUsers).draw();
        }

        function updateActiveFiltersCount() {
            let count = 0;
            if ($('#customSearchBox').val()) count++;
            if ($('#platformFilter').val()) count++;
            if ($('#deviceFilter').val()) count++;
            if ($('#authFilter').val()) count++;

            const badge = $('#activeFiltersCount');
            if (count > 0) {
                badge.text(count).show();
            } else {
                badge.hide();
            }
        }

        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#platformFilter').val('');
            $('#authFilter').val('');
            $('#customSearchBox').val('');
            updateActiveFiltersCount();
            table.clear().rows.add(currentUsers).draw();
            table.search('').draw();
        });

        // Ensure Lucide icons are loaded
        function ensureLucideIcons() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            } else {
                setTimeout(ensureLucideIcons, 100);
            }
        }

        $(document).ajaxComplete(function() {
            ensureLucideIcons();
        });

        ensureLucideIcons();
    });
</script>
@endpush