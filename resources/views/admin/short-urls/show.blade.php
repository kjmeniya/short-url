@extends('admin.layout.master')

@section('title', $title ?? 'Short URL Details')
@section('description', $description ?? 'View short URL details.')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.short-urls.index') }}">Short URLs</a></li>
        <li class="breadcrumb-item active" aria-current="page">#{{ $shortUrl->code }}</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="card-title mb-0">
                    <i data-lucide="link" class="icon-sm me-2"></i>
                    {{ $shortUrl->title ?: 'Short URL #' . $shortUrl->code }}
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ $shortUrl->short_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i data-lucide="external-link" class="icon-sm me-1"></i>Open
                    </a>
                    <a href="{{ route('admin.short-urls.edit', $shortUrl->id) }}" class="btn btn-sm btn-primary">
                        <i data-lucide="edit" class="icon-sm me-1"></i>Edit
                    </a>
                    <a href="{{ route('admin.short-urls.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Short URL --}}
                <div class="mb-4 p-3 rounded-3 bg-primary bg-opacity-10">
                    <label class="form-label text-muted small">Short URL</label>
                    <div class="input-group">
                        <input type="text" class="form-control fw-semibold" value="{{ $shortUrl->short_url }}" readonly>
                        <button class="btn btn-primary" type="button" id="copyShortUrl" data-url="{{ $shortUrl->short_url }}">
                            <i data-lucide="copy" class="icon-sm me-1"></i>Copy
                        </button>
                    </div>
                </div>

                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted" style="width:30%">Destination URL</th>
                            <td><a href="{{ $shortUrl->original_url }}" target="_blank" class="text-break">{{ $shortUrl->original_url }}</a></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Code</th>
                            <td><code>{{ $shortUrl->code }}</code></td>
                        </tr>
                        @if($shortUrl->custom_alias)
                        <tr>
                            <th class="text-muted">Custom Alias</th>
                            <td><code>{{ $shortUrl->custom_alias }}</code></td>
                        </tr>
                        @endif
                        <tr>
                            <th class="text-muted">Status</th>
                            <td>
                                @php $colors = ['active'=>'success','inactive'=>'secondary','expired'=>'danger']; @endphp
                                <span class="badge bg-{{ $colors[$shortUrl->status] ?? 'secondary' }}">
                                    {{ ucfirst($shortUrl->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Total Clicks</th>
                            <td><strong>{{ number_format($shortUrl->clicks) }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Expires At</th>
                            <td>{{ $shortUrl->expires_at ? $shortUrl->expires_at->format('M d, Y H:i') : '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Password Protected</th>
                            <td>
                                @if($shortUrl->isPasswordProtected())
                                <span class="badge bg-warning text-dark"><i data-lucide="lock" class="icon-xs me-1"></i>Yes</span>
                                @else
                                <span class="text-muted">No</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Created By</th>
                            <td>{{ $shortUrl->creator?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Created At</th>
                            <td>{{ $shortUrl->created_at->format('M d, Y H:i A') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Updated At</th>
                            <td>{{ $shortUrl->updated_at->format('M d, Y H:i A') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Quick Actions --}}
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i data-lucide="zap" class="icon-sm me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.short-urls.edit', $shortUrl->id) }}" class="btn btn-outline-primary btn-sm">
                        <i data-lucide="edit" class="icon-sm me-2"></i>Edit Link
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="copyShortUrlSide" data-url="{{ $shortUrl->short_url }}">
                        <i data-lucide="copy" class="icon-sm me-2"></i>Copy Short URL
                    </button>
                    <a href="{{ $shortUrl->short_url }}" target="_blank" class="btn btn-outline-info btn-sm">
                        <i data-lucide="external-link" class="icon-sm me-2"></i>Open in New Tab
                    </a>
                    <hr class="my-1">
                    <button type="button" class="btn btn-outline-danger btn-sm" id="deleteShortUrl" data-id="{{ $shortUrl->id }}">
                        <i data-lucide="trash-2" class="icon-sm me-2"></i>Delete Link
                    </button>
                </div>
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
    function copyUrl(url) {
        navigator.clipboard.writeText(url).then(function() {
            window.Toast?.fire({
                icon: 'success',
                title: 'Short URL copied!'
            });
        });
    }
    document.getElementById('copyShortUrl')?.addEventListener('click', function() {
        copyUrl(this.dataset.url);
    });
    document.getElementById('copyShortUrlSide')?.addEventListener('click', function() {
        copyUrl(this.dataset.url);
    });

    document.getElementById('deleteShortUrl')?.addEventListener('click', function() {
        const id = this.dataset.id;
        Swal.fire({
            title: 'Delete this link?',
            text: "This action cannot be undone.",
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
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        }).then(result => {
            if (result.isConfirmed) {
                fetch('/admin/short-urls/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        window.location.href = "{{ route('admin.short-urls.index') }}";
                    }
                });
            }
        });
    });
</script>
@endpush