@extends('admin.layout.master')

@section('title', $title ?? 'Deleted Users')
@section('description', $description ?? 'Manage soft-deleted user accounts with restore options')
@section('keywords', $keywords ?? 'deleted users, soft delete, restore users, trashed accounts')

@push('plugin-styles')
<link href="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
  .filter-chevron {
    transition: transform 0.2s ease-in-out;
  }

  /* Responsive table improvements */
  .table-responsive {
    overflow-x: auto;
  }

  .table-responsive .dropdown-menu {
    position: fixed !important;
    z-index: 1050;
  }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Deleted Users</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 pb-3 border-bottom gap-2">
          <h6 class="card-title mb-0">Deleted Users Management</h6>
          <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-sm">
              <i data-lucide="arrow-left" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Back to Users</span>
              <span class="d-sm-none">Back</span>
            </a>
          </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Deleted Users Filters Section -->
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
                  <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search deleted users...">
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="roleFilter" class="form-label">Role</label>
                  <select id="roleFilter" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                    <option value="super_admin">Super Admin</option>
                  </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <label for="deletedByFilter" class="form-label">Deleted By</label>
                  <select id="deletedByFilter" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="system">System</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>
                <!-- Date Filter Component -->
                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                  <x-admin.date-filter label="Deletion Date" />
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
          <table id="trashedUsersTable" class="table table-hover">
            <thead>
              <tr class="bg-dark">
                <th>Avatar</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Deleted Info</th>
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
    var table = $('#trashedUsersTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "{{ route('admin.users.trashed') }}",
        data: function(d) {
          d.role = $('#roleFilter').val();
          d.deleted_by = $('#deletedByFilter').val();
          // Use AdminDateFilter class
          Object.assign(d, dateFilter.getAjaxData());
        }
      },
      columns: [{
          data: 'avatar',
          name: 'avatar',
          orderable: false,
          searchable: false,
          className: 'text-center align-middle'
        },
        {
          data: 'name',
          name: 'name',
          className: 'align-middle'
        },
        {
          data: 'email',
          name: 'email',
          className: 'align-middle'
        },
        {
          data: 'role',
          name: 'role',
          orderable: false,
          searchable: false,
          className: 'text-center align-middle'
        },
        {
          data: 'deleted_info',
          name: 'deleted_info',
          orderable: false,
          searchable: false,
          className: 'text-center align-middle'
        },
        {
          data: 'action',
          name: 'action',
          orderable: false,
          searchable: false,
          className: 'text-center align-middle'
        }
      ],
      drawCallback: function() {
        // Initialize Lucide icons for newly loaded content
        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }

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

        for (let i = 0; i < totalPages; i++) {
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

        // Re-initialize lucide icons for pagination
        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }
      },
      order: [
        [1, 'asc']
      ],
      pageLength: <?= admin_items_per_page() ?>,
      responsive: true,
      dom: 'rt',
      language: {
        emptyTable: "No deleted users found"
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
      if ($('#roleFilter').val()) count++;
      if ($('#deletedByFilter').val()) count++;
      if (dateFilter.getValue()) count++;

      const badge = $('#activeFiltersCount');

      if (count > 0) {
        badge.text(count).fadeIn(200);
      } else {
        badge.fadeOut(200);
      }
    }



    // Filter functionality
    $('#roleFilter, #deletedByFilter').on('change', function() {
      updateActiveFiltersCount();
      table.ajax.reload();
    });

    // Clear filters
    $('#clearFilters').on('click', function(event) {
      event.stopPropagation();
      $('#roleFilter, #deletedByFilter').val('');
      $('#customSearchBox').val('');
      dateFilter.clear();
      updateActiveFiltersCount();
      table.search('').ajax.reload();
    });

    // Initialize filter count and button visibility on page load
    updateActiveFiltersCount();
    $('#customLength').on('change', function() {
      var value = $(this).val();
      table.page.len(value).draw();
    });
    // Previous / Next / Page click handling
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

    // Restore user
    $(document).on('click', '.restore-user', function(e) {
      e.preventDefault();
      var userId = $(this).data('id');

      Swal.fire({
        title: 'Are you sure?',
        text: "This will restore the user account!",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="undo" class="icon-sm me-1"></i>Yes, restore it!',
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
          $.ajax({
            url: "{{ route('admin.users.restore', ':id') }}".replace(':id', userId),
            type: 'POST',
            data: {
              _token: '{{ csrf_token() }}'
            },
            success: function(response) {
              if (response.success) {
                Swal.fire({
                  title: 'Restored!',
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
                  buttonsStyling: false,
                  didOpen: () => {
                    if (typeof lucide !== 'undefined') {
                      lucide.createIcons();
                    }
                  }
                });
              }
            },
            error: function(xhr) {
              var errorMessage = 'An error occurred while restoring the user.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
              Swal.fire({
                title: 'Error!',
                text: errorMessage,
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

    // Force delete user (permanent delete)
    $(document).on('click', '.force-delete-user', function(e) {
      e.preventDefault();
      var userId = $(this).data('id');

      Swal.fire({
        title: 'Are you absolutely sure?',
        html: '<div class="text-danger"><strong>This action cannot be undone!</strong></div><br>This will permanently delete the user account and all associated data.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="trash-2" class="icon-sm me-1"></i>Yes, permanently delete!',
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
            url: "{{ route('admin.users.force-delete', ':id') }}".replace(':id', userId),
            type: 'DELETE',
            data: {
              _token: '{{ csrf_token() }}'
            },
            success: function(response) {
              if (response.success) {
                Swal.fire({
                  title: 'Permanently Deleted!',
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
                  buttonsStyling: false,
                  didOpen: () => {
                    if (typeof lucide !== 'undefined') {
                      lucide.createIcons();
                    }
                  }
                });
              }
            },
            error: function(xhr) {
              var errorMessage = 'An error occurred while permanently deleting the user.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
              Swal.fire({
                title: 'Error!',
                text: errorMessage,
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