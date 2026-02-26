@extends('admin.layout.master')

@section('title', $title ?? 'Roles')
@section('description', $description ?? 'Manage user roles and permissions')
@section('keywords', $keywords ?? 'roles, user roles, role management, access control')

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
    border-left-color: #dc3545;
  }

  .stats-card.with-users {
    border-left-color: #0d6efd;
  }

  .stats-card.without-users {
    border-left-color: #fd7e14;
  }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Roles</li>
  </ol>
</nav>

<!-- Role Statistics Section -->
<div class="card mb-3">
  <div class="card-header p-0">
    <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
      type="button" data-bs-toggle="collapse" data-bs-target="#statisticsCollapse"
      aria-expanded="true" aria-controls="statisticsCollapse">
      <span>
        <i data-lucide="shield" class="icon-sm me-2"></i>Role Statistics
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
                <i data-lucide="shield" class="icon-lg text-muted"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card active">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Active</h6>
                  <h4 class="mb-0">{{ number_format($stats['active']) }}</h4>
                </div>
                <i data-lucide="shield-check" class="icon-lg text-success"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card inactive">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Inactive</h6>
                  <h4 class="mb-0">{{ number_format($stats['inactive']) }}</h4>
                </div>
                <i data-lucide="shield-x" class="icon-lg text-danger"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card with-users">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">With Users</h6>
                  <h4 class="mb-0">{{ number_format($stats['with_users']) }}</h4>
                </div>
                <i data-lucide="users" class="icon-lg text-primary"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card without-users">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Empty Roles</h6>
                  <h4 class="mb-0">{{ number_format($stats['without_users']) }}</h4>
                </div>
                <i data-lucide="user-x" class="icon-lg text-warning"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card active">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Active Rate</h6>
                  <h4 class="mb-0">{{ $stats['active_rate'] }}%</h4>
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
          <h6 class="card-title mb-0">Roles Management</h6>
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-info btn-sm">
              <i data-lucide="shield" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Permissions</span>
              <span class="d-sm-none">Permissions</span>
            </a>
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
              <i data-lucide="plus" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Add Role</span>
              <span class="d-sm-none">Add</span>
            </a>
          </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Roles Filters Section -->
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
                  <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search roles...">
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="statusFilter" class="form-label">Status</label>
                  <select id="statusFilter" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <!-- Date Filter Component -->
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <x-admin.date-filter label="Created Date" />
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-2 mt-sm-0 mb-0 mb-sm-2 align-items-end d-flex">
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
          <table id="rolesTable" class="table table-hover">
            <thead>
              <tr class="bg-dark">
                <th>ID</th>
                <th>Role Name</th>
                <th>Display Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Permissions</th>
                <th>Users</th>
                <th>Created</th>
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

    // Initialize Admin Date Filter FIRST (before DataTable)
    const dateFilter = new AdminDateFilter({
      onFilterChange: function() {
        updateActiveFiltersCount();
        table.ajax.reload();
      }
    });

    // Initialize DataTable
    const table = $('#rolesTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('admin.roles.index') }}",
        data: function(d) {
          d.status = $('#statusFilter').val();
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
          data: 'name',
          name: 'name',
          className: 'align-content-center'
        },
        {
          data: 'display_name',
          name: 'display_name',
          className: 'align-content-center'
        },
        {
          data: 'description',
          name: 'description',
          orderable: false,
          className: 'align-content-center'
        },
        {
          data: 'status',
          name: 'is_active',
          orderable: false,
          className: 'align-content-center'
        },
        {
          data: 'permissions_count',
          name: 'permissions_count',
          orderable: false,
          className: 'align-content-center'
        },
        {
          data: 'users_count',
          name: 'users_count',
          orderable: false,
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

        var paginationHtml = '';
        var startPage = Math.max(0, currentPage - 2);
        var endPage = Math.min(totalPages - 1, currentPage + 2);

        // Previous button
        paginationHtml += `
          <li class="page-item ${currentPage === 0 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">
              <i data-lucide="chevron-left"></i>
            </a>
          </li>
        `;

        // Page numbers
        for (var i = startPage; i <= endPage; i++) {
          paginationHtml += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
              <a class="page-link" href="#" data-page="${i}">${i + 1}</a>
            </li>
          `;
        }

        // Next button
        paginationHtml += `
          <li class="page-item ${currentPage === totalPages - 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">
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
        [7, 'desc']
      ],
      pageLength: <?= admin_items_per_page() ?>,
      responsive: true,
      dom: 'rt'
    });

    // Custom pagination click handler
    $(document).on('click', '#customPagination .page-link', function(e) {
      e.preventDefault();
      var page = $(this).data('page');
      if (page !== undefined && !$(this).parent().hasClass('disabled')) {
        table.page(page).draw('page');
      }
    });

    // Custom length change handler
    $('#customLength').on('change', function() {
      var length = $(this).val();
      table.page.len(length).draw();
    });

    // Custom search functionality
    $('#customSearchBox').on('keyup', function() {
      table.search(this.value).draw();
    });

    // Filter functionality
    $('#statusFilter, #dateFilter').on('change', function() {
      table.ajax.reload();
      updateActiveFiltersCount();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
      $('#statusFilter').val('');
      $('#customSearchBox').val('');
      dateFilter.clear();
      table.search('').ajax.reload();
      updateActiveFiltersCount();
    });

    // Update active filters count
    function updateActiveFiltersCount() {
      let count = 0;
      if ($('#statusFilter').val()) count++;
      if (dateFilter.getValue()) count++;

      const badge = $('#activeFiltersCount');
      if (count > 0) {
        badge.text(count).show();
      } else {
        badge.hide();
      }
    }

    // Filter chevron animation
    $('[data-bs-toggle="collapse"]').on('click', function() {
      const chevron = $(this).find('.filter-chevron');
      chevron.toggleClass('rotate-180');
    });

    // Delete role functionality
    $(document).on('click', '.delete-role', function(e) {
      e.preventDefault();
      const roleId = $(this).data('id');

      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="trash-2" class="icon-sm me-1"></i>Yes, delete it!',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-sm btn-danger',
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
            url: "{{ route('admin.roles.destroy', ':id') }}".replace(':id', roleId),
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
                  buttonsStyling: false,
                  didOpen: () => {
                    if (typeof lucide !== 'undefined') {
                      lucide.createIcons();
                    }
                  }
                });
                table.ajax.reload();
              } else {
                Swal.fire({
                  title: 'Error!',
                  text: response.message,
                  icon: 'error',
                  confirmButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>OK',
                  customClass: {
                    confirmButton: 'btn btn-sm btn-danger'
                  },
                  buttonsStyling: false
                });
              }
            },
            error: function(xhr) {
              const response = xhr.responseJSON;
              Swal.fire({
                title: 'Error!',
                text: response.message || 'Something went wrong!',
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
      });
    });
  });
</script>

<style>
  .rotate-180 {
    transform: rotate(180deg);
  }
</style>
@endpush