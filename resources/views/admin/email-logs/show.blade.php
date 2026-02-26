@extends('admin.layout.master')

@section('title', $title ?? 'Email Log Details')
@section('description', $description ?? 'View detailed information about email log including content, delivery status, and metadata.')
@section('keywords', $keywords ?? 'email log details, email tracking, email delivery')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
  .info-card {
    border-left: 4px solid #0d6efd;
  }

  .metadata-card {
    background-color: var(--bs-gray-100);
  }

  .email-content {
    border: 1px solid var(--bs-border-color);
    border-radius: 0.375rem;
    max-height: 500px;
    overflow-y: auto;
  }

  .timeline {
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
    <li class="breadcrumb-item"><a href="{{ route('admin.email-logs.index') }}">Email Logs</a></li>
    <li class="breadcrumb-item active" aria-current="page">Email Details</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-8">
    <!-- Email Information -->
    <div class="card info-card mb-4">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <h5 class="card-title mb-0">
            <i data-lucide="mail" class="icon-sm me-2"></i>Email Information
          </h5>
          <div class="d-flex gap-2">
            @if($emailLog->body)
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="previewEmail()">
              <i data-lucide="eye" class="icon-sm me-1"></i>Preview
            </button>
            @endif
            @if($emailLog->status === 'failed')
            <button type="button" class="btn btn-outline-warning btn-sm" onclick="retryEmail()">
              <i data-lucide="refresh-cw" class="icon-sm me-1"></i>Retry
            </button>
            @endif
            <a href="{{ route('admin.email-logs.index') }}" class="btn btn-outline-secondary btn-sm">
              <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back
            </a>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Subject:</label>
            <p class="mb-0">{{ $emailLog->subject }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Status:</label>
            <p class="mb-0">{!! $emailLog->status_badge !!}</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Type:</label>
            <p class="mb-0">{!! $emailLog->type_badge !!}</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Template:</label>
            <p class="mb-0">
              @if($emailLog->emailTemplate)
              <a href="{{ route('admin.email-templates.show', $emailLog->emailTemplate->id) }}" class="text-decoration-none">
                {{ $emailLog->emailTemplate->name }}
              </a>
              @else
              <span class="text-muted">No template used</span>
              @endif
            </p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Recipient:</label>
            <p class="mb-0">
              @if($emailLog->recipient_name)
              <strong>{{ $emailLog->recipient_name }}</strong><br>
              @endif
              <a href="mailto:{{ $emailLog->recipient_email }}" class="text-decoration-none">
                {{ $emailLog->recipient_email }}
              </a>
            </p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Sender:</label>
            <p class="mb-0">
              @if($emailLog->user)
              <strong>{{ $emailLog->user->name }}</strong> ({{ $emailLog->user->email }})<br>
              <small class="text-muted">Sent via: {{ $emailLog->sender_email }}</small>
              @else
              @if($emailLog->sender_name)
              <strong>{{ $emailLog->sender_name }}</strong><br>
              @endif
              {{ $emailLog->sender_email }}
              @endif
            </p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Mailer:</label>
            <p class="mb-0">{{ ucfirst($emailLog->mailer ?? 'Unknown') }}</p>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">Message ID:</label>
            <p class="mb-0">
              @if($emailLog->message_id)
              <code>{{ $emailLog->message_id }}</code>
              @else
              <span class="text-muted">Not available</span>
              @endif
            </p>
          </div>
        </div>

        @if($emailLog->error_message)
        <div class="alert alert-danger">
          <h6 class="alert-heading">
            <i data-lucide="alert-circle" class="icon-sm me-2"></i>Error Message
          </h6>
          <p class="mb-0">{{ $emailLog->error_message }}</p>
        </div>
        @endif
      </div>
    </div>

    <!-- Email Content -->
    @if($emailLog->body)
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">
          <i data-lucide="file-text" class="icon-sm me-2"></i>Email Content
        </h5>
        <div class="email-content p-3">
          @include('emails.custom-template', ['content' => $emailLog->body, 'subject' => $emailLog->subject])
        </div>
      </div>
    </div>
    @endif

    <!-- Metadata -->
    @if($emailLog->metadata && count($emailLog->metadata) > 0)
    <div class="card metadata-card">
      <div class="card-body">
        <h5 class="card-title">
          <i data-lucide="info" class="icon-sm me-2"></i>Metadata
        </h5>
        <div class="row">
          @foreach($emailLog->metadata as $key => $value)
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
          <i data-lucide="clock" class="icon-sm me-2"></i>Timeline
        </h5>

        <div class="timeline p-3">
          <div class="timeline-item">
            <div class="d-flex justify-content-between">
              <strong>Created</strong>
              <small class="text-muted">{{ formatUserDateTime($emailLog->created_at) }}</small>
            </div>
            <p class="mb-0 small text-muted">Email log entry created</p>
          </div>

          @if($emailLog->sent_at)
          <div class="timeline-item">
            <div class="d-flex justify-content-between">
              <strong>Sent</strong>
              <small class="text-muted">{{ formatUserDateTime($emailLog->sent_at) }}</small>
            </div>
            <p class="mb-0 small text-muted">Email sent successfully</p>
          </div>
          @endif

          @if($emailLog->delivered_at)
          <div class="timeline-item">
            <div class="d-flex justify-content-between">
              <strong>Delivered</strong>
              <small class="text-muted">{{ formatUserDateTime($emailLog->delivered_at) }}</small>
            </div>
            <p class="mb-0 small text-muted">Email delivered to recipient</p>
          </div>
          @endif

          @if($emailLog->opened_at)
          <div class="timeline-item">
            <div class="d-flex justify-content-between">
              <strong>Opened</strong>
              <small class="text-muted">{{ formatUserDateTime($emailLog->opened_at) }}</small>
            </div>
            <p class="mb-0 small text-muted">Email opened by recipient</p>
          </div>
          @endif

          @if($emailLog->clicked_at)
          <div class="timeline-item">
            <div class="d-flex justify-content-between">
              <strong>Clicked</strong>
              <small class="text-muted">{{ formatUserDateTime($emailLog->clicked_at) }}</small>
            </div>
            <p class="mb-0 small text-muted">Links in email clicked</p>
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
          <label class="form-label fw-bold">IP Address:</label>
          <p class="mb-0">{{ $emailLog->ip_address ?? 'Not recorded' }}</p>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">User Agent:</label>
          <p class="mb-0 small">{{ $emailLog->user_agent ?? 'Not recorded' }}</p>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Created:</label>
          <p class="mb-0">{{ formatUserDateTime($emailLog->created_at) }}</p>
        </div>

        <div class="mb-0">
          <label class="form-label fw-bold">Last Updated:</label>
          <p class="mb-0">{{ formatUserDateTime($emailLog->updated_at) }}</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Email Preview Modal -->
<div class="modal fade" id="emailPreviewModal" tabindex="-1" aria-labelledby="emailPreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="emailPreviewModalLabel">
          <i data-lucide="mail" class="icon-sm me-2"></i>Email Preview
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-6">
            <strong>To:</strong> {{ $emailLog->recipient_email }}
          </div>
          <div class="col-md-6">
            <strong>From:</strong> {{ $emailLog->sender_email }}
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-12">
            <strong>Subject:</strong> {{ $emailLog->subject }}
          </div>
        </div>
        <div class="row">
          <div class="col-12">
            <div class="border rounded p-3" style="max-height: 500px; overflow-y: auto;">
              @include('emails.custom-template', ['content' => $emailLog->body, 'subject' => $emailLog->subject])
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
  function previewEmail() {
    $('#emailPreviewModal').modal('show');
  }

  function retryEmail() {
    Swal.fire({
      title: 'Retry Email?',
      text: 'This will attempt to resend the failed email.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: '<i data-lucide="refresh-cw" class="icon-sm me-1"></i>Yes, retry',
      cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
      customClass: {
        confirmButton: 'btn btn-sm btn-primary me-2',
        cancelButton: 'btn btn-sm btn-secondary'
      },
      didOpen: () => {
        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }
      },
      buttonsStyling: false
    }).then((result) => {
      if (result.isConfirmed) {
        $.post("{{ route('admin.email-logs.retry', $emailLog->id) }}", {
            _token: '{{ csrf_token() }}'
          })
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
            const error = xhr.responseJSON?.error || 'Failed to retry email';
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