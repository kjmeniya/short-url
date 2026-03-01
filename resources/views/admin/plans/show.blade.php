@extends('admin.layout.master')

@section('title', $title ?? 'Plan Details')
@section('description', $description ?? "View details for {$plan->name} plan.")
@section('keywords', $keywords ?? 'plan details, subscription plan view, plan information')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.plans.index') }}">Plan Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($plan->name, 50) }}</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                    <h6 class="card-title mb-0">Plan Details</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.plans.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i data-lucide="arrow-left" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Back to List</span>
                            <span class="d-sm-none">Back</span>
                        </a>
                        <a href="{{ route('admin.plans.edit', $plan->id) }}" class="btn btn-outline-primary btn-sm">
                            <i data-lucide="edit" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Edit Plan</span>
                            <span class="d-sm-none">Edit</span>
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm delete-plan" data-id="{{ $plan->id }}">
                            <i data-lucide="trash-2" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Delete</span>
                            <span class="d-sm-none">Delete</span>
                        </button>
                    </div>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="row">
                    <div class="col-lg-8">
                        <!-- Plan Content -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Plan Information</h6>
                            </div>
                            <div class="card-body">
                                <h1 class="mb-3">{{ $plan->name }}</h1>
                                <div class="mb-4">
                                    <p class="lead text-muted">{{ $plan->description ?: 'No description provided.' }}</p>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Slug:</label>
                                            <p class="text-muted">{{ $plan->slug }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Price:</label>
                                            <p class="text-muted text-success fs-4 fw-bolder">${{ number_format($plan->price, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Features Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Plan Features</h6>
                            </div>
                            <div class="card-body">
                                @if($plan->features->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Feature Title</th>
                                                <th>Key</th>
                                                <th>Value</th>
                                                <th>Status</th>
                                                <th>Included</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($plan->features as $feature)
                                            <tr>
                                                <td><i data-lucide="check" class="icon-sm text-success me-2"></i> {{ $feature->feature_title ?? '-' }}</td>
                                                <td>{{ $feature->feature_name }}</td>
                                                <td>{{ $feature->feature_value ?? '-' }}</td>
                                                <td>
                                                    @if($feature->status)
                                                    <span class="badge bg-success">Active</span>
                                                    @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($feature->is_include)
                                                    <span class="badge bg-primary">Yes</span>
                                                    @else
                                                    <span class="badge bg-secondary">No</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <p class="text-muted">No features defined for this plan.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Plan Meta Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Status Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status:</label>
                                    <div>
                                        @if($plan->is_active)
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Sort Order:</label>
                                    <p class="text-muted mb-0">{{ $plan->sort_order }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created:</label>
                                    <p class="text-muted mb-0">{{ $plan->created_at->format('M d, Y g:i A') }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Last Updated:</label>
                                    <p class="text-muted mb-0">{{ $plan->updated_at->format('M d, Y g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
    $(document).ready(function() {
        // Delete plan functionality
        $('.delete-plan').on('click', function() {
            const planId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this! All associated subscriptions might be affected.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/plans/${planId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success || response.message) {
                                Swal.fire('Deleted!', response.message || 'Plan deleted successfully.', 'success').then(() => {
                                    window.location.href = '{{ route("admin.plans.index") }}';
                                });
                            } else {
                                Swal.fire('Error!', response.message || 'Something went wrong.', 'error');
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