@extends('admin.layout.master')

@section('title', $title ?? 'Laravel Logs')
@section('description', $description ?? 'View and manage Laravel application logs')
@section('keywords', $keywords ?? 'laravel logs, application logs, error monitoring')

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

  .stats-card.errors {
    border-left-color: #dc3545;
  }

  .stats-card.warnings {
    border-left-color: #ffc107;
  }

  .stats-card.recent {
    border-left-color: #0d6efd;
  }

  .log-level-emergency,
  .log-level-alert,
  .log-level-critical,
  .log-level-error {
    color: #dc3545 !important;
  }

  .log-level-warning {
    color: #ffc107 !important;
  }

  .log-level-notice,
  .log-level-info {
    color: #0d6efd !important;
  }

  .log-level-debug {
    color: #6c757d !important;
  }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Laravel Logs</li>
  </ol>
</nav>

<!-- Laravel Log Statistics Section -->
<div class="card mb-3">
  <div class="card-header p-0">
    <button class="btn btn-sm btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
      type="button" data-bs-toggle="collapse" data-bs-target="#statisticsCollapse"
      aria-expanded="true" aria-controls="statisticsCollapse">
      <span>
        <i data-lucide="file-text" class="icon-sm me-2"></i>Laravel Log Statistics
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
                  <h4 class="mb-0" id="totalLogs">{{ number_format($stats['total'] ?? 0) }}</h4>
                </div>
                <i data-lucide="file-text" class="icon-lg text-muted"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card errors">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Errors</h6>
                  <h4 class="mb-0" id="errorLogs">{{ number_format($stats['errors'] ?? 0) }}</h4>
                </div>
                <i data-lucide="alert-triangle" class="icon-lg text-danger"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card warnings">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Warnings</h6>
                  <h4 class="mb-0" id="warningLogs">{{ number_format($stats['warnings'] ?? 0) }}</h4>
                </div>
                <i data-lucide="alert-circle" class="icon-lg text-warning"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card recent">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Recent 24h</h6>
                  <h4 class="mb-0" id="recentLogs">{{ number_format($stats['recent_24h'] ?? 0) }}</h4>
                </div>
                <i data-lucide="clock" class="icon-lg text-primary"></i>
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
          <h6 class="card-title mb-0">Laravel Logs Management</h6>
          <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="parseLogs()">
              <i data-lucide="refresh-cw" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Parse Logs</span>
              <span class="d-sm-none">Parse</span>
            </button>
            <button type="button" id="exportLogs" class="btn btn-outline-primary btn-sm">
              <i data-lucide="download" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Export</span>
              <span class="d-sm-none">Export</span>
            </button>
            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#downloadLogModal">
              <i data-lucide="file-down" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Download Log File</span>
              <span class="d-sm-none">Download</span>
            </button>
            <button type="button" id="refreshLogs" class="btn btn-outline-secondary btn-sm">
              <i data-lucide="refresh-cw" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Refresh</span>
              <span class="d-sm-none">Refresh</span>
            </button>
          </div>
        </div>

        <!-- Laravel Logs Filters Section -->
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
                  <label for="levelFilter" class="form-label">Level</label>
                  <select id="levelFilter" class="form-select form-select-sm">
                    <option value="">All Levels</option>
                    @foreach($levels as $key => $level)
                    <option value="{{ $key }}">{{ $level['name'] }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="channelFilter" class="form-label">Channel</label>
                  <select id="channelFilter" class="form-select form-select-sm">
                    <option value="">All Channels</option>
                    @foreach($channels as $key => $channel)
                    <option value="{{ $key }}">{{ $channel }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="environmentFilter" class="form-label">Environment</label>
                  <select id="environmentFilter" class="form-select form-select-sm">
                    <option value="">All Environments</option>
                    @foreach($environments as $key => $environment)
                    <option value="{{ $key }}">{{ $environment }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="monthFilter" class="form-label">Month</label>
                  <select id="monthFilter" class="form-select form-select-sm">
                    <option value="">All Months</option>
                    @foreach($months as $month)
                    <option value="{{ $month }}">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</option>
                    @endforeach
                  </select>
                </div>
                <!-- Date Filter Component -->
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <x-admin.date-filter label="Date Range" />
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2 mt-sm-0 mb-0 mb-sm-2 align-items-end d-flex">
                  <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-sm">
                    <i data-lucide="x" class="icon-sm me-1"></i>Clear Filters
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="table-responsive mb-3">
          <table id="laravelLogsTable" class="table table-hover">
            <thead>
              <tr class="bg-dark">
                <th>ID</th>
                <th>Level</th>
                <th>Channel</th>
                <th>Environment</th>
                <th>Message</th>
                <th>User</th>
                <th>Logged At</th>
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

<!-- Date Range Modal Component -->
<x-admin.date-range-modal />

<!-- Download Log File Modal -->
<div class="modal fade" id="downloadLogModal" tabindex="-1" aria-labelledby="downloadLogModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center" id="downloadLogModalLabel">
          <i data-lucide="file-down" class="icon-sm me-2"></i>Download Log File
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12 mt-2 mb-0">
            <label for="downloadMonth" class="form-label">Select Month</label>
            <select class="form-select" id="downloadMonth">
              <option value="">Select Month</option>
              @foreach($months as $month)
              <option value="{{ $month }}">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-12 mt-2 mb-0">
            <label for="downloadType" class="form-label">Log Type</label>
            <select class="form-select" id="downloadType">
              <option value="laravel">Laravel</option>
              <option value="single">Single</option>
              <option value="daily">Daily</option>
            </select>
          </div>
        </div>
        <div class="alert alert-info mt-3 mb-0">
          <i data-lucide="info" class="icon-sm me-2"></i>
          <small>This will download the raw log file for the selected month and type.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
          <i data-lucide="x" class="icon-sm me-1"></i>Cancel
        </button>
        <button type="button" class="btn btn-sm btn-primary" id="downloadLogFile">
          <i data-lucide="download" class="icon-sm me-1"></i>Download
        </button>
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
    const table = $('#laravelLogsTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('admin.laravel-logs.index') }}",
        data: function(d) {
          d.level = $('#levelFilter').val();
          d.channel = $('#channelFilter').val();
          d.environment = $('#environmentFilter').val();
          d.month = $('#monthFilter').val();
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
          data: 'level_badge',
          name: 'level',
          orderable: false,
          searchable: false,
          className: 'align-content-center'
        },
        {
          data: 'channel_badge',
          name: 'channel',
          orderable: false,
          searchable: false,
          className: 'align-content-center'
        },
        {
          data: 'environment_badge',
          name: 'environment',
          orderable: false,
          searchable: false,
          className: 'align-content-center'
        },
        {
          data: 'message_preview',
          name: 'message',
          className: 'align-content-center'
        },
        {
          data: 'user_info',
          name: 'user_id',
          orderable: false,
          searchable: false,
          className: 'align-content-center'
        },
        {
          data: 'logged_at',
          name: 'logged_at',
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
      dom: 'rt',
      language: {
        processing: '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>',
        emptyTable: "No Laravel logs found",
        zeroRecords: "No matching logs found"
      }
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

    // Filter handlers
    $('#levelFilter, #channelFilter, #environmentFilter, #monthFilter').on('change', function() {
      table.draw();
      updateActiveFiltersCount();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
      $('#customSearchBox, #levelFilter, #channelFilter, #environmentFilter, #monthFilter').val('');
      dateFilter.clear();
      table.search('').draw();
      updateActiveFiltersCount();
    });

    // Update active filters count
    function updateActiveFiltersCount() {
      let count = 0;
      if ($('#customSearchBox').val()) count++;
      if ($('#levelFilter').val()) count++;
      if ($('#channelFilter').val()) count++;
      if ($('#environmentFilter').val()) count++;
      if ($('#monthFilter').val()) count++;
      if (dateFilter.getValue()) count++;

      if (count > 0) {
        $('#activeFiltersCount').text(count).show();
        $('#clearFilters').show();
      } else {
        $('#activeFiltersCount').hide();
      }
    }

    // Parse logs
    $('#parseLogsBtn').on('click', function() {
      const btn = $(this);
      const originalText = btn.html();

      btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Parsing...');

      $.ajax({
        url: "{{ route('admin.laravel-logs.parse') }}",
        type: 'POST',
        data: {
          _token: "{{ csrf_token() }}"
        },
        success: function(response) {
          if (response.success) {
            Swal.fire({
              title: 'Success!',
              text: response.message,
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
            table.draw();
            updateStats();
          } else {
            Swal.fire({
              title: 'Error!',
              text: response.message,
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
          }
        },
        error: function() {
          Swal.fire({
            title: 'Error!',
            text: 'Error parsing logs',
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
        },
        complete: function() {
          btn.prop('disabled', false).html(originalText);
        }
      });
    });

    // Export logs
    $('#exportBtn').on('click', function() {
      const params = new URLSearchParams();

      if ($('#levelFilter').val()) params.append('level', $('#levelFilter').val());
      if ($('#channelFilter').val()) params.append('channel', $('#channelFilter').val());
      if ($('#environmentFilter').val()) params.append('environment', $('#environmentFilter').val());
      if ($('#monthFilter').val()) params.append('month', $('#monthFilter').val());
      if ($('#dateFilter').val() === 'custom') {
        if ($('#dateFilter').data('startDate')) params.append('date_from', $('#dateFilter').data('startDate'));
        if ($('#dateFilter').data('endDate')) params.append('date_to', $('#dateFilter').data('endDate'));
      }

      window.location.href = "{{ route('admin.laravel-logs.export') }}?" + params.toString();
    });

    // Refresh table
    $('#refreshBtn').on('click', function() {
      table.draw();
      updateStats();
    });



    // Update statistics
    function updateStats() {
      $.get("{{ route('admin.laravel-logs.stats') }}")
        .done(function(stats) {
          $('#totalLogs').text(stats.total.toLocaleString());
          $('#errorLogs').text(stats.errors.toLocaleString());
          $('#warningLogs').text(stats.warnings.toLocaleString());
          $('#recentLogs').text(stats.recent_24h.toLocaleString());
        });
    }

    // Export functionality
    $('#exportLogs').on('click', function() {
      const params = new URLSearchParams({
        level: $('#levelFilter').val(),
        channel: $('#channelFilter').val(),
        environment: $('#environmentFilter').val(),
        month: $('#monthFilter').val(),
        ...dateFilter.getAjaxData()
      });

      window.location.href = "{{ route('admin.laravel-logs.export') }}?" + params.toString();
    });

    // Refresh functionality
    $('#refreshLogs').on('click', function() {
      table.ajax.reload();
      updateStats();
    });

    // Download log file
    $('#downloadLogFile').on('click', function() {
      const month = $('#downloadMonth').val();
      const type = $('#downloadType').val();

      if (!month) {
        Swal.fire({
          title: 'Error!',
          text: 'Please select a month',
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
        return;
      }

      const params = new URLSearchParams();
      params.append('month', month);
      params.append('type', type);

      window.location.href = "{{ route('admin.laravel-logs.download') }}?" + params.toString();
      $('#downloadLogModal').modal('hide');
    });

    // Initialize filters count
    updateActiveFiltersCount();
  });

  // Parse logs function
  function parseLogs() {
    $.ajax({
      url: "{{ route('admin.laravel-logs.parse') }}",
      type: 'POST',
      data: {
        _token: "{{ csrf_token() }}"
      },
      success: function(response) {
        if (response.success) {
          Swal.fire({
            title: 'Success!',
            text: response.message,
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
          $('#laravelLogsTable').DataTable().ajax.reload();
          updateStats();
        } else {
          Swal.fire({
            title: 'Error!',
            text: response.message,
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
        }
      },
      error: function() {
        Swal.fire({
          title: 'Error!',
          text: 'Error parsing logs',
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
      }
    });
  }

  // Update statistics
  function updateStats() {
    $.get("{{ route('admin.laravel-logs.stats') }}")
      .done(function(stats) {
        $('#totalLogs').text(stats.total.toLocaleString());
        $('#errorLogs').text(stats.errors.toLocaleString());
        $('#warningLogs').text(stats.warnings.toLocaleString());
        $('#recentLogs').text(stats.recent_24h.toLocaleString());
      });
  }
</script>
@endpush