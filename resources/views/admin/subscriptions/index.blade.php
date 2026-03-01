@extends('admin.layout.master')

@section('title', $title ?? 'Subscriptions')
@section('description', $description ?? 'Manage User Subscriptions.')

@push('plugin-styles')
<link href="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
    .stats-card {
        border-left: 4px solid;
    }

    .stats-card.total {
        border-left-color: #6c757d;
    }

    .stats-card.active-sub {
        border-left-color: #198754;
    }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item active" aria-current="page">Subscriptions</li>
    </ol>
</nav>

<!-- Statistics Section -->
<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
                <div class="card stats-card total">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0">Total Subscriptions</h6>
                                <h4 class="mb-0">{{ number_format($stats['total'] ?? 0) }}</h4>
                            </div>
                            <i data-lucide="users" class="icon-lg text-muted"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
                <div class="card stats-card active-sub">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0">Active Subscriptions</h6>
                                <h4 class="mb-0">{{ number_format($stats['active'] ?? 0) }}</h4>
                            </div>
                            <i data-lucide="check-circle" class="icon-lg text-success"></i>
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
                    <h6 class="card-title mb-0">Subscription Management</h6>
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
                                    <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search subscriptions...">
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                                    <label for="statusFilter" class="form-label">Status</label>
                                    <select id="statusFilter" class="form-select form-select-sm">
                                        <option value="">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                                    <label for="planFilter" class="form-label">Plan</label>
                                    <select id="planFilter" class="form-select form-select-sm">
                                        <option value="">All Plans</option>
                                        @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                        @endforeach
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
                    <table id="subscriptionsTable" class="table table-hover w-100">
                        <thead>
                            <tr class="bg-dark">
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Starts</th>
                                <th>Ends</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
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
@endpush

@push('custom-scripts')
<script>
    $(document).ready(function() {
        const table = $('#subscriptionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.subscriptions.index') }}",
                data: function(d) {
                    d.status = $('#statusFilter').val();
                    d.plan_id = $('#planFilter').val();
                }
            },
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'user_name',
                    name: 'user.name'
                },
                {
                    data: 'user_email',
                    name: 'user.email'
                },
                {
                    data: 'plan_name',
                    name: 'plan.name'
                },
                {
                    data: 'status_badge',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'starts_at',
                    name: 'starts_at'
                },
                {
                    data: 'ends_at',
                    name: 'ends_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
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

        // Search functionality
        $('#customSearchBox').on('keyup', function() {
            table.search(this.value).draw();
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

        function updateActiveFiltersCount() {
            let count = 0;
            if ($('#statusFilter').val()) count++;
            if ($('#planFilter').val()) count++;

            const badge = $('#activeFiltersCount');
            if (count > 0) {
                badge.text(count).show();
            } else {
                badge.hide();
            }
        }

        $('#statusFilter, #planFilter').on('change', function() {
            updateActiveFiltersCount();
            table.ajax.reload();
        });

        $('#clearFilters').on('click', function() {
            $('#statusFilter').val('');
            $('#planFilter').val('');
            $('#customSearchBox').val('');
            updateActiveFiltersCount();
            table.search('').ajax.reload();
        });

        // Cancel subscription
        $(document).on('click', '.cancel-subscription', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');

            Swal.fire({
                title: 'Are you sure?',
                text: "This will cancel the subscription immediately.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i data-lucide="x-circle" class="icon-sm me-1"></i>Yes, cancel it!',
                didOpen: () => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Cancelled!', response.message, 'success');
                                table.ajax.reload();
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush