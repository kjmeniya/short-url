@extends('admin.layout.master')

@section('title', $title ?? 'Edit Short URL')
@section('description', $description ?? 'Edit a shortened URL.')

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.short-urls.index') }}">Short URLs</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.short-urls.show', $shortUrl->id) }}">#{{ $shortUrl->code }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-8 col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i data-lucide="edit" class="icon-sm me-2"></i>Edit Short URL &mdash; <code>{{ $shortUrl->code }}</code>
                </h6>
                <a href="{{ route('admin.short-urls.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back
                </a>
            </div>
            <div class="card-body">

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <form action="{{ route('admin.short-urls.update', $shortUrl->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Short URL (read-only) --}}
                    <div class="mb-3">
                        <label class="form-label">Short URL</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $shortUrl->short_url }}" readonly>
                            <button class="btn btn-outline-secondary" type="button" id="copyShortUrl"
                                data-url="{{ $shortUrl->short_url }}">
                                <i data-lucide="copy" class="icon-sm"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Destination URL --}}
                    <div class="mb-3">
                        <label for="original_url" class="form-label">Destination URL <span class="text-danger">*</span></label>
                        <input type="url" id="original_url" name="original_url"
                            class="form-control @error('original_url') is-invalid @enderror"
                            value="{{ old('original_url', $shortUrl->original_url) }}" required>
                        @error('original_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-muted">(optional)</span></label>
                        <input type="text" id="title" name="title"
                            class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title', $shortUrl->title) }}" placeholder="Friendly name">
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Custom Alias --}}
                    <div class="mb-3">
                        <label for="custom_alias" class="form-label">Custom Alias <span class="text-muted">(optional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text text-muted">{{ url('/') }}/</span>
                            <input type="text" id="custom_alias" name="custom_alias"
                                class="form-control @error('custom_alias') is-invalid @enderror"
                                value="{{ old('custom_alias', $shortUrl->custom_alias) }}" placeholder="my-link">
                        </div>
                        <div class="form-text">Auto-generated code: <strong>{{ $shortUrl->code }}</strong></div>
                        @error('custom_alias')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                            @foreach(\App\Models\ShortUrl::getStatusOptions() as $value => $label)
                            <option value="{{ $value }}" {{ old('status', $shortUrl->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Expiry --}}
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Expiry Date <span class="text-muted">(optional)</span></label>
                        <input type="datetime-local" id="expires_at" name="expires_at"
                            class="form-control @error('expires_at') is-invalid @enderror"
                            value="{{ old('expires_at', $shortUrl->expires_at ? $shortUrl->expires_at->format('Y-m-d\TH:i') : '') }}">
                        @error('expires_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            Change Password
                            @if($shortUrl->password)
                            <span class="badge bg-warning text-dark ms-1">Password Set</span>
                            @endif
                        </label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Leave blank to keep existing" autocomplete="new-password">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i data-lucide="save" class="icon-sm me-1"></i>Update Short URL
                        </button>
                        <a href="{{ route('admin.short-urls.show', $shortUrl->id) }}" class="btn btn-outline-secondary btn-sm">
                            <i data-lucide="x" class="icon-sm me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Meta info --}}
    <div class="col-md-4 col-lg-5">
        <div class="card">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i data-lucide="info" class="icon-sm me-2"></i>Link Info</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width:40%">Code</th>
                        <td><code>{{ $shortUrl->code }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Clicks</th>
                        <td>{{ number_format($shortUrl->clicks) }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Created</th>
                        <td>{{ $shortUrl->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Updated</th>
                        <td>{{ $shortUrl->updated_at->format('M d, Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-scripts')
<script>
    document.getElementById('copyShortUrl')?.addEventListener('click', function() {
        navigator.clipboard.writeText(this.dataset.url).then(function() {
            window.Toast?.fire({
                icon: 'success',
                title: 'Short URL copied!'
            });
        });
    });
</script>
@endpush