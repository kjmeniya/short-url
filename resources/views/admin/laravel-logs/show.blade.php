@extends('admin.layout.master')

@section('title', $title ?? 'Laravel Log Details')
@section('description', $description ?? 'View detailed Laravel log information')
@section('keywords', $keywords ?? 'laravel log details, error details, log analysis')

@push('plugin-styles')
<style>
  .log-level-emergency, .log-level-alert, .log-level-critical, .log-level-error {
    color: #dc3545 !important;
  }
  .log-level-warning {
    color: #ffc107 !important;
  }
  .log-level-notice, .log-level-info {
    color: #0d6efd !important;
  }
  .log-level-debug {
    color: #6c757d !important;
  }
  .code-block {
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 1rem;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
    white-space: pre-wrap;
    word-wrap: break-word;
    max-height: 400px;
    overflow-y: auto;
  }
  .dark-mode .code-block {
    background-color: #2d3748;
    border-color: #4a5568;
    color: #e2e8f0;
  }

</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
  <div>
    <nav class="page-breadcrumb mb-0">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.laravel-logs.index') }}">Laravel Logs</a></li>
        <li class="breadcrumb-item active" aria-current="page">Log #{{ $laravelLog->id }}</li>
      </ol>
    </nav>
  </div>
  <div class="d-flex align-items-center flex-wrap text-nowrap gap-2">
    <a href="{{ route('admin.laravel-logs.index') }}" class="btn btn-outline-secondary btn-sm">
      <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back to Logs
    </a>
    @if($laravelLog->user)
      <a href="{{ route('admin.users.show', $laravelLog->user->id) }}" class="btn btn-outline-info btn-sm">
        <i data-lucide="user" class="icon-sm me-1"></i>View User
      </a>
    @endif
  </div>
</div>

<!-- Log Overview -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="card-title mb-0">Log Overview</h6>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="d-flex align-items-center">
              <div class="me-3">
                <i data-lucide="alert-triangle" class="icon-lg log-level-{{ $laravelLog->level }}"></i>
              </div>
              <div>
                <h6 class="mb-1">Level</h6>
                <p class="mb-0">{!! $laravelLog->level_badge !!}</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="d-flex align-items-center">
              <div class="me-3">
                <i data-lucide="radio" class="icon-lg text-info"></i>
              </div>
              <div>
                <h6 class="mb-1">Channel</h6>
                <p class="mb-0">{!! $laravelLog->channel_badge !!}</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="d-flex align-items-center">
              <div class="me-3">
                <i data-lucide="server" class="icon-lg text-success"></i>
              </div>
              <div>
                <h6 class="mb-1">Environment</h6>
                <p class="mb-0">{!! $laravelLog->environment_badge !!}</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="d-flex align-items-center">
              <div class="me-3">
                <i data-lucide="clock" class="icon-lg text-primary"></i>
              </div>
              <div>
                <h6 class="mb-1">Logged At</h6>
                <p class="mb-0">{{ formatUserDateTime($laravelLog->logged_at) }}</p>
                <small class="text-muted">{{ timeAgo($laravelLog->logged_at) }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Log Message -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h6 class="card-title mb-0">Log Message</h6>
      </div>
      <div class="card-body">
        <div class="code-block">{{ $laravelLog->message }}</div>
      </div>
    </div>
  </div>
</div>

<!-- Context and Exception Information -->
<div class="row mb-4">
  @if($laravelLog->formatted_context)
    <div class="col-md-6 mb-4">
      <div class="card">
        <div class="card-header">
          <h6 class="card-title mb-0">Context Data</h6>
        </div>
        <div class="card-body">
          <div class="code-block">{{ $laravelLog->formatted_context }}</div>
        </div>
      </div>
    </div>
  @endif

  @if($laravelLog->stack_trace)
    <div class="col-md-{{ $laravelLog->formatted_context ? '6' : '12' }} mb-4">
      <div class="card">
        <div class="card-header">
          <h6 class="card-title mb-0">Stack Trace</h6>
        </div>
        <div class="card-body">
          <div class="code-block">{{ $laravelLog->stack_trace }}</div>
        </div>
      </div>
    </div>
  @endif
</div>

<!-- Technical Details -->
<div class="row mb-4">
  <div class="col-md-6 mb-4">
    <div class="card">
      <div class="card-header">
        <h6 class="card-title mb-0">Technical Details</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm">
            <tbody>
              <tr>
                <td><strong>Log ID:</strong></td>
                <td>{{ $laravelLog->id }}</td>
              </tr>
              <tr>
                <td><strong>File Path:</strong></td>
                <td>{{ $laravelLog->file_path ?: 'N/A' }}</td>
              </tr>
              <tr>
                <td><strong>Line Number:</strong></td>
                <td>{{ $laravelLog->line_number ?: 'N/A' }}</td>
              </tr>
              <tr>
                <td><strong>Exception Class:</strong></td>
                <td>{{ $laravelLog->exception_class ?: 'N/A' }}</td>
              </tr>
              <tr>
                <td><strong>Request ID:</strong></td>
                <td>{{ $laravelLog->request_id ?: 'N/A' }}</td>
              </tr>
              <tr>
                <td><strong>Log Month:</strong></td>
                <td>{{ $laravelLog->log_month }}</td>
              </tr>
              <tr>
                <td><strong>Log Date:</strong></td>
                <td>{{ $laravelLog->log_date }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-6 mb-4">
    <div class="card">
      <div class="card-header">
        <h6 class="card-title mb-0">Request Information</h6>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm">
            <tbody>
              @if($laravelLog->user)
                <tr>
                  <td><strong>User:</strong></td>
                  <td>
                    <a href="{{ route('admin.users.show', $laravelLog->user->id) }}" class="text-decoration-none">
                      {{ $laravelLog->user->name }}
                    </a>
                    <br><small class="text-muted">{{ $laravelLog->user->email }}</small>
                  </td>
                </tr>
              @elseif($laravelLog->user_id)
                <tr>
                  <td><strong>User ID:</strong></td>
                  <td>{{ $laravelLog->user_id }} <small class="text-muted">(User not found)</small></td>
                </tr>
              @endif
              <tr>
                <td><strong>IP Address:</strong></td>
                <td>{{ $laravelLog->ip_address ?: 'N/A' }}</td>
              </tr>
              <tr>
                <td><strong>URL:</strong></td>
                <td>{{ $laravelLog->url ?: 'N/A' }}</td>
              </tr>
              <tr>
                <td><strong>HTTP Method:</strong></td>
                <td>{{ $laravelLog->method ?: 'N/A' }}</td>
              </tr>
              <tr>
                <td><strong>User Agent:</strong></td>
                <td>{{ $laravelLog->user_agent ? Str::limit($laravelLog->user_agent, 50) : 'N/A' }}</td>
              </tr>
              <tr>
                <td><strong>Created At:</strong></td>
                <td>{{ formatUserDateTime($laravelLog->created_at) }}</td>
              </tr>
              <tr>
                <td><strong>Updated At:</strong></td>
                <td>{{ formatUserDateTime($laravelLog->updated_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Metadata -->
@if($laravelLog->metadata)
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h6 class="card-title mb-0">Additional Metadata</h6>
        </div>
        <div class="card-body">
          <div class="code-block">{{ json_encode($laravelLog->metadata, JSON_PRETTY_PRINT) }}</div>
        </div>
      </div>
    </div>
  </div>
@endif

<!-- Extra Data -->
@if($laravelLog->extra)
  <div class="row mb-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h6 class="card-title mb-0">Extra Data</h6>
        </div>
        <div class="card-body">
          <div class="code-block">{{ $laravelLog->extra }}</div>
        </div>
      </div>
    </div>
  </div>
@endif
@endsection
