@extends('admin.layout.master')

@section('title', $title ?? 'Notifications')
@section('description', $description ?? 'View and manage your admin notifications and system alerts')
@section('keywords', $keywords ?? 'notifications, alerts, admin notifications, system messages')

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

  .stats-card.unread {
    border-left-color: #dc3545;
  }

  .stats-card.today {
    border-left-color: #198754;
  }

  .stats-card.week {
    border-left-color: #0d6efd;
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
    <li class="breadcrumb-item active" aria-current="page">Notifications</li>
  </ol>
</nav>

<!-- Notification Statistics Section -->
<div class="card mb-3">
  <div class="card-header p-0">
    <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
      type="button" data-bs-toggle="collapse" data-bs-target="#statisticsCollapse"
      aria-expanded="true" aria-controls="statisticsCollapse">
      <span>
        <i data-lucide="bell" class="icon-sm me-2"></i>Notification Statistics
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
                  <h4 class="mb-0">{{ number_format($stats['total'] ?? 0) }}</h4>
                </div>
                <i data-lucide="bell" class="icon-lg text-muted"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card unread">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Unread</h6>
                  <h4 class="mb-0">{{ number_format($stats['unread'] ?? 0) }}</h4>
                </div>
                <i data-lucide="bell-ring" class="icon-lg text-danger"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card today">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Today</h6>
                  <h4 class="mb-0">{{ number_format($stats['today'] ?? 0) }}</h4>
                </div>
                <i data-lucide="calendar" class="icon-lg text-success"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card week">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">This Week</h6>
                  <h4 class="mb-0">{{ number_format($stats['this_week'] ?? 0) }}</h4>
                </div>
                <i data-lucide="calendar-days" class="icon-lg text-primary"></i>
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
          <h6 class="card-title mb-0">Notifications Management</h6>
          <div class="d-flex flex-wrap gap-2">
            @canAccess('admin.notifications.send')
            <a href="{{ route('admin.notifications.send') }}" class="btn btn-primary btn-sm">
              <i data-lucide="send" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Send Notification</span>
              <span class="d-sm-none">Send</span>
            </a>
            @endcanAccess
            <a href="{{ route('admin.notifications.trashed') }}" class="btn btn-outline-secondary btn-sm">
              <i data-lucide="trash-2" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Deleted</span>
              <span class="d-sm-none">Deleted</span>
            </a>
            <button type="button" class="btn btn-outline-primary btn-sm" id="refresh-notifications">
              <i data-lucide="refresh-cw" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Refresh</span>
              <span class="d-sm-none">Refresh</span>
            </button>
            <button type="button" class="btn btn-outline-success btn-sm" id="mark-all-read">
              <i data-lucide="check-circle" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Mark All Read</span>
              <span class="d-sm-none">Mark Read</span>
            </button>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i data-lucide="download" class="icon-sm me-1"></i>
                <span class="d-none d-sm-inline">Export</span>
                <span class="d-sm-none">Export</span>
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('admin.notifications.export', ['format' => 'json']) }}">
                    <i data-lucide="file-text" class="icon-sm me-2"></i>Export as JSON
                  </a></li>
                <li><a class="dropdown-item" href="{{ route('admin.notifications.export', ['format' => 'csv']) }}">
                    <i data-lucide="table" class="icon-sm me-2"></i>Export as CSV
                  </a></li>
              </ul>
            </div>
            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
              <i data-lucide="upload" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Import</span>
              <span class="d-sm-none">Import</span>
            </button>
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-warning btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" id="bulkActionsBtn" disabled>
                <i data-lucide="layers" class="icon-sm me-1"></i>
                <span class="d-none d-sm-inline">Bulk Actions</span>
                <span class="d-sm-none">Bulk</span>
              </button>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item bulk-action" href="#" data-action="read">
                    <i data-lucide="check-circle" class="icon-sm me-2 text-success"></i>Mark as Read
                  </a></li>
                <li><a class="dropdown-item bulk-action" href="#" data-action="unread">
                    <i data-lucide="circle" class="icon-sm me-2 text-warning"></i>Mark as Unread
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

        <!-- Notifications Filters Section -->
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
                  <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search notifications...">
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="typeFilter" class="form-label">Type</label>
                  <select id="typeFilter" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach($notificationTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="statusFilter" class="form-label">Status</label>
                  <select id="statusFilter" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                  </select>
                </div>
                <!-- Date Filter Component -->
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <x-admin.date-filter />
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2 align-content-end">
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
          <table id="notificationsTable" class="table table-hover">
            <thead>
              <tr class="bg-dark">
                <th width="40">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label" for="selectAll"></label>
                  </div>
                </th>
                <th width="50">#</th>
                @if($isSuperAdmin)
                <th>User</th>
                @endif
                <th>Type</th>
                <th>Title</th>
                <th>Message</th>
                <th>Status</th>
                <th>Date</th>
                <th width="100">Actions</th>
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center" id="importModalLabel">
          <i data-lucide="upload" class="icon-sm me-2"></i>Import Notifications
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="importForm" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-3">
            <label for="importFile" class="form-label">Select File</label>
            <input type="file" class="form-control" id="importFile" name="file" accept=".json,.csv" required>
            <div class="form-text">Supported formats: JSON, CSV (Max size: 10MB)</div>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="overwriteExisting" name="overwrite">
              <label class="form-check-label" for="overwriteExisting">
                Overwrite existing notifications
              </label>
            </div>
          </div>
          <div class="alert alert-info">
            <i data-lucide="info" class="icon-sm me-2"></i>
            <strong>Import Format:</strong> The file should contain notification data with fields like title, message, type, etc.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <i data-lucide="x" class="icon-sm me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-sm btn-primary">
            <i data-lucide="upload" class="icon-sm me-1"></i>Import Notifications
          </button>
        </div>
      </form>
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
    const table = $('#notificationsTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('admin.notifications.index') }}",
        data: function(d) {
          d.type = $('#typeFilter').val();
          d.status = $('#statusFilter').val();
          // Use AdminDateFilter class
          Object.assign(d, dateFilter.getAjaxData());
        }
      },
      columns: [{
          data: 'checkbox',
          name: 'checkbox',
          orderable: false,
          searchable: false,
          className: 'align-content-center'
        },
        {
          data: 'readable_id',
          name: 'readable_id',
          className: 'align-content-center'
        },
        <?php if ($isSuperAdmin) { ?> {
            data: 'user',
            name: 'user',
            orderable: false,
            searchable: false,
            className: 'align-content-center'
          },
        <?php } ?> {
          data: 'type',
          name: 'type',
          orderable: false,
          searchable: false,
          className: 'align-content-center'
        },
        {
          data: 'title',
          name: 'title',
          className: 'align-content-center'
        },
        {
          data: 'message',
          name: 'message',
          className: 'align-content-center'
        },
        {
          data: 'status',
          name: 'status',
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

    // Search functionality
    $('#customSearchBox').on('keyup', function() {
      table.search(this.value).draw();
    });

    // Function to update active filters count
    function updateActiveFiltersCount() {
      let count = 0;
      if ($('#typeFilter').val()) count++;
      if ($('#statusFilter').val()) count++;
      if (dateFilter.getValue()) count++;

      const badge = $('#activeFiltersCount');
      if (count > 0) {
        badge.text(count).show();
      } else {
        badge.hide();
      }
    }



    // Filter functionality
    $('#typeFilter, #statusFilter').on('change', function() {
      updateActiveFiltersCount();
      table.ajax.reload();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
      $('#typeFilter').val('');
      $('#statusFilter').val('');
      $('#customSearchBox').val('');
      dateFilter.clear();
      updateActiveFiltersCount();
      table.search('').ajax.reload();
    });

    // Refresh notifications functionality
    $('#refresh-notifications').on('click', function() {
      const btn = $(this);
      const originalHtml = btn.html();

      // Show loading state
      btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>Refreshing...');

      // Refresh DataTable
      table.ajax.reload(function() {
        // Refresh navbar notifications
        if (window.notificationManager) {
          window.notificationManager.refresh();
        }

        // Reset button state
        btn.prop('disabled', false).html(originalHtml);

        // Re-initialize Lucide icons
        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }

        // Show success message
        Swal.fire({
          title: 'Refreshed!',
          text: 'Notifications have been refreshed.',
          icon: 'success',
          timer: 1500,
          showConfirmButton: false
        });
      });
    });

    // Mark all as read functionality
    $('#mark-all-read').on('click', function() {
      Swal.fire({
        title: 'Are you sure?',
        text: "This will mark all notifications as read!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="check-circle" class="icon-sm me-1"></i>Yes, mark all read!',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-sm btn-success me-2',
          cancelButton: 'btn btn-sm btn-secondary'
        },
        buttonsStyling: false,
        didOpen: () => {
          if (typeof lucide !== 'undefined') {
            lucide.createIcons();
          }
        }
      }).then((result) => {
        if (result.isConfirmed) {
          $.post('{{ route("admin.notifications.read-all") }}', {
              _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
              if (response.success) {
                Swal.fire({
                  title: 'Success!',
                  text: response.message,
                  icon: 'success',
                  confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
                  customClass: {
                    confirmButton: 'btn btn-sm btn-success'
                  },
                  buttonsStyling: false
                });
                table.ajax.reload();
                // Update navbar notification count
                if (window.notificationManager) {
                  window.notificationManager.refresh();
                }
              }
            })
            .fail(function() {
              Swal.fire({
                title: 'Error!',
                text: 'Something went wrong.',
                icon: 'error',
                confirmButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>OK',
                customClass: {
                  confirmButton: 'btn btn-sm btn-danger'
                },
                buttonsStyling: false
              });
            });
        }
      });
    });

    // Mark single notification as read
    $(document).on('click', '.mark-read-btn', function() {
      const notificationId = $(this).data('id');

      $.post(`/admin/notifications/${notificationId}/read`, {
          _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
          if (response.success) {
            Swal.fire({
              title: 'Success!',
              text: 'Notification marked as read',
              icon: 'success',
              timer: 1500,
              showConfirmButton: false
            });
            table.ajax.reload();
            // Update navbar notification count
            if (window.notificationManager) {
              window.notificationManager.refresh();
            }
          }
        })
        .fail(function() {
          Swal.fire({
            title: 'Error!',
            text: 'Something went wrong.',
            icon: 'error',
            confirmButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>OK',
            customClass: {
              confirmButton: 'btn btn-sm btn-danger'
            },
            buttonsStyling: false
          });
        });
    });

    // Delete notification functionality
    $(document).on('click', '.delete-btn', function() {
      var notificationId = $(this).data('id');

      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="trash-2" class="icon-sm me-1"></i>Yes, delete it!',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-sm btn-danger me-2',
          cancelButton: 'btn btn-sm btn-secondary'
        },
        buttonsStyling: false,
        didOpen: () => {
          if (typeof lucide !== 'undefined') {
            lucide.createIcons();
          }
        }
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "/admin/notifications/" + notificationId,
            type: 'DELETE',
            data: {
              _token: "{{ csrf_token() }}"
            },
            success: function(response) {
              if (response.success) {
                Swal.fire({
                  title: 'Deleted!',
                  text: response.message,
                  icon: 'success',
                  confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
                  customClass: {
                    confirmButton: 'btn btn-sm btn-success'
                  },
                  buttonsStyling: false
                });
                table.ajax.reload();
                // Update navbar notification count
                if (window.notificationManager) {
                  window.notificationManager.refresh();
                }
              }
            },
            error: function() {
              Swal.fire({
                title: 'Error!',
                text: 'Something went wrong.',
                icon: 'error',
                confirmButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>OK',
                customClass: {
                  confirmButton: 'btn btn-sm btn-danger'
                },
                buttonsStyling: false
              });
            }
          });
        }
      });
    });

    // Checkbox functionality
    $('#selectAll').on('change', function() {
      const isChecked = $(this).is(':checked');
      $('.row-checkbox').prop('checked', isChecked);
      updateBulkActionsButton();
    });

    $(document).on('change', '.row-checkbox', function() {
      const totalCheckboxes = $('.row-checkbox').length;
      const checkedCheckboxes = $('.row-checkbox:checked').length;

      $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
      updateBulkActionsButton();
    });

    function updateBulkActionsButton() {
      const checkedCount = $('.row-checkbox:checked').length;
      $('#bulkActionsBtn').prop('disabled', checkedCount === 0);
    }

    // Bulk actions
    $('.bulk-action').on('click', function(e) {
      e.preventDefault();
      const action = $(this).data('action');
      const selectedIds = $('.row-checkbox:checked').map(function() {
        return $(this).val();
      }).get();

      if (selectedIds.length === 0) {
        Swal.fire({
          title: 'Warning!',
          text: 'Please select at least one notification.',
          icon: 'warning',
          confirmButtonText: '<i data-lucide="alert-triangle" class="icon-sm me-1"></i>OK',
          customClass: {
            confirmButton: 'btn btn-sm btn-warning'
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

      let title, text, confirmButtonText, confirmButtonClass;

      switch (action) {
        case 'read':
          title = 'Mark as Read?';
          text = `Mark ${selectedIds.length} notification(s) as read?`;
          confirmButtonText = '<i data-lucide="check-circle" class="icon-sm me-1"></i>Mark as Read';
          confirmButtonClass = 'btn btn-sm btn-success me-2';
          break;
        case 'unread':
          title = 'Mark as Unread?';
          text = `Mark ${selectedIds.length} notification(s) as unread?`;
          confirmButtonText = '<i data-lucide="circle" class="icon-sm me-1"></i>Mark as Unread';
          confirmButtonClass = 'btn btn-sm btn-warning me-2';
          break;
        case 'delete':
          title = 'Delete Notifications?';
          text = `Are you sure you want to delete ${selectedIds.length} notification(s)? This action cannot be undone.`;
          confirmButtonText = '<i data-lucide="trash-2" class="icon-sm me-1"></i>Delete';
          confirmButtonClass = 'btn btn-sm btn-danger me-2';
          break;
      }

      Swal.fire({
        title: title,
        text: text,
        icon: action === 'delete' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: confirmButtonText,
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: confirmButtonClass,
          cancelButton: 'btn btn-sm btn-secondary'
        },
        buttonsStyling: false,
        didOpen: () => {
          if (typeof lucide !== 'undefined') {
            lucide.createIcons();
          }
        }
      }).then((result) => {
        if (result.isConfirmed) {
          performBulkAction(action, selectedIds);
        }
      });
    });

    function performBulkAction(action, ids) {
      const data = {
        action: action,
        ids: ids,
        _token: "{{ csrf_token() }}"
      };

      $.ajax({
        url: "{{ route('admin.notifications.bulk-action') }}",
        type: 'POST',
        data: data,
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
            table.ajax.reload();
            $('#selectAll').prop('checked', false);
            updateBulkActionsButton();

            // Update navbar notification count
            if (window.notificationManager) {
              window.notificationManager.refresh();
            }
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
            text: 'Something went wrong.',
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

    // Import functionality
    $('#importForm').on('submit', function(e) {
      e.preventDefault();

      const formData = new FormData(this);
      const submitBtn = $(this).find('button[type="submit"]');
      const originalText = submitBtn.html();

      submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>Importing...');

      $.ajax({
        url: "{{ route('admin.notifications.import') }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
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
            table.ajax.reload();
            $('#importModal').modal('hide');
            $('#importForm')[0].reset();

            if (response.errors && response.errors.length > 0) {
              let errorHtml = '<ul>';
              response.errors.forEach(error => {
                errorHtml += `<li>${error}</li>`;
              });
              errorHtml += '</ul>';

              Swal.fire({
                title: 'Import completed with warnings',
                html: errorHtml,
                icon: 'warning',
                confirmButtonText: '<i data-lucide="alert-triangle" class="icon-sm me-1"></i>OK',
                customClass: {
                  confirmButton: 'btn btn-sm btn-warning'
                },
                buttonsStyling: false,
                didOpen: () => {
                  if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                  }
                }
              });
            }
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
            text: 'Import failed.',
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
          submitBtn.prop('disabled', false).html(originalText);
          if (typeof lucide !== 'undefined') {
            lucide.createIcons();
          }
        }
      });
    });
  });
</script>
@endpush