@extends('admin.layout.master')

@section('title', $title ?? 'Email Templates')
@section('description', $description ?? 'Manage email templates for system notifications')
@section('keywords', $keywords ?? 'email templates, notifications, automated emails')

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

  .stats-card.types {
    border-left-color: #0d6efd;
  }

  .stats-card.sent {
    border-left-color: #6f42c1;
  }

  .stats-card.recent {
    border-left-color: #fd7e14;
  }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Email Templates</li>
  </ol>
</nav>
<!-- Email Template Statistics Section -->
<div class="card mb-3">
  <div class="card-header p-0">
    <button class="btn btn-sm btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
      type="button" data-bs-toggle="collapse" data-bs-target="#statisticsCollapse"
      aria-expanded="true" aria-controls="statisticsCollapse">
      <span>
        <i data-lucide="mail" class="icon-sm me-2"></i>Template Statistics
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
                <i data-lucide="mail" class="icon-lg text-muted"></i>
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
                <i data-lucide="check-circle" class="icon-lg text-success"></i>
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
                <i data-lucide="x-circle" class="icon-lg text-danger"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card types">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Types</h6>
                  <h4 class="mb-0">{{ number_format($stats['types']) }}</h4>
                </div>
                <i data-lucide="folder" class="icon-lg text-primary"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card sent">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Total Sent</h6>
                  <h4 class="mb-0">{{ number_format($stats['total_sent']) }}</h4>
                </div>
                <i data-lucide="send" class="icon-lg text-purple"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
          <div class="card stats-card recent">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Recent (30d)</h6>
                  <h4 class="mb-0">{{ number_format($stats['recent_sent']) }}</h4>
                </div>
                <i data-lucide="clock" class="icon-lg text-warning"></i>
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
          <h6 class="card-title mb-0">Email Templates Management</h6>
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.email-templates.create') }}" class="btn btn-primary btn-sm">
              <i data-lucide="plus" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Add Template</span>
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

        <!-- Email Templates Filters Section -->
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
                  <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search templates...">
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="typeFilter" class="form-label">Type</label>
                  <select id="typeFilter" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach($types as $key => $value)
                    <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                  </select>
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
          <table id="emailTemplatesTable" class="table table-hover">
            <thead>
              <tr class="bg-dark">
                <th>ID</th>
                <th>Name</th>
                <th>Subject</th>
                <th>Type</th>
                <th>Status</th>
                <th>Created By</th>
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
    const table = $('#emailTemplatesTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('admin.email-templates.index') }}",
        data: function(d) {
          d.type = $('#typeFilter').val();
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
          data: 'subject',
          name: 'subject',
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
          data: 'status',
          name: 'is_active',
          orderable: false,
          searchable: false,
          className: 'align-content-center'
        },
        {
          data: 'creator_name',
          name: 'creator.name',
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
      const length = $(this).val();
      table.page.len(length).draw();
    });

    // Delete template functionality
    $(document).on('click', '.delete-template', function() {
      var templateId = $(this).data('id');

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
            url: "/admin/email-templates/" + templateId,
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
      });
    });
  });
</script>
@endpush