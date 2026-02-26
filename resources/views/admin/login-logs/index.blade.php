@extends('admin.layout.master')

@section('title', $title ?? 'Login Logs')
@section('description', $description ?? 'View and manage all login logs, track user login activities, and monitor security events.')
@section('keywords', $keywords ?? 'login logs, user activity, security monitoring, login tracking')

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

  .stats-card.successful {
    border-left-color: #198754;
  }

  .stats-card.failed {
    border-left-color: #dc3545;
  }

  .stats-card.suspicious {
    border-left-color: #fd7e14;
  }

  .stats-card.users {
    border-left-color: #0d6efd;
  }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Login Logs</li>
  </ol>
</nav>
<!-- Login Statistics Section -->
<div class="card mb-3">
  <div class="card-header p-0">
    <button class="btn btn-sm btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
      type="button" data-bs-toggle="collapse" data-bs-target="#statisticsCollapse"
      aria-expanded="true" aria-controls="statisticsCollapse">
      <span>
        <i data-lucide="activity" class="icon-sm me-2"></i>Login Statistics
      </span>
      <i data-lucide="chevron-up" class="icon-sm stats-chevron"></i>
    </button>
  </div>
  <div class="collapse show" id="statisticsCollapse">
    <div class="card-body">
      <div class="row">
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card total">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Total</h6>
                  <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                </div>
                <i data-lucide="activity" class="icon-lg text-muted"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card successful">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Successful</h6>
                  <h4 class="mb-0">{{ number_format($stats['successful']) }}</h4>
                </div>
                <i data-lucide="check-circle" class="icon-lg text-success"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card failed">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Failed</h6>
                  <h4 class="mb-0">{{ number_format($stats['failed']) }}</h4>
                </div>
                <i data-lucide="x-circle" class="icon-lg text-danger"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card suspicious">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Suspicious</h6>
                  <h4 class="mb-0">{{ number_format($stats['suspicious']) }}</h4>
                </div>
                <i data-lucide="alert-triangle" class="icon-lg text-warning"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card users">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Unique Users</h6>
                  <h4 class="mb-0">{{ number_format($stats['unique_users']) }}</h4>
                </div>
                <i data-lucide="users" class="icon-lg text-primary"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card successful">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Success Rate</h6>
                  <h4 class="mb-0">{{ $stats['success_rate'] }}%</h4>
                </div>
                <i data-lucide="trending-up" class="icon-lg text-success"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
          <h6 class="card-title mb-0">Login Logs Management</h6>
          <div class="d-flex flex-wrap gap-2">
            <button type="button" id="exportLogs" class="btn btn-outline-primary btn-sm">
              <i data-lucide="download" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Export</span>
              <span class="d-sm-none">Export</span>
            </button>
            <button type="button" id="refreshLogs" class="btn btn-outline-secondary btn-sm">
              <i data-lucide="refresh-cw" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Refresh</span>
              <span class="d-sm-none">Refresh</span>
            </button>
          </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Login Logs Filters Section -->
        <div class="card mb-3">
          <div class="card-header p-0">
            <button class="btn btn-sm btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
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
                  <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search logs...">
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="statusFilter" class="form-label">Status</label>
                  <select id="statusFilter" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    @foreach($statuses as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="typeFilter" class="form-label">Type</label>
                  <select id="typeFilter" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach($types as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="userFilter" class="form-label">User</label>
                  <select id="userFilter" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                  </select>
                </div>
                <!-- Date Filter Component -->
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <x-admin.date-filter label="Date Range" />
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

        <!-- Date Range Modal Component -->
        <x-admin.date-range-modal />

        <div class="table-responsive mb-3">
          <table id="loginLogsTable" class="table table-hover">
            <thead>
              <tr class="bg-dark">
                <th>ID</th>
                <th>User</th>
                <th>Device & IP</th>
                <th>Location</th>
                <th>Type</th>
                <th>Status</th>
                <th>Login At</th>
                <th>Actions</th>
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
            <nav id="customPaginationWrapper" aria-label="Page navigation example" class="">
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
    // Initialize Admin Date Filter FIRST (before DataTable)
    const dateFilter = new AdminDateFilter({
      onFilterChange: function() {
        updateActiveFiltersCount();
        table.ajax.reload();
      }
    });

    // Initialize DataTable
    const table = $('#loginLogsTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('admin.login-logs.index') }}",
        data: function(d) {
          d.status = $('#statusFilter').val();
          d.type = $('#typeFilter').val();
          d.user_id = $('#userFilter').val();
          // Use AdminDateFilter class
          Object.assign(d, dateFilter.getAjaxData());
        }
      },
      columns: [{
          data: 'id',
          name: 'id',
          className: 'align-content-center'
        },
        {
          data: 'user_info',
          name: 'email',
          className: 'align-content-center'
        },
        {
          data: 'device_info',
          name: 'ip_address',
          className: 'align-content-center'
        },
        {
          data: 'location_info',
          name: 'location',
          orderable: false,
          searchable: false,
          className: 'align-content-center'
        },
        {
          data: 'type_badge',
          name: 'type',
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
          data: 'login_at',
          name: 'login_at',
          className: 'align-content-center'
        },
        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false,
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

        let paginationHtml = '';

        paginationHtml += `
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
        [0, 'desc']
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

    // Pagination functionality
    $(document).on('click', '.page-btn', function(e) {
      e.preventDefault();
      var page = $(this).data('page');
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
      var length = $(this).val();
      table.page.len(length).draw();
    });

    // Search functionality
    $('#customSearchBox').on('keyup', function() {
      table.search(this.value).draw();
    });

    // Filter handlers
    $('#statusFilter, #typeFilter, #userFilter').on('change', function() {
      table.draw();
      updateActiveFiltersCount();
    });



    // Clear filters
    $('#clearFilters').on('click', function() {
      $('#customSearchBox').val('');
      $('#statusFilter').val('');
      $('#typeFilter').val('');
      $('#userFilter').val('');
      dateFilter.clear();
      table.search('').draw();
      updateActiveFiltersCount();
    });

    // Export functionality
    $('#exportLogs').on('click', function() {
      const params = new URLSearchParams({
        status: $('#statusFilter').val(),
        type: $('#typeFilter').val(),
        user_id: $('#userFilter').val()
      });

      // Add date filter parameters
      const dateParams = dateFilter.getAjaxData();
      for (const [key, value] of Object.entries(dateParams)) {
        if (value) params.append(key, value);
      }

      window.location.href = "{{ route('admin.login-logs.export') }}?" + params.toString();
    });

    // Refresh functionality
    $('#refreshLogs').on('click', function() {
      table.ajax.reload();
    });

    // Update active filters count
    function updateActiveFiltersCount() {
      let count = 0;
      if ($('#customSearchBox').val()) count++;
      if ($('#statusFilter').val()) count++;
      if ($('#typeFilter').val()) count++;
      if ($('#userFilter').val()) count++;
      if (dateFilter.getValue()) count++;

      if (count > 0) {
        $('#activeFiltersCount').text(count).show();
        $('#clearFilters').show();
      } else {
        $('#activeFiltersCount').hide();
      }
    }

    // Initialize filters count
    updateActiveFiltersCount();
  });

  // Mark as safe function
  function markAsSafe(loginLogId) {
    Swal.fire({
      title: 'Mark as Safe?',
      text: 'This will mark the login attempt as safe and remove the suspicious flag.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: '<i data-lucide="shield-check" class="icon-sm me-1"></i>Yes, mark as safe',
      cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
      customClass: {
        confirmButton: 'btn btn-sm btn-success',
        cancelButton: 'btn btn-sm btn-secondary'
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("{{ route('admin.login-logs.mark-safe', ':id') }}".replace(':id', loginLogId))
          .done(function(data) {
            Swal.fire({
              title: 'Success',
              text: data.message,
              icon: 'success',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-sm btn-success'
              },
              buttonsStyling: false,
              didOpen: () => {
                if (typeof lucide !== 'undefined') {
                  lucide.createIcons();
                }
              }
            });
            $('#loginLogsTable').DataTable().ajax.reload();
          })
          .fail(function(xhr) {
            const error = xhr.responseJSON?.error || 'Failed to mark as safe';
            Swal.fire({
              title: 'Error',
              text: error,
              icon: 'error',
              confirmButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-sm btn-danger'
              },
              buttonsStyling: false,
              didOpen: () => {
                if (typeof lucide !== 'undefined') {
                  lucide.createIcons();
                }
              }
            });
          });
      }
    });
  }
</script>
@endpush