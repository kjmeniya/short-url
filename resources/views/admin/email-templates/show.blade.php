@extends('admin.layout.master')

@section('title', $title ?? 'Email Template Details')
@section('description', $description ?? 'View email template details and content')
@section('keywords', $keywords ?? 'email template details, template view, template information')

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.email-templates.index') }}">Email Templates</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $emailTemplate->name }}</li>
  </ol>
</nav>

<div class="profile-page tx-13">
  <div class="row">
    <div class="col-12 grid-margin">
      <div class="card">
        <div class="position-relative">
          <figure class="overflow-hidden mb-0 d-flex justify-content-center align-items-center rounded-top"
            style="height: 200px; background: linear-gradient(135deg, #245dac 0%, #1a4a8a 100%);">
            <div class="w-100 text-center">
              <i data-lucide="mail" class="text-white" style="width: 80px; height: 80px; opacity: 0.3;"></i>
              <h1 class="text-white fw-bold mb-0" style="opacity: 0.3; text-transform: uppercase; letter-spacing: 0.2rem;">{{ $emailTemplate->name }}</h1>
            </div>
          </figure>
          <div class="d-flex justify-content-between align-items-center position-absolute top-90 w-100 px-2 px-md-4 mt-n4">
            <div class="d-flex align-items-center">
              <div class="me-3">
                <div class="w-70px h-70px bg-white dark:bg-dark rounded-circle shadow d-flex align-items-center justify-content-center">
                  <i data-lucide="mail" class="icon-lg" style="color: #245dac;"></i>
                </div>
              </div>
              <div>
                <!-- <span class="h4 mb-4 d-block text-white">{{ $emailTemplate->name }}</span> -->
                <!-- <small class="text-muted">Email Template</small> -->
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-center p-3 rounded-bottom">
          <ul class="d-flex align-items-center m-0 p-0">
            <li class="d-flex align-items-center active">
              <i class="me-1 icon-md text-primary" data-lucide="eye"></i>
              <a class="pt-1px d-none d-md-block text-primary" href="#">Template Details</a>
            </li>
            <li class="ms-3 ps-3 border-start d-flex align-items-center">
              <i class="me-1 icon-md" data-lucide="edit"></i>
              <a class="pt-1px d-none d-md-block text-body" href="{{ route('admin.email-templates.edit', $emailTemplate) }}">Edit Template</a>
            </li>
            <li class="ms-3 ps-3 border-start d-flex align-items-center">
              <i class="me-1 icon-md" data-lucide="arrow-left"></i>
              <a class="pt-1px d-none d-md-block text-body" href="{{ route('admin.email-templates.index') }}">Back to List</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row profile-body">
  <!-- left wrapper start -->
  <div class="col-12 col-md-4 col-xl-3 left-wrapper mb-3">
    <div class="card rounded">
      <div class="card-body">

        <div class="d-flex align-items-center justify-content-between mb-2">
          <h6 class="card-title mb-0">Template Information</h6>
        </div>
        <div class="mt-3">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <i data-lucide="tag" class="text-primary me-2 icon-sm"></i>
              <div>
                <p class="fs-12px mb-0">Template Name</p>
                <p class="fs-11px text-secondary">{{ $emailTemplate->name }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-3">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <i data-lucide="layers" class="text-info me-2 icon-sm"></i>
              <div>
                <p class="fs-12px mb-0">Template Type</p>
                <p class="fs-11px text-secondary">
                  @php
                  $types = \App\Models\EmailTemplate::getTypes();
                  $typeName = $types[$emailTemplate->type] ?? ucfirst($emailTemplate->type);
                  $badgeClass = match($emailTemplate->type) {
                  'password_reset' => 'bg-warning',
                  'welcome' => 'bg-success',
                  'notification' => 'bg-info',
                  'reminder' => 'bg-secondary',
                  default => 'bg-primary'
                  };
                  @endphp
                  <span class="badge {{ $badgeClass }}">{{ $typeName }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-3">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <i data-lucide="toggle-{{ $emailTemplate->is_active ? 'right' : 'left' }}" class="text-{{ $emailTemplate->is_active ? 'success' : 'danger' }} me-2  icon-sm"></i>
              <div>
                <p class="fs-12px mb-0">Status</p>
                <p class="fs-11px text-secondary">
                  @if($emailTemplate->is_active)
                  <span class="badge bg-success">Active</span>
                  @else
                  <span class="badge bg-danger">Inactive</span>
                  @endif
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-3">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <i data-lucide="user" class="text-warning me-2 icon-sm"></i>
              <div>
                <p class="fs-12px mb-0">Created By</p>
                <p class="fs-11px text-secondary">{{ $emailTemplate->creator ? $emailTemplate->creator->name : 'N/A' }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-3">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <i data-lucide="calendar" class="text-success me-2 icon-sm"></i>
              <div>
                <p class="fs-12px mb-0">Created Date</p>
                <p class="fs-11px text-secondary">{{ formatUserDateTime($emailTemplate->created_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-3">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <i data-lucide="clock" class="text-secondary me-2 icon-sm"></i>
              <div>
                <p class="fs-12px mb-0">Last Updated</p>
                <p class="fs-11px text-secondary">{{ formatUserDateTime($emailTemplate->updated_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        @if($emailTemplate->updater && $emailTemplate->updater->id !== $emailTemplate->creator?->id)
        <div class="mt-3">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <i data-lucide="user-check" class="text-info me-2 icon-sm"></i>
              <div>
                <p class="fs-12px mb-0">Updated By</p>
                <p class="fs-11px text-secondary">{{ $emailTemplate->updater->name }}</p>
              </div>
            </div>
          </div>
        </div>
        @endif

        @if($emailTemplate->variables && count($emailTemplate->variables) > 0)
        <div class="mt-3">
          <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center">
              <i data-lucide="code" class="text-purple me-2 icon-sm"></i>
              <div>
                <p class="fs-12px mb-0">Available Variables</p>
                <div class="d-flex flex-wrap gap-1 mt-1">
                  @foreach($emailTemplate->variables as $variable)
                  <span class="badge bg-light text-dark">{!! $variable !!}</span>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
  <!-- left wrapper end -->

  <!-- middle wrapper start -->
  <div class="col-12 col-md-8 col-xl-9 middle-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card rounded">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <div class="d-flex align-items-center">
                <i data-lucide="code" class="text-warning me-2 icon-sm"></i>
                <h6 class="card-title mb-0">Raw HTML Code</h6>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyToClipboard()">
                <i data-lucide="copy" class="icon-sm me-1"></i>
                Copy Code
              </button>
            </div>
            <div class="row">
              <div class="col-12">
                <div class="position-relative">
                  <pre class="bg-dark text-light p-3 rounded mb-0" style="max-height: 300px; overflow-y: auto; font-size: 12px;"><code id="rawHtml">{{ $emailTemplate->body }}</code></pre>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-12 grid-margin">
        <div class="card rounded">
          <div class="card-body">
            <h6 class="card-title">Template Content</h6>
            <div class="row">
              <div class="col-12 mb-4">
                <div class="d-flex align-items-center mb-2">
                  <i data-lucide="type" class="text-primary me-2 icon-sm"></i>
                  <h6 class="mb-0">Email Subject</h6>
                </div>
                <div class="p-3 bg-light border rounded">
                  <p class="mb-0">{{ $emailTemplate->subject }}</p>
                </div>
              </div>

              <div class="col-12 mb-4">
                <div class="d-flex align-items-center mb-2">
                  <i data-lucide="file-text" class="text-success me-2 icon-sm"></i>
                  <h6 class="mb-0">Email Body Preview</h6>
                </div>
                <div class="p-3 bg-light border rounded" style="max-height: 400px; overflow-y: auto;">
                  <div class="email-content">
                    {!! view('emails.custom-template', ['content' => $emailTemplate->body, 'subject' => $emailTemplate->subject])->render() !!}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- middle wrapper end -->
</div>
@endsection

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@push('plugin-scripts')
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
  function copyToClipboard() {
    const emailContent = document.querySelector('.email-content')?.innerHTML;
    if (!emailContent) {
      if (typeof AdminNotifications !== 'undefined') {
        AdminNotifications.error('Email content not found!');
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Error!',
          text: 'Email content not found!',
          timer: 3000,
          showConfirmButton: false,
          didOpen: () => {
            if (typeof lucide !== 'undefined') {
              lucide.createIcons();
            }
          }
        });
      }
      return;
    }

    // Use navigator.clipboard if available
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(emailContent)
        .then(() => {
          if (typeof AdminNotifications !== 'undefined') {
            AdminNotifications.success('Email HTML copied to clipboard!');
          } else {
            Swal.fire({
              icon: 'success',
              title: 'Copied!',
              text: 'Email HTML copied to clipboard!',
              timer: 2000,
              showConfirmButton: false,
              position: 'top-end',
              toast: true
            });
          }
        })
        .catch(err => {
          console.error('Clipboard copy failed', err);
          fallbackCopy(emailContent);
        });
    } else {
      // Fallback if navigator.clipboard is unavailable
      fallbackCopy(emailContent);
    }
  }

  function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed'; // Prevent scrolling to bottom
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
      const successful = document.execCommand('copy');
      if (successful) {
        if (typeof AdminNotifications !== 'undefined') {
          AdminNotifications.success('Email HTML copied to clipboard!');
        } else {
          Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'Email HTML copied to clipboard!',
            timer: 2000,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
          });
        }
      } else {
        if (typeof AdminNotifications !== 'undefined') {
          AdminNotifications.error('Failed to copy to clipboard');
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Failed!',
            text: 'Failed to copy to clipboard',
            timer: 3000,
            showConfirmButton: false
          });
        }
      }
    } catch (err) {
      if (typeof AdminNotifications !== 'undefined') {
        AdminNotifications.error('Failed to copy to clipboard');
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Failed!',
          text: 'Failed to copy to clipboard',
          timer: 3000,
          showConfirmButton: false
        });
      }
      console.error('Fallback copy failed', err);
    }
    document.body.removeChild(textarea);
  }
</script>
@endpush