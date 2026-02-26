@extends('user.layout.master')

@section('title', 'My Links')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
  <div>
    <h4 class="mb-1">My Links</h4>
    <p class="text-secondary mb-0">Manage and track your shortened URLs.</p>
  </div>
  <div class="d-flex gap-2 mt-3 mt-md-0">
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#createLinkModal">
      <i data-lucide="plus" class="icon-sm"></i> Create New Link
    </button>
  </div>
</div>

{{-- Search & Filter Bar --}}
<div class="card border-0 shadow-sm rounded-3 mb-4">
  <div class="card-body p-3">
    <form action="{{ route('user.my-links') }}" method="GET" class="row g-2 align-items-center">
      <div class="col-md-5">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-light border-end-0"><i data-lucide="search" class="icon-sm text-muted"></i></span>
          <input type="text" class="form-control bg-light border-start-0 ps-0" name="search" placeholder="Search by destination, code, or title..." value="{{ $search }}">
        </div>
      </div>
      <div class="col-md-4">
        <select name="status" class="form-select form-select-sm bg-light">
          <option value="">All Statuses</option>
          <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
          <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
          <option value="expired" {{ $status == 'expired' ? 'selected' : '' }}>Expired</option>
        </select>
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
        @if($search || $status)
        <a href="{{ route('user.my-links') }}" class="btn btn-sm btn-outline-secondary w-100">Reset</a>
        @endif
      </div>
    </form>
  </div>
</div>

{{-- Alerts --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i data-lucide="check-circle" class="icon-sm me-2"></i>{{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <ul class="mb-0 ps-3">
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Links List --}}
<div class="card border-0 shadow-sm rounded-3">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="bg-light bg-opacity-50">
          <tr>
            <th class="border-top-0 px-4 py-3" style="min-width: 250px;">Short Link / Info</th>
            <th class="border-top-0 py-3" style="min-width: 300px;">Original Destination</th>
            <th class="border-top-0 py-3 text-center">Clicks</th>
            <th class="border-top-0 py-3 text-center">Status</th>
            <th class="border-top-0 py-3">Created</th>
            <th class="border-top-0 px-4 py-3 text-end" style="min-width: 140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($links as $link)
          <tr>
            <td class="px-4 py-3">
              <div class="d-flex align-items-center gap-2">
                <a href="{{ $link->short_url }}" target="_blank" class="fw-bold text-primary text-decoration-none">
                  {{ rtrim(url('/'), '/') }}/{{ $link->custom_alias ?: $link->code }}
                </a>
                <button class="btn btn-icon btn-xs btn-outline-secondary border-0 copy-btn" data-clipboard-text="{{ $link->short_url }}" title="Copy Short URL">
                  <i data-lucide="copy" class="icon-xs"></i>
                </button>
              </div>
              @if($link->title)
              <div class="small text-muted mt-1">{{ Str::limit($link->title, 40) }}</div>
              @endif
              @if($link->custom_alias)
              <div class="badge bg-light text-secondary mt-1 border" style="font-size: .65rem;">Custom Alias</div>
              @endif
            </td>
            <td class="py-3">
              <div class="d-flex align-items-center gap-2">
                <img src="https://www.google.com/s2/favicons?domain={{ parse_url($link->original_url, PHP_URL_HOST) }}&sz=32" alt="" width="16" height="16" class="rounded-1 flex-shrink-0" onerror="this.src='{{ asset('images/placeholder-favicon.png') }}';">
                <a href="{{ $link->original_url }}" target="_blank" class="text-body text-truncate d-block text-decoration-none" style="max-width: 280px;" title="{{ $link->original_url }}">
                  {{ $link->original_url }}
                </a>
              </div>
            </td>
            <td class="py-3 text-center">
              <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill fw-medium">
                {{ number_format($link->clicks) }}
              </span>
            </td>
            <td class="py-3 text-center">
              @if($link->status == 'active' && !$link->isExpired())
                <span class="badge bg-success bg-opacity-15 text-success">Active</span>
              @elseif($link->isExpired())
                <span class="badge bg-danger bg-opacity-15 text-danger" title="Expired: {{ $link->expires_at->format('M d, Y') }}">Expired</span>
              @else
                <span class="badge bg-secondary bg-opacity-15 text-secondary">Inactive</span>
              @endif
            </td>
            <td class="py-3 text-muted small">
              <div title="{{ $link->created_at->format('M d, Y H:i:s') }}">
                {{ $link->created_at->format('M d, Y') }}
                <div class="opacity-50" style="font-size:.65rem;">{{ $link->created_at->diffForHumans() }}</div>
              </div>
            </td>
            <td class="px-4 py-3 text-end">
              <div class="btn-group">
                {{-- Toggle Status --}}
                <form action="{{ route('user.links.toggle', $link->id) }}" method="POST" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-icon btn-sm {{ $link->status == 'active' ? 'btn-outline-warning' : 'btn-outline-success' }} me-1" title="{{ $link->status == 'active' ? 'Deactivate' : 'Activate' }}">
                    <i data-lucide="{{ $link->status == 'active' ? 'power-off' : 'power' }}" class="icon-sm"></i>
                  </button>
                </form>
                {{-- Delete --}}
                <form action="{{ route('user.links.destroy', $link->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this link?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-icon btn-sm btn-outline-danger" title="Delete">
                    <i data-lucide="trash-2" class="icon-sm"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="text-center py-5">
              <div class="text-muted">
                <i data-lucide="link-2" class="mb-3 opacity-25" style="width: 48px;height: 48px;"></i>
                <p class="mb-0 fs-5">No links found</p>
                <p class="small opacity-75">Start shortening your first URL today!</p>
                @if($search || $status)
                <a href="{{ route('user.my-links') }}" class="btn btn-sm btn-outline-secondary mt-2">Clear Filters</a>
                @endif
              </div>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($links->hasPages())
  <div class="card-footer bg-transparent border-top p-3 d-flex justify-content-center">
    {{ $links->links() }}
  </div>
  @endif
</div>

{{-- Create Link Modal --}}
<div class="modal fade" id="createLinkModal" tabindex="-1" aria-labelledby="createLinkModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom-0 bg-light">
        <h5 class="modal-title fw-bold" id="createLinkModalLabel">
          <i data-lucide="plus-circle" class="icon-md me-2 text-primary"></i> Create New Link
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('user.links.store') }}" method="POST">
        @csrf
        <div class="modal-body p-4 pt-3">
          <div class="mb-3">
            <label class="form-label fw-semibold">Destination URL <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i data-lucide="link" class="icon-sm text-muted"></i></span>
              <input type="url" name="original_url" class="form-control border-start-0 ps-0" placeholder="https://example.com/long-page-url" value="{{ old('original_url') }}" required>
            </div>
            <div class="form-text">The long URL you want to shorten.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Short Title <span class="text-muted fw-normal">(Optional)</span></label>
            <input type="text" name="title" class="form-control" placeholder="E.g. Summer Promo Campaign" value="{{ old('title') }}">
            <div class="form-text">To help you organize and identify your links.</div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Custom Alias <span class="text-muted fw-normal">(Optional)</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light ps-3 pe-2 text-muted" style="font-size: .85rem;">{{ rtrim(url('/'), '/') }}/</span>
              <input type="text" name="custom_alias" class="form-control" placeholder="my-custom-name" value="{{ old('custom_alias') }}" pattern="[A-Za-z0-9\-_]+" title="Only alphanumeric characters, dashes and underscores">
            </div>
            <div class="form-text">Leave blank to generate a random short code.</div>
          </div>
          
          <div class="mb-1">
            <label class="form-label fw-semibold">Expiration Date <span class="text-muted fw-normal">(Optional)</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0"><i data-lucide="calendar" class="icon-sm text-muted"></i></span>
              <input type="datetime-local" name="expires_at" class="form-control border-start-0 ps-0" value="{{ old('expires_at') }}">
            </div>
            <div class="form-text">The link will stop working after this time.</div>
          </div>
        </div>
        <div class="modal-footer bg-light border-top-0 pt-3 pb-3">
          <button type="button" class="btn btn-outline-secondary px-4 fw-medium" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary px-4 fw-medium shadow-sm">Create Short Link</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/clipboard/clipboard.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof ClipboardJS !== 'undefined') {
      const clipboard = new ClipboardJS('.copy-btn');
      
      clipboard.on('success', function(e) {
        const btn = e.trigger;
        const icon = btn.querySelector('i');
        const origIcon = icon.getAttribute('data-lucide');
        
        // Show check
        icon.setAttribute('data-lucide', 'check');
        icon.classList.add('text-success');
        lucide.createIcons({ name: 'check', attrs: { class: 'icon-xs text-success' } });
        
        e.clearSelection();
        
        // Revert after 2s
        setTimeout(() => {
          btn.innerHTML = `<i data-lucide="copy" class="icon-xs"></i>`;
          lucide.createIcons();
        }, 2000);
      });
    }

    // Auto-show modal if there are errors (meaning form submission failed)
    @if($errors->any() && old('original_url'))
    new bootstrap.Modal(document.getElementById('createLinkModal')).show();
    @endif
  });
</script>
@endpush
