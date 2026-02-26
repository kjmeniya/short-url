@extends('admin.layout.master')

@section('title', $title ?? 'Blog Management')
@section('description', $description ?? 'Manage blog posts, articles, and content for your website.')
@section('keywords', $keywords ?? 'blog management, posts, articles, content management, website blog')

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

    .stats-card.published {
        border-left-color: #198754;
    }

    .stats-card.draft {
        border-left-color: #ffc107;
    }

    .stats-card.featured {
        border-left-color: #0d6efd;
    }

    .stats-card.archived {
        border-left-color: #dc3545;
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
        <li class="breadcrumb-item active" aria-current="page">Blog Management</li>
    </ol>
</nav>

<!-- Blog Statistics Section -->
<div class="card mb-3">
    <div class="card-header p-0">
        <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
            type="button" data-bs-toggle="collapse" data-bs-target="#statisticsCollapse"
            aria-expanded="true" aria-controls="statisticsCollapse">
            <span>
                <i data-lucide="bar-chart-3" class="icon-sm me-2"></i>Blog Statistics
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
                                <i data-lucide="file-text" class="icon-lg text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
                    <div class="card stats-card published">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Published</h6>
                                    <h4 class="mb-0">{{ number_format($stats['published']) }}</h4>
                                </div>
                                <i data-lucide="check-circle" class="icon-lg text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
                    <div class="card stats-card draft">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Draft</h6>
                                    <h4 class="mb-0">{{ number_format($stats['draft']) }}</h4>
                                </div>
                                <i data-lucide="edit" class="icon-lg text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
                    <div class="card stats-card featured">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Featured</h6>
                                    <h4 class="mb-0">{{ number_format($stats['featured']) }}</h4>
                                </div>
                                <i data-lucide="star" class="icon-lg text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
                    <div class="card stats-card archived">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Archived</h6>
                                    <h4 class="mb-0">{{ number_format($stats['archived']) }}</h4>
                                </div>
                                <i data-lucide="archive" class="icon-lg text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
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

<!-- Main Content -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                    <h6 class="card-title mb-0">Blog Management</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary btn-sm">
                            <i data-lucide="plus" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Add Post</span>
                            <span class="d-sm-none">Add</span>
                        </a>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i data-lucide="download" class="icon-sm me-1"></i>
                                <span class="d-none d-sm-inline">Export</span>
                                <span class="d-sm-none">Export</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.blogs.export', ['format' => 'json']) }}">
                                        <i data-lucide="file-text" class="icon-sm me-2"></i>Export as JSON
                                    </a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.blogs.export', ['format' => 'csv']) }}">
                                        <i data-lucide="table" class="icon-sm me-2"></i>Export as CSV
                                    </a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.blogs.export', ['format' => 'sql']) }}">
                                        <i data-lucide="database" class="icon-sm me-2"></i>Export as SQL
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
                                <li><a class="dropdown-item bulk-action" href="#" data-action="publish">
                                        <i data-lucide="check-circle" class="icon-sm me-2 text-success"></i>Publish Selected
                                    </a></li>
                                <li><a class="dropdown-item bulk-action" href="#" data-action="draft">
                                        <i data-lucide="edit" class="icon-sm me-2 text-warning"></i>Move to Draft
                                    </a></li>
                                <li><a class="dropdown-item bulk-action" href="#" data-action="archive">
                                        <i data-lucide="archive" class="icon-sm me-2 text-info"></i>Archive Selected
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item bulk-action" href="#" data-action="feature">
                                        <i data-lucide="star" class="icon-sm me-2 text-primary"></i>Mark as Featured
                                    </a></li>
                                <li><a class="dropdown-item bulk-action" href="#" data-action="unfeature">
                                        <i data-lucide="star-off" class="icon-sm me-2 text-secondary"></i>Remove Featured
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

                <!-- Blog Filters Section -->
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
                                    <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search posts...">
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                                    <label for="statusFilter" class="form-label">Status</label>
                                    <select id="statusFilter" class="form-select form-select-sm">
                                        <option value="">All Status</option>
                                        <option value="published">Published</option>
                                        <option value="draft">Draft</option>
                                        <option value="archived">Archived</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                                    <label for="featuredFilter" class="form-label">Featured</label>
                                    <select id="featuredFilter" class="form-select form-select-sm">
                                        <option value="">All Posts</option>
                                        <option value="1">Featured Only</option>
                                        <option value="0">Regular Only</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                                    <label for="authorFilter" class="form-label">Author</label>
                                    <select id="authorFilter" class="form-select form-select-sm">
                                        <option value="">All Authors</option>
                                        @foreach($authors as $author)
                                        <option value="{{ $author->id }}">{{ $author->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Date Filter Dropdown -->
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
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


                <!-- Date Range Modal Component -->
                <x-admin.date-range-modal />

                <div class="table-responsive mb-3">
                    <table id="blogTable" class="table table-hover">
                        <thead>
                            <tr class="bg-dark">
                                <th width="40">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label" for="selectAll"></label>
                                    </div>
                                </th>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Author</th>
                                <th>Views</th>
                                <th>Created At</th>
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Blog Posts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="importForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Select File</label>
                        <input type="file" class="form-control" id="importFile" name="file" accept=".json,.csv,.sql" required>
                        <div class="form-text">Supported formats: JSON, CSV, SQL (Max size: {{ $maxUploadSize ?? 10 }}MB)</div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="overwriteExisting" name="overwrite" value="1">
                        <label class="form-check-label" for="overwriteExisting">
                            Overwrite existing posts with same slug
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        <i data-lucide="x" class="icon-sm me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i data-lucide="upload" class="icon-sm me-1"></i>Import
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
        const table = $('#blogTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.blogs.index') }}",
                data: function(d) {
                    d.status = $('#statusFilter').val();
                    d.featured = $('#featuredFilter').val();
                    d.author = $('#authorFilter').val();
                    // Use AdminDateFilter class
                    Object.assign(d, dateFilter.getAjaxData());
                }
            },
            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'align-content-center',
                    render: function(data, type, row) {
                        return `<div class="form-check">
                            <input class="form-check-input row-checkbox" type="checkbox" value="${row.id}">
                            <label class="form-check-label"></label>
                        </div>`;
                    }
                },
                {
                    data: 'id',
                    name: 'id',
                    className: 'align-content-center'
                },
                {
                    data: 'featured_image',
                    name: 'featured_image',
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
                    data: 'status_badge',
                    name: 'status',
                    orderable: false,
                    searchable: false,
                    className: 'align-content-center'
                },
                {
                    data: 'featured_badge',
                    name: 'is_featured',
                    orderable: false,
                    searchable: false,
                    className: 'align-content-center'
                },
                {
                    data: 'author_name',
                    name: 'author.name',
                    className: 'align-content-center'
                },
                {
                    data: 'views_count',
                    name: 'views_count',
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
                [1, 'desc']
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
            if ($('#statusFilter').val()) count++;
            if ($('#featuredFilter').val()) count++;
            if ($('#authorFilter').val()) count++;
            if (dateFilter.getValue()) count++;

            const badge = $('#activeFiltersCount');
            if (count > 0) {
                badge.text(count).show();
            } else {
                badge.hide();
            }
        }


        // Filter functionality
        $('#statusFilter, #featuredFilter, #authorFilter').on('change', function() {
            updateActiveFiltersCount();
            table.ajax.reload();
        });

        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#statusFilter').val('');
            $('#featuredFilter').val('');
            $('#authorFilter').val('');
            $('#customSearchBox').val('');
            dateFilter.clear(); // Use AdminDateFilter class method
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

        // Delete blog functionality
        $(document).on('click', '.delete-blog', function() {
            var blogId = $(this).data('id');

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
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/admin/blog/" + blogId,
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
                    text: 'Please select at least one post.',
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

            let confirmMessage = '';
            let confirmButtonText = '';

            switch (action) {
                case 'publish':
                    confirmMessage = `Are you sure you want to publish ${selectedIds.length} selected posts?`;
                    confirmButtonText = 'Yes, publish them!';
                    break;
                case 'draft':
                    confirmMessage = `Are you sure you want to move ${selectedIds.length} selected posts to draft?`;
                    confirmButtonText = 'Yes, move to draft!';
                    break;
                case 'archive':
                    confirmMessage = `Are you sure you want to archive ${selectedIds.length} selected posts?`;
                    confirmButtonText = 'Yes, archive them!';
                    break;
                case 'feature':
                    confirmMessage = `Are you sure you want to mark ${selectedIds.length} selected posts as featured?`;
                    confirmButtonText = 'Yes, mark as featured!';
                    break;
                case 'unfeature':
                    confirmMessage = `Are you sure you want to remove featured status from ${selectedIds.length} selected posts?`;
                    confirmButtonText = 'Yes, remove featured!';
                    break;
                case 'delete':
                    confirmMessage = `Are you sure you want to delete ${selectedIds.length} selected posts? This action cannot be undone!`;
                    confirmButtonText = 'Yes, delete them!';
                    break;
            }

            let confirmIcon = '';
            let confirmButtonClass = 'btn btn-sm me-2 btn-primary';

            switch (action) {
                case 'delete':
                    confirmIcon = '<i data-lucide="trash-2" class="icon-sm me-1"></i>';
                    confirmButtonClass = 'btn btn-sm me-2 btn-danger';
                    break;
                case 'publish':
                    confirmIcon = '<i data-lucide="check-circle" class="icon-sm me-1"></i>';
                    confirmButtonClass = 'btn btn-sm me-2 btn-success';
                    break;
                case 'draft':
                    confirmIcon = '<i data-lucide="edit" class="icon-sm me-1"></i>';
                    confirmButtonClass = 'btn btn-sm me-2 btn-warning';
                    break;
                case 'archive':
                    confirmIcon = '<i data-lucide="archive" class="icon-sm me-1"></i>';
                    confirmButtonClass = 'btn btn-sm me-2 btn-info';
                    break;
                case 'feature':
                    confirmIcon = '<i data-lucide="star" class="icon-sm me-1"></i>';
                    confirmButtonClass = 'btn btn-sm me-2 btn-primary';
                    break;
                case 'unfeature':
                    confirmIcon = '<i data-lucide="star-off" class="icon-sm me-1"></i>';
                    confirmButtonClass = 'btn btn-sm me-2 btn-secondary';
                    break;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: confirmMessage,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: confirmIcon + confirmButtonText,
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
                url: "{{ route('admin.blogs.bulk-action') }}",
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
                url: "{{ route('admin.blogs.import') }}",
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
                }
            });
        });

        // Ensure Lucide icons are loaded
        function ensureLucideIcons() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            } else {
                // Fallback: try to load lucide if not available
                setTimeout(ensureLucideIcons, 100);
            }
        }

        // Call after any dynamic content changes
        $(document).ajaxComplete(function() {
            ensureLucideIcons();
        });

        // Initial load
        ensureLucideIcons();
    });
</script>
@endpush