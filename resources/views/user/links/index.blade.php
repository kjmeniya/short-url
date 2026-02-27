@extends('user.layout.master')

@section('title', 'My Links')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
  .stats-card {
    border-left: 4px solid;
  }

  .stats-card.total {
    border-left-color: #6c757d;
  }

  .stats-card.active {
    border-left-color: #198754;
  }

  .stats-card.inactive {
    border-left-color: #ffc107;
  }

  .stats-card.expired {
    border-left-color: #dc3545;
  }

  .stats-card.clicks {
    border-left-color: #0d6efd;
  }

  .stats-card.month {
    border-left-color: #20c997;
  }

  .filter-chevron {
    transition: transform 0.2s ease-in-out;
  }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">My Links</li>
  </ol>
</nav>

{{-- ── Statistics ── --}}
<div class="card mb-3">
  <div class="card-header p-0">
    <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
      type="button" data-bs-toggle="collapse" data-bs-target="#statisticsCollapse" aria-expanded="true">
      <span><i data-lucide="bar-chart-3" class="icon-sm me-2"></i>Statistics</span>
      <i data-lucide="chevron-up" class="icon-sm stats-chevron"></i>
    </button>
  </div>
  <div class="collapse show" id="statisticsCollapse">
    <div class="card-body">
      <div class="row">
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
          <div class="card stats-card total">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Total</h6>
                  <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                </div>
                <i data-lucide="link" class="icon-lg text-muted"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
          <div class="card stats-card active">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Active</h6>
                  <h4 class="mb-0">{{ number_format($stats['active']) }}</h4>
                </div>
                <i data-lucide="check-circle" class="icon-lg text-success"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
          <div class="card stats-card inactive">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Inactive</h6>
                  <h4 class="mb-0">{{ number_format($stats['inactive']) }}</h4>
                </div>
                <i data-lucide="pause-circle" class="icon-lg text-warning"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
          <div class="card stats-card expired">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Expired</h6>
                  <h4 class="mb-0">{{ number_format($stats['expired']) }}</h4>
                </div>
                <i data-lucide="clock" class="icon-lg text-danger"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
          <div class="card stats-card clicks">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">Total Clicks</h6>
                  <h4 class="mb-0">{{ number_format($stats['total_clicks']) }}</h4>
                </div>
                <i data-lucide="mouse-pointer-click" class="icon-lg text-primary"></i>
              </div>
            </div>
          </div>
        </div>
        <div class="col-6 col-sm-6 col-md-4 col-lg-4 col-xl-2 mb-3">
          <div class="card stats-card month">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title mb-0">This Month</h6>
                  <h4 class="mb-0">{{ number_format($stats['this_month']) }}</h4>
                </div>
                <i data-lucide="calendar" class="icon-lg text-info"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── Main Table Card ── --}}
<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">

        {{-- Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
          <h6 class="card-title mb-0">My Short Links</h6>
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('user.links.create') }}" class="btn btn-primary btn-sm">
              <i data-lucide="plus" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Create New Link</span>
              <span class="d-sm-none">New</span>
            </a>
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
          <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Filters --}}
        <div class="card mb-3">
          <div class="card-header p-0">
            <button class="btn btn-link w-100 text-start d-flex justify-content-between align-items-center p-3"
              type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse" aria-expanded="{{ ($search || $status) ? 'true' : 'false' }}">
              <span>
                <i data-lucide="filter" class="icon-sm me-2"></i>Filters
                @if($search || $status)
                <span class="badge bg-primary ms-2">{{ ($search ? 1 : 0) + ($status ? 1 : 0) }}</span>
                @endif
              </span>
              <i data-lucide="{{ ($search || $status) ? 'chevron-up' : 'chevron-down' }}" class="icon-sm filter-chevron"></i>
            </button>
          </div>
          <div class="collapse {{ ($search || $status) ? 'show' : '' }}" id="filtersCollapse">
            <div class="card-body">
              <form action="{{ route('user.links') }}" method="GET" id="filterForm">
                <div class="row g-3">
                  <div class="col-12 col-sm-6 col-md-4">
                    <label for="searchInput" class="form-label">Search</label>
                    <input type="text" id="searchInput" name="search" class="form-control form-control-sm"
                      placeholder="Search by title, URL, code..." value="{{ $search }}">
                  </div>
                  <div class="col-12 col-sm-6 col-md-3">
                    <label for="statusFilter" class="form-label">Status</label>
                    <select id="statusFilter" name="status" class="form-select form-select-sm">
                      <option value="">All Status</option>
                      <option value="active" {{ $status == 'active'   ? 'selected' : '' }}>Active</option>
                      <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                      <option value="expired" {{ $status == 'expired'  ? 'selected' : '' }}>Expired</option>
                    </select>
                  </div>
                </div>
                <div class="row mt-2">
                  <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">
                      <i data-lucide="search" class="icon-sm me-1"></i>Apply
                    </button>
                    @if($search || $status)
                    <a href="{{ route('user.links') }}" class="btn btn-outline-secondary btn-sm">
                      <i data-lucide="x" class="icon-sm me-1"></i>Clear
                    </a>
                    @endif
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th style="min-width: 220px;">Short Link</th>
                <th style="min-width: 280px;">Destination</th>
                <th class="text-center">Clicks</th>
                <th class="text-center">Status</th>
                <th>Expires</th>
                <th>Created</th>
                <th class="text-end" style="min-width: 130px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($links as $link)
              <tr>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <a href="{{ $link->short_url }}" target="_blank" class="fw-semibold text-primary text-decoration-none">
                      {{ rtrim(url('/'), '/') }}/{{ $link->custom_alias ?: $link->code }}
                    </a>
                    <button class="btn btn-icon btn-xs btn-outline-secondary border-0 copy-btn"
                      data-url="{{ $link->short_url }}" title="Copy">
                      <i data-lucide="copy" class="icon-xs"></i>
                    </button>
                  </div>
                  @if($link->title)
                  <div class="small text-muted mt-1">{{ Str::limit($link->title, 40) }}</div>
                  @endif
                  @if($link->custom_alias)
                  <div class="mt-1">
                    <span class="badge bg-light text-secondary border" style="font-size:.65rem;">Custom Alias</span>
                  </div>
                  @endif
                  @if($link->password)
                  <div class="mt-1">
                    <span class="badge bg-warning bg-opacity-15 text-warning border border-warning" style="font-size:.65rem;">
                      <i data-lucide="lock" style="width:10px;height:10px;"></i> Password
                    </span>
                  </div>
                  @endif
                  @if($link->isPrivate())
                  <div class="mt-1">
                    <span class="badge bg-danger bg-opacity-15 text-danger border border-danger" style="font-size:.65rem;">
                      <i data-lucide="shield" style="width:10px;height:10px;"></i> Private
                    </span>
                  </div>
                  @endif
                  @if($link->is24hStory())
                  <div class="mt-1">
                    <span class="badge bg-primary bg-opacity-15 text-primary border border-primary" style="font-size:.65rem;">
                      <i data-lucide="clock" style="width:10px;height:10px;"></i> 24h Story
                    </span>
                  </div>
                  @endif
                  @if($link->isOneTime())
                  <div class="mt-1">
                    <span class="badge bg-secondary bg-opacity-25 text-dark border border-secondary" style="font-size:.65rem;">
                      <i data-lucide="zap" style="width:10px;height:10px;"></i> One-time
                    </span>
                  </div>
                  @endif
                  @if($link->max_clicks)
                  <div class="mt-1">
                    <span class="badge bg-info bg-opacity-15 text-info border border-info" style="font-size:.65rem;">
                      <i data-lucide="mouse-pointer-click" style="width:10px;height:10px;"></i>
                      Limit: {{ number_format($link->clicks) }}/{{ number_format($link->max_clicks) }}
                    </span>
                    @php $pct = $link->clickUsagePercent(); @endphp
                    @if($pct !== null)
                    <div class="progress mt-1" style="height:3px; max-width:120px;">
                      <div class="progress-bar {{ $pct >= 100 ? 'bg-danger' : ($pct >= 75 ? 'bg-warning' : 'bg-info') }}"
                        role="progressbar" style="width:{{ $pct }}%"></div>
                    </div>
                    @endif
                  </div>
                  @endif
                  @if($link->ipBlocks->count())
                  <div class="mt-1">
                    <span class="badge bg-danger bg-opacity-15 text-danger border border-danger" style="font-size:.65rem;">
                      <i data-lucide="shield-alert" style="width:10px;height:10px;"></i> IP Blocks
                    </span>
                  </div>
                  @endif
                  @if($link->redirect_delay > 0)
                  <div class="mt-1">
                    <span class="badge bg-dark bg-opacity-10 text-dark border border-dark" style="font-size:.65rem;">
                      <i data-lucide="timer" style="width:10px;height:10px;"></i> Delay: {{ $link->redirect_delay }}s
                    </span>
                  </div>
                  @endif
                  @if($link->mobile_url || $link->tablet_url || $link->desktop_url || $link->office_url || $link->after_hours_url)
                  <div class="mt-1">
                    <span class="badge bg-success bg-opacity-15 text-success border border-success" style="font-size:.65rem;">
                      <i data-lucide="git-branch" style="width:10px;height:10px;"></i> Smart Rules
                    </span>
                  </div>
                  @endif
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <img src="https://www.google.com/s2/favicons?domain={{ parse_url($link->original_url, PHP_URL_HOST) }}&sz=32"
                      alt="" width="16" height="16" class="rounded-1 flex-shrink-0"
                      onerror="this.src='{{ asset('images/placeholder-favicon.png') }}';">
                    <a href="{{ $link->original_url }}" target="_blank"
                      class="text-body text-truncate d-block text-decoration-none"
                      style="max-width: 240px;" title="{{ $link->original_url }}">
                      {{ $link->original_url }}
                    </a>
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill">
                    {{ number_format($link->clicks) }}
                  </span>
                </td>
                <td class="text-center">
                  @if($link->status === 'active' && !$link->isExpired() && !$link->isClickLimitReached())
                  <span class="badge bg-success">Active</span>

                  @elseif($link->isExpired() || $link->isClickLimitReached())
                  <span class="badge bg-danger"
                    title="
          {{ $link->isExpired() && $link->expires_at ? 'Expired: '.$link->expires_at->format('M d, Y') : '' }}
          {{ $link->isClickLimitReached() ? ' Click limit reached ('.$link->clicks.'/'.$link->max_clicks.')' : '' }}">
                    Expired
                  </span>

                  @else
                  <span class="badge bg-secondary">Inactive</span>
                  @endif
                </td>
                <td class="text-muted small">
                  @if($link->expires_at)
                  <span class="{{ $link->isExpired() ? 'text-danger' : '' }}">
                    {{ $link->expires_at->format('M d, Y') }}
                  </span>
                  @else
                  <span class="text-muted">Never</span>
                  @endif
                </td>
                <td class="text-muted small">
                  <div title="{{ $link->created_at->format('M d, Y H:i:s') }}">
                    {{ $link->created_at->format('M d, Y') }}
                    <div class="opacity-50" style="font-size:.65rem;">{{ $link->created_at->diffForHumans() }}</div>
                  </div>
                </td>
                <td class="text-end">
                  <div class="d-flex gap-1 justify-content-end">
                    {{-- Edit --}}
                    <a href="{{ route('user.links.edit', $link->id) }}"
                      class="btn btn-icon btn-sm btn-outline-primary" title="Edit">
                      <i data-lucide="pencil" class="icon-sm"></i>
                    </a>
                    {{-- Toggle --}}
                    <form action="{{ route('user.links.toggle', $link->id) }}" method="POST" class="d-inline">
                      @csrf
                      <button type="submit"
                        class="btn btn-icon btn-sm {{ $link->status == 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                        title="{{ $link->status == 'active' ? 'Deactivate' : 'Activate' }}">
                        <i data-lucide="{{ $link->status == 'active' ? 'power-off' : 'power' }}" class="icon-sm"></i>
                      </button>
                    </form>
                    {{-- Delete --}}
                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger delete-link-btn"
                      data-id="{{ $link->id }}"
                      data-action="{{ route('user.links.destroy', $link->id) }}"
                      title="Delete">
                      <i data-lucide="trash-2" class="icon-sm"></i>
                    </button>
                  </div>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="7" class="text-center py-5">
                  <i data-lucide="link-2" class="text-muted opacity-25 mb-3" style="width:48px;height:48px;"></i>
                  <p class="mb-1 fs-5 text-muted">No links found</p>
                  <p class="small text-muted opacity-75">
                    @if($search || $status)
                    No results match your current filters.
                    @else
                    Start shortening your first URL!
                    @endif
                  </p>
                  @if($search || $status)
                  <a href="{{ route('user.links') }}" class="btn btn-sm btn-outline-secondary mt-1">
                    <i data-lucide="x" class="icon-sm me-1"></i>Clear Filters
                  </a>
                  @else
                  <a href="{{ route('user.links.create') }}" class="btn btn-sm btn-primary mt-1">
                    <i data-lucide="plus" class="icon-sm me-1"></i>Create Your First Link
                  </a>
                  @endif
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        @if($links->hasPages())
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
          <div class="text-muted small">
            Showing {{ $links->firstItem() }} to {{ $links->lastItem() }} of {{ $links->total() }} links
          </div>
          <div>
            {{ $links->links() }}
          </div>
        </div>
        @endif

      </div>
    </div>
  </div>
</div>

{{-- Hidden delete forms --}}
<div id="deleteForms"></div>

@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/clipboard/clipboard.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
  $(document).ready(function() {

    // ── Statistics accordion chevron ──
    $('#statisticsCollapse').on('show.bs.collapse', function() {
      $('.stats-chevron').attr('data-lucide', 'chevron-up');
      if (typeof lucide !== 'undefined') lucide.createIcons();
    });
    $('#statisticsCollapse').on('hide.bs.collapse', function() {
      $('.stats-chevron').attr('data-lucide', 'chevron-down');
      if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    // ── Filters accordion chevron ──
    $('#filtersCollapse').on('show.bs.collapse', function() {
      $('.filter-chevron').attr('data-lucide', 'chevron-up');
      if (typeof lucide !== 'undefined') lucide.createIcons();
    });
    $('#filtersCollapse').on('hide.bs.collapse', function() {
      $('.filter-chevron').attr('data-lucide', 'chevron-down');
      if (typeof lucide !== 'undefined') lucide.createIcons();
    });

    // ── Copy to clipboard ──
    if (typeof ClipboardJS !== 'undefined') {
      const clipboard = new ClipboardJS('.copy-btn', {
        text: function(trigger) {
          return trigger.getAttribute('data-url');
        }
      });
      clipboard.on('success', function(e) {
        const btn = e.trigger;
        const origHtml = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="check" class="icon-xs text-success"></i>';
        if (typeof lucide !== 'undefined') lucide.createIcons();
        e.clearSelection();
        setTimeout(() => {
          btn.innerHTML = origHtml;
          if (typeof lucide !== 'undefined') lucide.createIcons();
        }, 2000);
      });
    }

    // ── Delete with SweetAlert ──
    $(document).on('click', '.delete-link-btn', function() {
      const id = $(this).data('id');
      const action = $(this).data('action');

      Swal.fire({
        title: 'Delete this link?',
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
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
          const form = $('<form>', {
            method: 'POST',
            action: action
          }).append(
            $('<input>', {
              type: 'hidden',
              name: '_token',
              value: '{{ csrf_token() }}'
            }),
            $('<input>', {
              type: 'hidden',
              name: '_method',
              value: 'DELETE'
            })
          );
          $('#deleteForms').append(form);
          form.submit();
        }
      });
    });

  });
</script>
@endpush