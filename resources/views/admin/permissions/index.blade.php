@extends('admin.layout.master')

@section('title', $title ?? 'Permissions')
@section('description', $description ?? 'Manage system permissions and access control')
@section('keywords', $keywords ?? 'permissions, access control, authorization, security')

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

  .stats-card.assigned {
    border-left-color: #198754;
  }

  .stats-card.unassigned {
    border-left-color: #dc3545;
  }

  .stats-card.categories {
    border-left-color: #0d6efd;
  }

  .stats-card.roles {
    border-left-color: #6f42c1;
  }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Permissions</li>
  </ol>
</nav>

<!-- Permission Statistics Section -->
<div class="card mb-3">
  <div class="card-header p-0">
    <button class="btn btn-sm btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
      type="button" data-bs-toggle="collapse" data-bs-target="#statisticsCollapse"
      aria-expanded="true" aria-controls="statisticsCollapse">
      <span>
        <i data-lucide="shield" class="icon-sm me-2"></i>Permission Statistics
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
          <div class="card stats-card assigned">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Assigned</h6>
                  <h4 class="mb-0">{{ number_format($stats['assigned']) }}</h4>
                </div>
                <i data-lucide="shield-check" class="icon-lg text-success"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card unassigned">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Unassigned</h6>
                  <h4 class="mb-0">{{ number_format($stats['unassigned']) }}</h4>
                </div>
                <i data-lucide="shield-x" class="icon-lg text-danger"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card categories">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Categories</h6>
                  <h4 class="mb-0">{{ number_format($stats['categories']) }}</h4>
                </div>
                <i data-lucide="folder" class="icon-lg text-primary"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card roles">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Roles w/ Perms</h6>
                  <h4 class="mb-0">{{ number_format($stats['roles_with_permissions']) }}</h4>
                </div>
                <i data-lucide="users" class="icon-lg text-purple"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card assigned">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Assignment Rate</h6>
                  <h4 class="mb-0">{{ $stats['assignment_rate'] }}%</h4>
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
          <h6 class="card-title mb-0">Permissions Management</h6>
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.permissions.sync') }}" class="btn btn-outline-warning btn-sm">
              <i data-lucide="refresh-cw" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Sync Permissions</span>
              <span class="d-sm-none">Sync</span>
            </a>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-primary btn-sm">
              <i data-lucide="users" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Roles</span>
              <span class="d-sm-none">Roles</span>
            </a>
          </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Permissions Filters Section -->
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
                  <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search permissions...">
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="categoryFilter" class="form-label">Category</label>
                  <select id="categoryFilter" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                    <option value="{{ $category }}">{{ ucfirst(str_replace('admin_', '', $category)) }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="methodFilter" class="form-label">HTTP Method</label>
                  <select id="methodFilter" class="form-select form-select-sm">
                    <option value="">All Methods</option>
                    @foreach($methods as $method)
                    <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                  </select>
                </div>
                <!-- Date Filter Component -->
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <x-admin.date-filter label="Created Date" />
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
          <table id="permissionsTable" class="table table-hover">
            <thead>
              <tr class="bg-dark">
                <th>ID</th>
                <th>Permission Name</th>
                <th>Display Name</th>
                <th>Method</th>
                <th>Category</th>
                <th>Route Info</th>
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
    const table = $('#permissionsTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('admin.permissions.index') }}",
        data: function(d) {
          d.category = $('#categoryFilter').val();
          d.method = $('#methodFilter').val();
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
          data: 'method',
          name: 'method',
          orderable: false,
          className: 'align-content-center'
        },
        {
          data: 'category',
          name: 'category',
          orderable: false,
          className: 'align-content-center'
        },
        {
          data: 'route_info',
          name: 'route_name',
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
        [6, 'desc']
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
    $('#categoryFilter, #methodFilter, #dateFilter').on('change', function() {
      table.ajax.reload();
      updateActiveFiltersCount();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
      $('#categoryFilter, #methodFilter').val('');
      $('#customSearchBox').val('');
      dateFilter.clear();
      table.search('').ajax.reload();
      updateActiveFiltersCount();
    });

    // Update active filters count
    function updateActiveFiltersCount() {
      let count = 0;
      if ($('#categoryFilter').val()) count++;
      if ($('#methodFilter').val()) count++;
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
  });
</script>

<style>
  .rotate-180 {
    transform: rotate(180deg);
  }
</style>
@endpush