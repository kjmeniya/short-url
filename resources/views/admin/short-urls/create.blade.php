@extends('admin.layout.master')

@section('title', $title ?? 'Create Short URL')
@section('description', $description ?? 'Create a new shortened URL.')

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.short-urls.index') }}">Short URLs</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-8 col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i data-lucide="link" class="icon-sm me-2"></i>Create Short URL
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

                <form action="{{ route('admin.short-urls.store') }}" method="POST">
                    @csrf

                    {{-- Destination URL --}}
                    <div class="mb-3">
                        <label for="original_url" class="form-label">Destination URL <span class="text-danger">*</span></label>
                        <input type="url" id="original_url" name="original_url"
                            class="form-control @error('original_url') is-invalid @enderror"
                            value="{{ old('original_url') }}" placeholder="https://example.com/very-long-url" required>
                        @error('original_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Title --}}
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-muted">(optional)</span></label>
                        <input type="text" id="title" name="title"
                            class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') }}" placeholder="Friendly name for this link">
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Custom Alias --}}
                    <div class="mb-3">
                        <label for="custom_alias" class="form-label">Custom Alias <span class="text-muted">(optional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text text-muted" id="alias-addon">{{ url('/') }}/</span>
                            <input type="text" id="custom_alias" name="custom_alias"
                                class="form-control @error('custom_alias') is-invalid @enderror"
                                value="{{ old('custom_alias') }}" placeholder="my-link"
                                aria-describedby="alias-addon">
                        </div>
                        <div class="form-text">Letters, numbers, hyphens, and underscores only. Leave blank to auto-generate.</div>
                        @error('custom_alias')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" {{ old('status', 'active') === 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Expiry Date --}}
                    <div class="mb-3">
                        <label for="expires_at" class="form-label">Expiry Date <span class="text-muted">(optional)</span></label>
                        <input type="datetime-local" id="expires_at" name="expires_at"
                            class="form-control @error('expires_at') is-invalid @enderror"
                            value="{{ old('expires_at') }}">
                        <div class="form-text">Leave blank for no expiry.</div>
                        @error('expires_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-4">
                        <label for="password" class="form-label">Password Protection <span class="text-muted">(optional)</span></label>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Leave blank for no password" autocomplete="new-password">
                        <div class="form-text">Visitors will need this password to access the link.</div>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            <i data-lucide="save" class="icon-sm me-1"></i>Create Short URL
                        </button>
                        <a href="{{ route('admin.short-urls.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i data-lucide="x" class="icon-sm me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Side Tips --}}
    <div class="col-md-4 col-lg-5">
        <div class="card border-0 bg-primary bg-opacity-10">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i data-lucide="info" class="icon-sm me-2"></i>Tips</h6>
                <ul class="list-unstyled small text-muted mb-0" style="line-height:2;">
                    <li><i data-lucide="check" class="icon-xs me-2 text-success"></i>A short code will be auto-generated if no custom alias is set.</li>
                    <li><i data-lucide="check" class="icon-xs me-2 text-success"></i>Custom aliases must be unique across all links.</li>
                    <li><i data-lucide="check" class="icon-xs me-2 text-success"></i>Set an expiry date to automatically deactivate the link.</li>
                    <li><i data-lucide="check" class="icon-xs me-2 text-success"></i>Password-protected links prompt visitors before redirecting.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection