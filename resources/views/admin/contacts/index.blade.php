@extends('admin.layout.master')

@section('title', $title ?? 'Contact Messages')
@section('description', $description ?? 'View and manage all contact form submissions from website visitors.')
@section('keywords', $keywords ?? 'contacts, messages, inquiries, customer support')

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

    .stats-card.new {
        border-left-color: #0d6efd;
    }

    .stats-card.read {
        border-left-color: #0dcaf0;
    }

    .stats-card.replied {
        border-left-color: #198754;
    }

    .stats-card.archived {
        border-left-color: #6c757d;
    }

    .stats-card.spam {
        border-left-color: #dc3545;
    }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item active" aria-current="page">Contact Messages</li>
    </ol>
</nav>

<!-- Contact Statistics Section -->
<div class="card mb-3">
    <div class="card-header p-0">
        <button class="btn btn-sm btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
            type="button" data-bs-toggle="collapse" data-bs-target="#statisticsCollapse"
            aria-expanded="true" aria-controls="statisticsCollapse">
            <span>
                <i data-lucide="bar-chart" class="icon-sm me-2"></i>Contact Statistics
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
                    <div class="card stats-card new">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">New</h6>
                                    <h4 class="mb-0">{{ number_format($stats['new']) }}</h4>
                                </div>
                                <i data-lucide="mail-open" class="icon-lg text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
                    <div class="card stats-card read">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Read</h6>
                                    <h4 class="mb-0">{{ number_format($stats['read']) }}</h4>
                                </div>
                                <i data-lucide="eye" class="icon-lg text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
                    <div class="card stats-card replied">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Replied</h6>
                                    <h4 class="mb-0">{{ number_format($stats['replied']) }}</h4>
                                </div>
                                <i data-lucide="check-circle" class="icon-lg text-success"></i>
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
                                <i data-lucide="archive" class="icon-lg text-secondary"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-3 col-xxl-2 mb-3">
                    <div class="card stats-card spam">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title mb-0">Spam</h6>
                                    <h4 class="mb-0">{{ number_format($stats['spam']) }}</h4>
                                </div>
                                <i data-lucide="alert-triangle" class="icon-lg text-danger"></i>
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
                    <h6 class="card-title mb-0">Contact Messages Management</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" id="exportContacts" class="btn btn-outline-primary btn-sm">
                            <i data-lucide="download" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Export</span>
                            <span class="d-sm-none">Export</span>
                        </button>
                        <button type="button" id="refreshContacts" class="btn btn-outline-secondary btn-sm">
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

                <!-- Contact Filters Section -->
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
                                    <input type="text" id="customSearchBox" class="form-control form-control-sm" placeholder="Search contacts...">
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
                                    <label for="spamFilter" class="form-label">Spam Filter</label>
                                    <select id="spamFilter" class="form-select form-select-sm">
                                        <option value="">All Messages</option>
                                        <option value="false">Legitimate Only</option>
                                        <option value="true">Spam Only</option>
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
                    <table id="contactsTable" class="table table-hover">
                        <thead>
                            <tr class="bg-dark">
                                <th>ID</th>
                                <th>Contact Info</th>
                                <th>Subject & Message</th>
                                <th>Status</th>
                                <th>Spam</th>
                                <th>Created At</th>
                                <th class="w-125px">Actions</th>
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
        const table = $('#contactsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.contacts.index') }}",
                data: function(d) {
                    d.status = $('#statusFilter').val();
                    d.is_spam = $('#spamFilter').val();
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
                    data: 'contact_info',
                    name: 'name',
                    className: 'align-content-center'
                },
                {
                    data: 'subject_preview',
                    name: 'subject',
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
                    data: 'spam_badge',
                    name: 'is_spam',
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
        $('#statusFilter, #spamFilter').on('change', function() {
            table.draw();
            updateActiveFiltersCount();
        });



        // Clear filters
        $('#clearFilters').on('click', function() {
            $('#customSearchBox').val('');
            $('#statusFilter').val('');
            $('#spamFilter').val('');
            dateFilter.clear();
            table.search('').draw();
            updateActiveFiltersCount();
        });

        // Export functionality
        $('#exportContacts').on('click', function() {
            const params = new URLSearchParams({
                status: $('#statusFilter').val(),
                is_spam: $('#spamFilter').val()
            });

            // Add date filter parameters
            const dateParams = dateFilter.getAjaxData();
            for (const [key, value] of Object.entries(dateParams)) {
                if (value) params.append(key, value);
            }

            window.location.href = "{{ route('admin.contacts.export') }}?" + params.toString();
        });

        // Refresh functionality
        $('#refreshContacts').on('click', function() {
            table.ajax.reload();
        });

        // Update active filters count
        function updateActiveFiltersCount() {
            let count = 0;
            if ($('#customSearchBox').val()) count++;
            if ($('#statusFilter').val()) count++;
            if ($('#spamFilter').val()) count++;
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

    // Mark as read function
    function markAsRead(contactId) {
        Swal.fire({
            title: 'Mark as Read?',
            text: 'This will mark the contact message as read.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>Yes, mark as read',
            cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-sm btn-info',
                cancelButton: 'btn btn-sm btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ route('admin.contacts.mark-read', ':id') }}".replace(':id', contactId))
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
                        $('#contactsTable').DataTable().ajax.reload();
                    })
                    .fail(function(xhr) {
                        const error = xhr.responseJSON?.error || 'Failed to mark as read';
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

    // Mark as spam function
    function markAsSpam(contactId) {
        Swal.fire({
            title: 'Mark as Spam?',
            text: 'This will mark the contact message as spam and archive it.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i data-lucide="alert-triangle" class="icon-sm me-1"></i>Yes, mark as spam',
            cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-sm btn-warning',
                cancelButton: 'btn btn-sm btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ route('admin.contacts.mark-spam', ':id') }}".replace(':id', contactId))
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
                        $('#contactsTable').DataTable().ajax.reload();
                    })
                    .fail(function(xhr) {
                        const error = xhr.responseJSON?.error || 'Failed to mark as spam';
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

    // Mark as not spam function
    function markAsNotSpam(contactId) {
        Swal.fire({
            title: 'Mark as Not Spam?',
            text: 'This will mark the contact message as legitimate.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i data-lucide="shield-check" class="icon-sm me-1"></i>Yes, mark as not spam',
            cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-sm btn-success',
                cancelButton: 'btn btn-sm btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ route('admin.contacts.mark-not-spam', ':id') }}".replace(':id', contactId))
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
                        $('#contactsTable').DataTable().ajax.reload();
                    })
                    .fail(function(xhr) {
                        const error = xhr.responseJSON?.error || 'Failed to mark as not spam';
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

    // Delete contact function
    function deleteContact(contactId) {
        Swal.fire({
            title: 'Delete Contact?',
            text: 'This action cannot be undone. Are you sure you want to delete this contact message?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: '<i data-lucide="trash-2" class="icon-sm me-1"></i>Yes, delete it',
            cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-sm btn-danger',
                cancelButton: 'btn btn-sm btn-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('admin.contacts.destroy', ':id') }}".replace(':id', contactId),
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        Swal.fire({
                            title: 'Deleted!',
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
                        $('#contactsTable').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.error || 'Failed to delete contact';
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
                    }
                });
            }
        });
    }
</script>
@endpush