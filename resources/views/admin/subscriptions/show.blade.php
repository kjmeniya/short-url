@extends('admin.layout.master')

@section('title', $title ?? 'Subscription Details')
@section('description', $description ?? "View details for {$subscription->user->name}'s subscription.")
@section('keywords', $keywords ?? 'subscription details, user subscription, billing information')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.subscriptions.index') }}">Subscription Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($subscription->user->name, 50) }}</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                    <h6 class="card-title mb-0">Subscription Details</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i data-lucide="arrow-left" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Back to List</span>
                            <span class="d-sm-none">Back</span>
                        </a>
                        @if($subscription->status === 'active')
                        <form action="{{ route('admin.subscriptions.destroy', $subscription->id) }}" method="POST" class="d-inline subscription-cancel-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger btn-sm cancel-subscription">
                                <i data-lucide="x-circle" class="icon-sm me-1"></i>
                                <span class="d-none d-sm-inline">Cancel Subscription</span>
                                <span class="d-sm-none">Cancel</span>
                            </button>
                        </form>
                        @endif
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
                        <!-- User Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">User Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Name:</label>
                                            <p class="text-muted">{{ $subscription->user->name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Role:</label>
                                            <p class="text-muted">{{ $subscription->user->getRoleNames()->first() ?? 'User' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Email:</label>
                                            <p class="text-muted">
                                                <a href="mailto:{{ $subscription->user->email ?? '' }}" class="text-primary text-decoration-none">
                                                    {{ $subscription->user->email ?? 'N/A' }}
                                                </a>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Member Since:</label>
                                            <p class="text-muted">{{ optional($subscription->user->created_at)->format('M d, Y') ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Plan Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Plan Specifications</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Plan Name:</label>
                                            <p class="text-muted">
                                                <a href="{{ route('admin.plans.show', $subscription->plan_id) }}" class="text-primary fw-bolder text-decoration-none">
                                                    {{ $subscription->plan->name ?? 'N/A' }}
                                                </a>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Plan Price:</label>
                                            <p class="text-muted text-success fw-bolder">${{ number_format(optional($subscription->plan)->price, 2) ?? '0.00' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Billing Gateway:</label>
                                            <p class="text-muted">Internal (No Active Gateway)</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Plan Details:</label>
                                            <p class="text-muted mb-0">
                                                <a href="{{ route('admin.plans.show', $subscription->plan_id) }}" class="btn btn-xs btn-outline-info">
                                                    <i data-lucide="eye" class="icon-xs me-1"></i>View Plan Features
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Subscription Details -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Subscription Timeline</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status:</label>
                                    <div>
                                        @if($subscription->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-danger">Cancelled / Expired</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Start Date:</label>
                                    <p class="text-muted mb-0">{{ optional($subscription->starts_at)->format('M d, Y g:i A') ?? 'N/A' }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">End Date:</label>
                                    <p class="text-muted mb-0">
                                        @if($subscription->ends_at)
                                        <span class="text-danger fw-bold">{{ $subscription->ends_at->format('M d, Y g:i A') }}</span>
                                        @else
                                        <span class="text-success"><i data-lucide="infinity" class="icon-sm"></i> Lifetime / Indefinite</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created At:</label>
                                    <p class="text-muted mb-0">{{ $subscription->created_at->format('M d, Y g:i A') }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Last Updated:</label>
                                    <p class="text-muted mb-0">{{ $subscription->updated_at->format('M d, Y g:i A') }}</p>
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
        // Cancel subscription functionality
        $('.cancel-subscription').on('click', function(e) {
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
                                Swal.fire('Cancelled!', response.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Something went wrong while cancelling the subscription.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush