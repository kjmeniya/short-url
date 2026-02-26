@extends('admin.layout.master')

@section('title', $title ?? 'Notification Details')
@section('description', $description ?? 'View detailed information about this notification')
@section('keywords', $keywords ?? 'notification details, admin notification, system alert')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
        <li class="breadcrumb-item active" aria-current="page">Notification Details</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i data-lucide="bell" class="icon-sm me-2"></i>Notification Details
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i data-lucide="arrow-left" class="icon-sm me-1"></i>
                        Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Main Notification Content -->
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle me-3" style="width: 50px; height: 50px;">
                                    <i data-lucide="{{ $notification->data['icon'] ?? 'bell' }}" class="icon-md text-{{ $notification->data['color'] ?? 'primary' }}"></i>
                                </div>
                                <div>
                                    <h4 class="mb-1 {{ $notification->read_at ? 'fw-normal' : 'fw-bold' }}">
                                        {{ $notification->data['title'] ?? 'Notification' }}
                                    </h4>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-{{ $notification->data['color'] ?? 'primary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $notification->data['type'] ?? 'info')) }}
                                        </span>
                                        @if($notification->read_at)
                                            <span class="badge bg-success">Read</span>
                                        @else
                                            <span class="badge bg-warning">Unread</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-light border-start border-{{ $notification->data['color'] ?? 'primary' }} border-4">
                                <p class="mb-0">{{ $notification->data['message'] ?? 'No message content available.' }}</p>
                            </div>

                            @if(isset($notification->data['url']) && $notification->data['url'])
                            <div class="mt-3">
                                <a href="{{ $notification->data['url'] }}" class="btn btn-sm btn-outline-primary">
                                    <i data-lucide="external-link" class="icon-sm me-1"></i>
                                    View Related Item
                                </a>
                            </div>
                            @endif
                        </div>

                        <!-- Additional Details -->
                        @if(isset($notification->data['action_by']) || isset($notification->data['ip_address']) || isset($notification->data['user_name']))
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i data-lucide="info" class="icon-sm me-2"></i>Additional Details
                                </h6>
                            </div>
                            <div class="card-body">
                                @if(isset($notification->data['action_by']))
                                <div class="row mb-2">
                                    <div class="col-sm-4"><strong>Action By:</strong></div>
                                    <div class="col-sm-8">{{ $notification->data['action_by'] }}</div>
                                </div>
                                @endif
                                @if(isset($notification->data['user_name']))
                                <div class="row mb-2">
                                    <div class="col-sm-4"><strong>Related User:</strong></div>
                                    <div class="col-sm-8">{{ $notification->data['user_name'] }}</div>
                                </div>
                                @endif
                                @if(isset($notification->data['ip_address']))
                                <div class="row mb-2">
                                    <div class="col-sm-4"><strong>IP Address:</strong></div>
                                    <div class="col-sm-8">{{ $notification->data['ip_address'] }}</div>
                                </div>
                                @endif
                                @if(isset($notification->data['changes']))
                                <div class="row mb-2">
                                    <div class="col-sm-4"><strong>Changes:</strong></div>
                                    <div class="col-sm-8">
                                        @foreach($notification->data['changes'] as $key => $value)
                                            <span class="badge bg-info me-1">{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <!-- Quick Actions -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i data-lucide="zap" class="icon-sm me-2"></i>Quick Actions
                                </h6>
                            </div>
                            <div class="card-body">
                                @if(!$notification->read_at)
                                <button type="button" class="btn btn-sm btn-success w-100 mb-2" id="markAsRead" data-id="{{ $notification->id }}">
                                    <i data-lucide="check" class="icon-sm me-1"></i>
                                    Mark as Read
                                </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-danger w-100 mb-2" id="deleteNotification" data-id="{{ $notification->id }}">
                                    <i data-lucide="trash-2" class="icon-sm me-1"></i>
                                    Delete Notification
                                </button>
                            </div>
                        </div>

                        <!-- Notification Information -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i data-lucide="info" class="icon-sm me-2"></i>Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-5"><small class="text-muted">ID:</small></div>
                                    <div class="col-7"><small>{{ substr($notification->id, 0, 8) }}...</small></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5"><small class="text-muted">Status:</small></div>
                                    <div class="col-7">
                                        @if($notification->read_at)
                                            <span class="badge bg-success badge-sm">Read</span>
                                        @else
                                            <span class="badge bg-warning badge-sm">Unread</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5"><small class="text-muted">Type:</small></div>
                                    <div class="col-7"><small>{{ ucfirst(str_replace('_', ' ', $notification->data['type'] ?? 'info')) }}</small></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5"><small class="text-muted">Created:</small></div>
                                    <div class="col-7">
                                        <small>{{ $formattedDates['created_at_date'] }}</small><br>
                                        <small class="text-muted">{{ $formattedDates['created_at_time'] }}</small>
                                    </div>
                                </div>
                                @if($notification->read_at)
                                <div class="row mb-2">
                                    <div class="col-5"><small class="text-muted">Read At:</small></div>
                                    <div class="col-7">
                                        <small>{{ $formattedDates['read_at_date'] }}</small><br>
                                        <small class="text-muted">{{ $formattedDates['read_at_time'] }}</small>
                                    </div>
                                </div>
                                @endif
                                <div class="row">
                                    <div class="col-5"><small class="text-muted">Time Ago:</small></div>
                                    <div class="col-7"><small class="text-primary">{{ $formattedDates['time_ago'] }}</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(count($notification->data) > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">
                                    <i data-lucide="code" class="icon-sm me-2"></i>Raw Notification Data
                                </h6>
                            </div>
                            <div class="card-body">
                                <pre class="bg-light p-3 rounded"><code>{{ json_encode($notification->data, JSON_PRETTY_PRINT) }}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
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
        // Mark as read functionality
        $('#markAsRead').on('click', function() {
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

                        // Update UI
                        $('.notification-card').removeClass('unread').addClass('read');
                        $('.badge.bg-warning').removeClass('bg-warning').addClass('bg-success').text('Read');
                        $('#markAsRead').remove();

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
        $('#deleteNotification').on('click', function() {
            const notificationId = $(this).data('id');

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
                        url: `/admin/notifications/${notificationId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
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
                                }).then(() => {
                                    window.location.href = '{{ route("admin.notifications.index") }}';
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
                                buttonsStyling: false
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush