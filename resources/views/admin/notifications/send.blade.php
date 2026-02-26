@extends('admin.layout.master')

@section('title', $title ?? 'Send Notification')
@section('description', $description ?? 'Send custom notifications to users')
@section('keywords', $keywords ?? 'notifications, alerts, admin notifications, system messages')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('build/plugins/select2/select2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
        <li class="breadcrumb-item active" aria-current="page">Send Notification</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12 col-xl-12 middle-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Send Custom Notification</h6>

                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('admin.notifications.send.post') }}" class="forms-sample" id="sendNotificationForm">
                            @csrf

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title *</label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                                            id="title" name="title" value="{{ old('title') }}" placeholder="Enter notification title"
                                            maxlength="255" data-maxlength="true" required>
                                        @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label for="notification_type" class="form-label">Notification Type *</label>
                                        <select class="form-select select2-notification-type @error('notification_type') is-invalid @enderror" id="notification_type" name="notification_type" required>
                                            <option value="">Select Notification Type</option>
                                            @foreach($notificationTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('notification_type') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('notification_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Choose the type of notification event</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label for="type" class="form-label">Alert Type *</label>
                                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                            <option value="info" {{ old('type', 'info') == 'info' ? 'selected' : '' }}>Info</option>
                                            <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success</option>
                                            <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                                            <option value="error" {{ old('type') == 'error' ? 'selected' : '' }}>Error</option>
                                        </select>
                                        @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Choose alert style for the notification</small>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label for="platform" class="form-label">Platform *</label>
                                        <select class="form-select @error('platform') is-invalid @enderror" id="platform" name="platform" required>
                                            <option value="both" {{ old('platform', 'both') == 'both' ? 'selected' : '' }}>Both (Web & Mobile)</option>
                                            <option value="web" {{ old('platform') == 'web' ? 'selected' : '' }}>Web Only (Admin Panel)</option>
                                            <option value="mobile" {{ old('platform') == 'mobile' ? 'selected' : '' }}>Mobile Only (App)</option>
                                        </select>
                                        @error('platform')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Choose where to send this notification</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Message *</label>
                                <textarea class="form-control @error('message') is-invalid @enderror"
                                    id="message" name="message" rows="4" placeholder="Enter notification message"
                                    maxlength="1000" data-maxlength="true" required>{{ old('message') }}</textarea>
                                @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label for="target_type" class="form-label">Send To *</label>
                                        <select class="form-select @error('target_type') is-invalid @enderror" id="target_type" name="target_type" required>
                                            <option value="all" {{ old('target_type', 'all') == 'all' ? 'selected' : '' }}>All Users</option>
                                            <option value="users" {{ old('target_type') == 'users' ? 'selected' : '' }}>Specific Users</option>
                                            <option value="roles" {{ old('target_type') == 'roles' ? 'selected' : '' }}>Specific Roles</option>
                                        </select>
                                        @error('target_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Choose target recipients</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Specific Users (hidden by default) -->
                            <div class="mb-3" id="users_section" style="display: none;">
                                <label for="user_ids" class="form-label">Select Users *</label>
                                <select class="form-select select2-users @error('user_ids') is-invalid @enderror" id="user_ids" name="user_ids[]" multiple>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, old('user_ids', [])) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('user_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Search and select multiple users</small>
                            </div>

                            <!-- Specific Roles (hidden by default) -->
                            <div class="mb-3" id="roles_section" style="display: none;">
                                <label for="role_ids" class="form-label">Select Roles *</label>
                                <select class="form-select select2-roles @error('role_ids') is-invalid @enderror" id="role_ids" name="role_ids[]" multiple>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ in_array($role->id, old('role_ids', [])) ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('role_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Search and select multiple roles</small>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label for="icon" class="form-label">Icon</label>
                                        <input type="text" class="form-control @error('icon') is-invalid @enderror"
                                            id="icon" name="icon" value="{{ old('icon', 'bell') }}" placeholder="bell"
                                            maxlength="50" data-maxlength="true">
                                        @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Lucide icon name (e.g., bell, alert-circle, check-circle)</small>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="mb-3">
                                        <label for="url" class="form-label">Action URL</label>
                                        <input type="url" class="form-control @error('url') is-invalid @enderror"
                                            id="url" name="url" value="{{ old('url') }}" placeholder="https://example.com"
                                            maxlength="500" data-maxlength="true">
                                        @error('url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">URL to open when notification is clicked</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary btn-sm">
                                    <i data-lucide="x" class="icon-sm me-1"></i>
                                    <span class="d-none d-sm-inline">Cancel</span>
                                    <span class="d-sm-none">Cancel</span>
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm" id="submitBtn">
                                    <i data-lucide="send" class="icon-sm me-1"></i>
                                    <span class="d-none d-sm-inline">Send Notification</span>
                                    <span class="d-sm-none">Send</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/select2/select2.min.js') }}"></script>
<script src="{{ asset('build/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
    $(document).ready(function() {
        // Initialize maxlength
        $('[data-maxlength]').maxlength({
            alwaysShow: true,
            threshold: 10,
            warningClass: "badge bg-success",
            limitReachedClass: "badge bg-danger"
        });

        // Initialize Select2 for notification types
        $('.select2-notification-type').select2({
            placeholder: 'Search notification types...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Initialize Select2 for users
        $('.select2-users').select2({
            placeholder: 'Search and select users...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Initialize Select2 for roles
        $('.select2-roles').select2({
            placeholder: 'Search and select roles...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Show/hide target sections based on selection
        function updateTargetSections() {
            const targetType = $('#target_type').val();

            if (targetType === 'users') {
                $('#users_section').show();
                $('#roles_section').hide();
                $('#user_ids').prop('required', true);
                $('#role_ids').prop('required', false);
            } else if (targetType === 'roles') {
                $('#roles_section').show();
                $('#users_section').hide();
                $('#role_ids').prop('required', true);
                $('#user_ids').prop('required', false);
            } else {
                $('#users_section').hide();
                $('#roles_section').hide();
                $('#user_ids').prop('required', false);
                $('#role_ids').prop('required', false);
            }
        }

        // Initialize on page load
        updateTargetSections();

        // Update on change
        $('#target_type').on('change', updateTargetSections);

        // Handle form submission
        $('#submitBtn').on('click', function(e) {
            e.preventDefault();

            const form = $('#sendNotificationForm');
            const submitBtn = $(this);
            const originalHtml = submitBtn.html();

            // Disable button and show loading state
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sending...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
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
                    }).then(() => {
                        window.location.href = '{{ route("admin.notifications.index") }}';
                    });
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred while sending the notification.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: errorMessage,
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

                    submitBtn.prop('disabled', false).html(originalHtml);
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            });
        });
    });
</script>
@endpush