@extends('admin.layout.master')

@section('title', $title ?? 'Login Log Details')
@section('description', $description ?? 'View detailed information about login attempt including device, location, and security details.')
@section('keywords', $keywords ?? 'login log details, user activity, security monitoring')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
  .info-card {
    border-left: 4px solid #0d6efd;
  }
  
  .metadata-card {
    background-color: var(--bs-gray-100);
  }
  
  .timeline{
    max-width: 100% !important;
  }
  
  .timeline-item {
    position: relative;
    padding-left: 2rem;
    margin-bottom: 1rem;
  }
  
  .timeline-item::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0.5rem;
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 50%;
    background-color: var(--bs-primary);
  }
  
  .timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 0.8125rem;
    top: 1.25rem;
    width: 2px;
    height: calc(100% + 0.5rem);
    background-color: var(--bs-border-color);
  }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.login-logs.index') }}">Login Logs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Login Details</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-8">
    <!-- Login Information -->
    <div class="card info-card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <h5 class="card-title mb-0">
            <i data-lucide="activity" class="icon-sm me-2"></i>Login Information
          </h5>
          <div class="d-flex gap-2">
            @if($loginLog->is_suspicious)
            <button type="button" class="btn btn-outline-warning btn-sm" onclick="markAsSafe()">
              <i data-lucide="shield-check" class="icon-sm me-1"></i>Mark as Safe
            </button>
            @endif
            @if($loginLog->user)
            <a href="{{ route('admin.users.show', $loginLog->user->id) }}" class="btn btn-outline-info btn-sm">
              <i data-lucide="user" class="icon-sm me-1"></i>View User
            </a>
            @endif
            <a href="{{ route('admin.login-logs.index') }}" class="btn btn-outline-secondary btn-sm">
              <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back
            </a>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">User:</label>
            <p class="mb-0">
              @if($loginLog->user)
                <strong>{{ $loginLog->user->name }}</strong><br>
                <small class="text-muted">{{ $loginLog->email }}</small>
              @else
                <strong>{{ $loginLog->name ?: 'Unknown User' }}</strong><br>
                <small class="text-muted">{{ $loginLog->email }}</small>
              @endif
            </p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Status:</label>
            <p class="mb-0">{!! $loginLog->status_badge !!}</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Type:</label>
            <p class="mb-0">{!! $loginLog->type_badge !!}</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">IP Address:</label>
            <p class="mb-0">{{ $loginLog->ip_address }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Device:</label>
            <p class="mb-0">{{ $loginLog->device_info }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Location:</label>
            <p class="mb-0">{{ $loginLog->location_summary }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Session Duration:</label>
            <p class="mb-0">{{ $loginLog->session_duration_human }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Suspicious Activity:</label>
            <p class="mb-0">
              @if($loginLog->is_suspicious)
                <span class="badge bg-warning">Yes</span>
              @else
                <span class="badge bg-success">No</span>
              @endif
            </p>
          </div>
        </div>

        @if($loginLog->failure_reason)
        <div class="alert alert-danger">
          <h6 class="alert-heading">
            <i data-lucide="alert-circle" class="icon-sm me-2"></i>Failure Reason
          </h6>
          <p class="mb-0">{{ $loginLog->failure_reason }}</p>
        </div>
        @endif
      </div>
    </div>

    <!-- User Agent Details -->
    @if($loginLog->user_agent)
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">
          <i data-lucide="monitor" class="icon-sm me-2"></i>User Agent Details
        </h5>
        <div class="row">
          <div class="col-md-4 mb-2">
            <strong>Browser:</strong> {{ $loginLog->browser ?: 'Unknown' }}
          </div>
          <div class="col-md-4 mb-2">
            <strong>Platform:</strong> {{ $loginLog->platform ?: 'Unknown' }}
          </div>
          <div class="col-md-4 mb-2">
            <strong>Device Type:</strong> {{ $loginLog->device_type ?: 'Unknown' }}
          </div>
        </div>
        <div class="mt-3">
          <strong>Full User Agent:</strong>
          <p class="small text-muted mb-0">{{ $loginLog->user_agent }}</p>
        </div>
      </div>
    </div>
    @endif

    <!-- Metadata -->
    @if($loginLog->metadata && count($loginLog->metadata) > 0)
    <div class="card metadata-card">
      <div class="card-body">
        <h5 class="card-title">
          <i data-lucide="info" class="icon-sm me-2"></i>Additional Information
        </h5>
        <div class="row">
          @foreach($loginLog->metadata as $key => $value)
          <div class="col-md-6 mb-2">
            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
            @if(is_array($value))
              <pre class="small">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
            @else
              {{ $value }}
            @endif
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif
  </div>

  <div class="col-md-4">
    <!-- Timeline -->
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">
          <i data-lucide="clock" class="icon-sm me-2"></i>Activity Timeline
        </h5>
        
        <div class="timeline p-3">
          <div class="timeline-item">
            <div class="d-flex justify-content-between">
              <strong>Login Attempt</strong>
              <small class="text-muted">{{ formatUserDateTime($loginLog->created_at) }}</small>
            </div>
            <p class="mb-0 small text-muted">Login attempt recorded</p>
          </div>

          @if($loginLog->login_at)
          <div class="timeline-item">
            <div class="d-flex justify-content-between">
              <strong>Login Successful</strong>
              <small class="text-muted">{{ formatUserDateTime($loginLog->login_at) }}</small>
            </div>
            <p class="mb-0 small text-muted">User successfully logged in</p>
          </div>
          @endif

          @if($loginLog->logout_at)
          <div class="timeline-item">
            <div class="d-flex justify-content-between">
              <strong>Logout</strong>
              <small class="text-muted">{{ formatUserDateTime($loginLog->logout_at) }}</small>
            </div>
            <p class="mb-0 small text-muted">User logged out</p>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Technical Details -->
    <div class="card mt-4">
      <div class="card-body">
        <h5 class="card-title">
          <i data-lucide="settings" class="icon-sm me-2"></i>Technical Details
        </h5>
        
        <div class="mb-3">
          <label class="form-label fw-bold">Session ID:</label>
          <p class="mb-0 small">{{ $loginLog->session_id ?: 'Not recorded' }}</p>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Created:</label>
          <p class="mb-0">{{ formatUserDateTime($loginLog->created_at) }}</p>
        </div>

        <div class="mb-0">
          <label class="form-label fw-bold">Last Updated:</label>
          <p class="mb-0">{{ formatUserDateTime($loginLog->updated_at) }}</p>
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
function markAsSafe() {
    Swal.fire({
        title: 'Mark as Safe?',
        text: 'This will mark the login attempt as safe and remove the suspicious flag.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="shield-check" class="icon-sm me-1"></i>Yes, mark as safe',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
            confirmButton: 'btn btn-sm btn-success',
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
            $.post("{{ route('admin.login-logs.mark-safe', $loginLog->id) }}")
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
                    }).then(() => {
                        location.reload();
                    });
                })
                .fail(function(xhr) {
                    const error = xhr.responseJSON?.error || 'Failed to mark as safe';
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
</script>
@endpush
