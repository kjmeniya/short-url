@extends('admin.layout.master')

@section('title', 'Global IP Blocks')

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item active" aria-current="page">Global IP Blocks</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <h6 class="card-title mb-0">Global IP Blocks Management</h6>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i data-lucide="check-circle" class="icon-sm me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i data-lucide="alert-circle" class="icon-sm me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <div class="row g-4">
                    {{-- Form Column --}}
                    <div class="col-lg-4">
                        <div class="card border shadow-none">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fs-14px">Add Global Block</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Add a block that applies to ALL short links globally.</p>
                                <form action="{{ route('admin.global-ip-blocks.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label mb-1">Type</label>
                                        <select name="type" class="form-select form-select-sm @error('type') is-invalid @enderror">
                                            <option value="ip" {{ old('type') == 'ip' ? 'selected' : '' }}>IP Address</option>
                                            <option value="cidr" {{ old('type') == 'cidr' ? 'selected' : '' }}>CIDR Range</option>
                                        </select>
                                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label mb-1">Value</label>
                                        <input type="text" name="value" class="form-control form-control-sm @error('value') is-invalid @enderror" value="{{ old('value') }}" placeholder="192.168.1.1 or 10.0.0.0/24">
                                        @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <i data-lucide="plus" class="icon-xs me-1"></i> Add Block
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Table Column --}}
                    <div class="col-lg-8">
                        <h6 class="card-title mb-3 fs-14px">Blocked IPs & Ranges</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Added At</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($blocks as $block)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary" style="font-size: 0.65rem;">
                                                {{ strtoupper($block->type) }}
                                            </span>
                                        </td>
                                        <td class="fw-medium">{{ $block->value }}</td>
                                        <td class="text-muted small">{{ $block->created_at->format('M d, Y H:i') }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('admin.global-ip-blocks.destroy', $block->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this block?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon btn-xs btn-outline-danger border-0" title="Delete">
                                                    <i data-lucide="trash" class="icon-xs"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i data-lucide="shield" class="icon-lg opacity-25 d-block mx-auto mb-2"></i>
                                            No global blocks defined.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $blocks->appends(['logs_page' => request('logs_page')])->links() }}
                        </div>
                    </div>
                </div>

                {{-- Logs Section --}}
                <div class="mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="card-title mb-0">Recent Blocked Attempts</h6>
                        <span class="badge bg-danger bg-opacity-10 text-danger px-2">Live Monitor</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>IP Address</th>
                                    <th>Blocked By Rule</th>
                                    <th>Target Link</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td><span class="text-danger fw-semibold">{{ $log->ip_address }}</span></td>
                                    <td><code class="small">{{ $log->matched_rule }}</code></td>
                                    <td>
                                        @if($log->shortUrl)
                                            <a href="{{ route('admin.short-urls.show', $log->shortUrl->id) }}" class="text-decoration-none">
                                                <i data-lucide="link-2" class="icon-xs me-1"></i>{{ $log->shortUrl->code }}
                                            </a>
                                        @else
                                            <span class="text-muted small">Global / System</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $log->created_at->diffForHumans() }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">No blocked attempts yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $logs->appends(['blocks_page' => request('blocks_page')])->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

