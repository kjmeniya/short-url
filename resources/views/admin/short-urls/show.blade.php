@extends('admin.layout.master')

@section('title', $title ?? 'Short URL Details')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')

<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.short-urls.index') }}">Short URLs</a></li>
        <li class="breadcrumb-item active">#{{ $shortUrl->code }}</li>
    </ol>
</nav>

{{-- Header --}}
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-4">
    <div>
        <h5 class="mb-1">
            <i data-lucide="link" class="icon-sm me-2 text-primary"></i>
            {{ $shortUrl->title ?: 'Short URL #' . $shortUrl->code }}
        </h5>
        <a href="{{ $shortUrl->short_url }}" target="_blank" class="text-primary fw-semibold small">
            {{ $shortUrl->short_url }}
        </a>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.short-urls.analytics', $shortUrl->id) }}" class="btn btn-sm btn-outline-info">
            <i data-lucide="bar-chart-3" class="icon-sm me-1"></i>Analytics
        </a>
        <a href="{{ route('admin.short-urls.edit', $shortUrl->id) }}" class="btn btn-sm btn-outline-primary">
            <i data-lucide="edit" class="icon-sm me-1"></i>Edit
        </a>
        <button type="button" id="deleteBtn" data-id="{{ $shortUrl->id }}" class="btn btn-sm btn-outline-danger">
            <i data-lucide="trash-2" class="icon-sm me-1"></i>Delete
        </button>
        <a href="{{ route('admin.short-urls.index') }}" class="btn btn-sm btn-outline-secondary">
            <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back
        </a>
    </div>
</div>

<div class="row g-3">

    {{-- Short URL card --}}
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0"><i data-lucide="link-2" class="icon-sm me-2"></i>Short URL</h6>
            </div>
            <div class="card-body">

                {{-- Copy row --}}
                <div class="input-group mb-4">
                    <input type="text" id="shortUrlInput" class="form-control form-control-sm" value="{{ $shortUrl->short_url }}" readonly>
                    <button class="btn btn-outline-secondary btn-sm" id="copyBtn" data-url="{{ $shortUrl->short_url }}" title="Copy">
                        <i data-lucide="copy" class="icon-sm"></i>
                    </button>
                    <a href="{{ $shortUrl->short_url }}" target="_blank" class="btn btn-outline-primary btn-sm" title="Open">
                        <i data-lucide="external-link" class="icon-sm"></i>
                    </a>
                </div>

                {{-- Details table --}}
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted ps-0" style="width:38%">Status</th>
                            <td>
                                @php $sc = ['active'=>'success','inactive'=>'secondary','expired'=>'danger']; @endphp
                                <span class="badge bg-{{ $sc[$shortUrl->status] ?? 'secondary' }}">
                                    {{ ucfirst($shortUrl->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-0">Code</th>
                            <td><code>{{ $shortUrl->code }}</code></td>
                        </tr>
                        @if($shortUrl->custom_alias)
                        <tr>
                            <th class="text-muted ps-0">Custom Alias</th>
                            <td><code>{{ $shortUrl->custom_alias }}</code></td>
                        </tr>
                        @endif
                        <tr>
                            <th class="text-muted ps-0">Total Clicks</th>
                            <td><span class="fw-semibold">{{ number_format($shortUrl->clicks) }}</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-0">Expires</th>
                            <td>{{ $shortUrl->expires_at?->format('M d, Y H:i') ?? 'Never' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-0">Password</th>
                            <td>
                                @if($shortUrl->isPasswordProtected())
                                <span class="text-warning"><i data-lucide="lock" class="icon-xs me-1"></i>Protected</span>
                                @else
                                <span class="text-muted">None</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-0">Created By</th>
                            <td>{{ $shortUrl->creator?->name ?? 'Guest' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-0">Created At</th>
                            <td>{{ $shortUrl->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted ps-0">Updated At</th>
                            <td>{{ $shortUrl->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Destination URL card --}}
    <div class="col-12 col-lg-5">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0"><i data-lucide="globe" class="icon-sm me-2"></i>Destination URL</h6>
            </div>
            <div class="card-body">
                <p class="text-break small mb-2">
                    <a href="{{ $shortUrl->original_url }}" target="_blank" rel="noopener">
                        {{ $shortUrl->original_url }}
                    </a>
                </p>
                <a href="{{ $shortUrl->original_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary w-100">
                    <i data-lucide="external-link" class="icon-sm me-1"></i>Open Destination
                </a>
            </div>
        </div>

        {{-- Quick Analytics preview --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><i data-lucide="bar-chart-2" class="icon-sm me-2"></i>Analytics</h6>
                <a href="{{ route('admin.short-urls.analytics', $shortUrl->id) }}" class="btn btn-link btn-sm p-0">
                    View Full →
                </a>
            </div>
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted small">Total clicks</span>
                    <span class="fw-semibold">{{ number_format($shortUrl->clicks) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted small">Status</span>
                    <span class="badge bg-{{ $sc[$shortUrl->status] ?? 'secondary' }} bg-opacity-20 fw-medium">
                        {{ ucfirst($shortUrl->status) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted small">Created</span>
                    <span>{{ $shortUrl->created_at->diffForHumans() }}</span>
                </div>
                <a href="{{ route('admin.short-urls.analytics', $shortUrl->id) }}"
                    class="btn btn-sm btn-outline-info w-100 mt-2">
                    <i data-lucide="bar-chart-3" class="icon-sm me-1"></i>Open Analytics Dashboard
                </a>
            </div>
        </div>
    </div>

</div>

@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // Copy short URL
        document.getElementById('copyBtn')?.addEventListener('click', function() {
            var url = this.dataset.url;
            navigator.clipboard.writeText(url).then(function() {
                if (window.Toast) {
                    window.Toast.fire({
                        icon: 'success',
                        title: 'Short URL copied!'
                    });
                }
            });
        });

        // Delete
        document.getElementById('deleteBtn')?.addEventListener('click', function() {
            var id = this.dataset.id;
            Swal.fire({
                title: 'Delete this short URL?',
                text: 'All analytics data will also be permanently deleted.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete!',
                customClass: {
                    confirmButton: 'btn btn-sm btn-danger me-2',
                    cancelButton: 'btn btn-sm btn-secondary',
                },
                buttonsStyling: false,
            }).then(function(result) {
                if (result.isConfirmed) {
                    fetch('/admin/short-urls/' + id, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                        }).then(function(r) {
                            return r.json();
                        })
                        .then(function(data) {
                            if (data.success) {
                                window.location.href = '{{ route("admin.short-urls.index") }}';
                            }
                        });
                }
            });
        });
    });
</script>
@endpush