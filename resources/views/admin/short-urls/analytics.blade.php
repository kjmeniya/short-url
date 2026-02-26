@extends('admin.layout.master')

@section('title', $title ?? 'Short URL Analytics')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
    .stat-card-border {
        border-left: 3px solid transparent;
    }

    .stat-card-border.c-primary {
        border-left-color: var(--bs-primary);
    }

    .stat-card-border.c-success {
        border-left-color: var(--bs-success);
    }

    .stat-card-border.c-info {
        border-left-color: var(--bs-info);
    }

    .stat-card-border.c-warning {
        border-left-color: var(--bs-warning);
    }

    /* Horizontal progress bars */
    .hbar {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        font-size: .82rem;
    }

    .hbar-label {
        width: 130px;
        flex-shrink: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .hbar-track {
        flex-grow: 1;
        height: 8px;
        border-radius: 50px;
        background: rgba(128, 128, 128, .12);
    }

    .hbar-fill {
        height: 100%;
        border-radius: 50px;
        transition: width .5s ease;
    }

    .hbar-count {
        width: 36px;
        text-align: right;
        font-weight: 600;
        flex-shrink: 0;
    }

    /* Device legend pills */
    .dev-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 50px;
        font-size: .78rem;
        font-weight: 500;
    }

    #clicksChart {
        min-height: 270px;
    }

    #deviceDonut {
        min-height: 170px;
    }
</style>
@endpush

@section('content')

{{-- Breadcrumb --}}
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.short-urls.index') }}">Short URLs</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.short-urls.show', $shortUrl->id) }}">#{{ $shortUrl->code }}</a></li>
        <li class="breadcrumb-item active">Analytics</li>
    </ol>
</nav>

{{-- Header --}}
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-start mb-4">
    <div>
        <h5 class="mb-1">
            <i data-lucide="bar-chart-3" class="icon-sm me-2 text-primary"></i>
            {{ $shortUrl->title ?: 'Short URL Analytics' }}
        </h5>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ $shortUrl->short_url }}" target="_blank" class="text-primary fw-semibold small">
                {{ $shortUrl->short_url }}
            </a>
            <span class="text-muted small">→</span>
            <span class="text-muted small text-truncate" style="max-width:300px;" title="{{ $shortUrl->original_url }}">
                {{ Str::limit($shortUrl->original_url, 60) }}
            </span>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.short-urls.show', $shortUrl->id) }}" class="btn btn-sm btn-outline-secondary">
            <i data-lucide="info" class="icon-sm me-1"></i>Details
        </a>
        <a href="{{ route('admin.short-urls.edit', $shortUrl->id) }}" class="btn btn-sm btn-outline-primary">
            <i data-lucide="edit" class="icon-sm me-1"></i>Edit
        </a>
    </div>
</div>

{{-- ── Quick Stats ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card stat-card-border c-primary h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Total Clicks</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($totalClicks) }}</h3>
                    </div>
                    <div class="p-2 bg-primary bg-opacity-10 rounded">
                        <i data-lucide="mouse-pointer-click" class="icon-sm text-primary"></i>
                    </div>
                </div>
                <p class="text-muted mb-0 mt-2 small">
                    Counter: {{ number_format($shortUrl->clicks) }}
                </p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card-border c-success h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Today</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($todayClicks) }}</h3>
                    </div>
                    <div class="p-2 bg-success bg-opacity-10 rounded">
                        <i data-lucide="calendar" class="icon-sm text-success"></i>
                    </div>
                </div>
                <p class="text-muted mb-0 mt-2 small">{{ now()->format('M d, Y') }}</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card-border c-info h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Unique IPs</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($uniqueIPs) }}</h3>
                    </div>
                    <div class="p-2 bg-info bg-opacity-10 rounded">
                        <i data-lucide="globe" class="icon-sm text-info"></i>
                    </div>
                </div>
                <p class="text-muted mb-0 mt-2 small">Distinct visitors</p>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card-border c-warning h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 small">Mobile Clicks</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($mobileClicks) }}</h3>
                    </div>
                    <div class="p-2 bg-warning bg-opacity-10 rounded">
                        <i data-lucide="smartphone" class="icon-sm text-warning"></i>
                    </div>
                </div>
                <p class="text-muted mb-0 mt-2 small">
                    @if($totalClicks > 0)
                    {{ round($mobileClicks / $totalClicks * 100) }}% of total
                    @else —
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

{{-- ── Clicks Over Time ── --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="card-title mb-0">
            <i data-lucide="trending-up" class="icon-sm me-2"></i>Clicks Over Time
        </h6>
        <div class="btn-group btn-group-sm" role="group">
            <a href="{{ route('admin.short-urls.analytics', $shortUrl->id) }}?days=7"
                class="btn btn-outline-primary {{ $days == 7 ? 'active' : '' }}">7 Days</a>
            <a href="{{ route('admin.short-urls.analytics', $shortUrl->id) }}?days=30"
                class="btn btn-outline-primary {{ $days == 30 ? 'active' : '' }}">30 Days</a>
            <a href="{{ route('admin.short-urls.analytics', $shortUrl->id) }}?days=90"
                class="btn btn-outline-primary {{ $days == 90 ? 'active' : '' }}">90 Days</a>
        </div>
    </div>
    <div class="card-body">
        @if($totalClicks === 0)
        <div class="text-center text-muted py-5">
            <i data-lucide="bar-chart-2" style="width:40px;height:40px;" class="mb-2 d-block mx-auto opacity-25"></i>
            <p class="mb-0">No click data yet. Share your short URL to start tracking analytics!</p>
        </div>
        @else
        <div id="clicksChart"></div>
        @endif
    </div>
</div>

{{-- ── Breakdown Row ── --}}
<div class="row g-3 mb-4">

    {{-- Browsers --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-lucide="globe-2" class="icon-sm me-2"></i>Browsers
                </h6>
            </div>
            <div class="card-body">
                @if(empty($browsers))
                <p class="text-muted text-center py-4 mb-0">No data yet</p>
                @else
                @php $bMax = max(array_column($browsers, 'total')); @endphp
                @foreach($browsers as $b)
                <div class="hbar">
                    <div class="hbar-label" title="{{ $b['label'] ?: 'Unknown' }}">{{ $b['label'] ?: 'Unknown' }}</div>
                    <div class="hbar-track">
                        <div class="hbar-fill bg-primary" style="width:{{ $bMax > 0 ? round($b['total']/$bMax*100) : 0 }}%"></div>
                    </div>
                    <div class="hbar-count">{{ number_format($b['total']) }}</div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Operating Systems --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-lucide="monitor" class="icon-sm me-2"></i>Operating Systems
                </h6>
            </div>
            <div class="card-body">
                @if(empty($operatingSys))
                <p class="text-muted text-center py-4 mb-0">No data yet</p>
                @else
                @php $oMax = max(array_column($operatingSys, 'total')); @endphp
                @foreach($operatingSys as $o)
                <div class="hbar">
                    <div class="hbar-label" title="{{ $o['label'] ?: 'Unknown' }}">{{ $o['label'] ?: 'Unknown' }}</div>
                    <div class="hbar-track">
                        <div class="hbar-fill bg-info" style="width:{{ $oMax > 0 ? round($o['total']/$oMax*100) : 0 }}%"></div>
                    </div>
                    <div class="hbar-count">{{ number_format($o['total']) }}</div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Device Types --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-lucide="smartphone" class="icon-sm me-2"></i>Devices
                </h6>
            </div>
            <div class="card-body">
                @if(empty($devices))
                <p class="text-muted text-center py-4 mb-0">No data yet</p>
                @else
                @php
                $devTotal = array_sum(array_column($devices, 'total'));
                $devColors = ['desktop'=>'primary','mobile'=>'success','tablet'=>'info','bot'=>'warning','unknown'=>'secondary'];
                $devIcons = ['desktop'=>'monitor','mobile'=>'smartphone','tablet'=>'tablet','bot'=>'cpu','unknown'=>'help-circle'];
                @endphp
                <div id="deviceDonut" class="mb-3"></div>
                <div class="d-flex flex-wrap gap-2 justify-content-center mt-1">
                    @foreach($devices as $dv)
                    @php
                    $k = strtolower($dv['label'] ?? 'unknown');
                    $pct = $devTotal > 0 ? round($dv['total'] / $devTotal * 100) : 0;
                    $c = $devColors[$k] ?? 'secondary';
                    @endphp
                    <div class="dev-pill bg-{{ $c }} bg-opacity-10 text-{{ $c }}">
                        <i data-lucide="{{ $devIcons[$k] ?? 'help-circle' }}" style="width:11px;height:11px;"></i>
                        {{ ucfirst($dv['label'] ?: 'Unknown') }}
                        <strong>{{ $pct }}%</strong>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Top Referrers --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-lucide="external-link" class="icon-sm me-2"></i>Top Referrers
                </h6>
            </div>
            <div class="card-body">
                @if(empty($referrers))
                <p class="text-muted text-center py-4 mb-0">
                    No referrer data yet.<br>
                    <small>Direct traffic or privacy-stripped headers won't appear.</small>
                </p>
                @else
                @php $rMax = max(array_column($referrers, 'total')); @endphp
                @foreach($referrers as $r)
                <div class="hbar">
                    <div class="hbar-label" title="{{ $r['label'] ?: 'Direct' }}">{{ $r['label'] ?: 'Direct' }}</div>
                    <div class="hbar-track">
                        <div class="hbar-fill bg-warning" style="width:{{ $rMax > 0 ? round($r['total']/$rMax*100) : 0 }}%"></div>
                    </div>
                    <div class="hbar-count">{{ number_format($r['total']) }}</div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    {{-- Countries --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i data-lucide="map-pin" class="icon-sm me-2"></i>Countries
                    <span class="ms-2 badge bg-secondary bg-opacity-20 text-secondary fw-normal fs-12px">
                        via ip-api.in
                    </span>
                </h6>
            </div>
            <div class="card-body">
                @php $validCountries = array_filter($countries, fn($c) => !empty($c['label'])); @endphp
                @if(empty($validCountries))
                <div class="text-center text-muted py-4">
                    <i data-lucide="globe" style="width:32px;height:32px;" class="mb-2 d-block mx-auto opacity-25"></i>
                    <p class="mb-1">No country data yet.</p>
                    <small>Country is detected via <code>ip_api_url</code> on each click. Data will appear here once visitors start clicking your link.</small>
                </div>
                @else
                @php $cMax = max(array_column($validCountries, 'total')); @endphp
                <div class="row g-2">
                    @foreach($validCountries as $c)
                    <div class="col-12 col-md-6">
                        <div class="hbar mb-1">
                            <div class="hbar-label" title="{{ $c['label'] }}">{{ $c['label'] }}</div>
                            <div class="hbar-track">
                                <div class="hbar-fill bg-success" style="width:{{ $cMax > 0 ? round($c['total']/$cMax*100) : 0 }}%"></div>
                            </div>
                            <div class="hbar-count">{{ number_format($c['total']) }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

{{-- ── Recent Clicks Table ── --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
            <i data-lucide="clock" class="icon-sm me-2"></i>Recent Clicks
            <span class="badge bg-secondary ms-1">Last 50</span>
        </h6>
    </div>
    <div class="card-body p-0">
        @if($recentClicks->isEmpty())
        <p class="text-muted text-center py-5 mb-0">No clicks recorded yet.</p>
        @else
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 small align-middle">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>IP Address</th>
                        <th>Browser</th>
                        <th>OS</th>
                        <th>Device</th>
                        <th>Country / City</th>
                        <th>Referrer</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentClicks as $click)
                    @php
                    $dtc = ['desktop'=>'primary','mobile'=>'success','tablet'=>'info','bot'=>'warning'];
                    @endphp
                    <tr>
                        <td class="text-nowrap text-muted">{{ $click->clicked_at->format('M d, H:i:s') }}</td>
                        <td><code class="small">{{ $click->ip_address ?? '—' }}</code></td>
                        <td>
                            {{ $click->browser ?: '—' }}
                            @if($click->browser_version)
                            <span class="text-muted"> {{ Str::before($click->browser_version, '.') }}</span>
                            @endif
                        </td>
                        <td>{{ $click->os ?: '—' }}</td>
                        <td>
                            @if($click->device_type && $click->device_type !== 'unknown')
                            <span class="badge bg-{{ $dtc[$click->device_type] ?? 'secondary' }} bg-opacity-15 text-{{ $dtc[$click->device_type] ?? 'secondary' }}">
                                {{ ucfirst($click->device_type) }}
                            </span>
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($click->country)
                            {{ $click->country }}@if($click->city), <span class="text-muted">{{ $click->city }}</span>@endif
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-truncate" style="max-width:180px;" title="{{ $click->referrer }}">
                            {{ $click->referrer_domain ?: ($click->referrer ? Str::limit($click->referrer, 30) : 'Direct') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
    (function() {
        'use strict';

        var chartData = {
            clicksOverTime: <?= json_encode($clicksOverTime) ?>,
            devices: <?= json_encode($devices) ?>,
        };

        var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        var tooltipTheme = isDark ? 'dark' : 'light';

        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            // ── Clicks over time ─────────────────────────────────────────────────────
            var clickEl = document.getElementById('clicksChart');
            if (clickEl && chartData.clicksOverTime && chartData.clicksOverTime.data.length) {
                new ApexCharts(clickEl, {
                    chart: {
                        type: 'area',
                        height: 270,
                        fontFamily: 'inherit',
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        }
                    },
                    series: [{
                        name: 'Clicks',
                        data: chartData.clicksOverTime.data
                    }],
                    colors: ['#245dac'],
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    dataLabels: {
                        enabled: false
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.4,
                            opacityTo: 0.02,
                            stops: [0, 95, 100]
                        }
                    },
                    xaxis: {
                        categories: chartData.clicksOverTime.labels,
                        labels: {
                            style: {
                                fontSize: '11px'
                            },
                            rotate: -30
                        },
                        tickAmount: 10
                    },
                    yaxis: {
                        min: 0,
                        labels: {
                            formatter: function(v) {
                                return Math.round(v);
                            }
                        }
                    },
                    grid: {
                        borderColor: 'rgba(128,128,128,.1)'
                    },
                    tooltip: {
                        theme: tooltipTheme
                    },
                }).render();
            }

            // ── Device donut ─────────────────────────────────────────────────────────
            var devEl = document.getElementById('deviceDonut');
            if (devEl && chartData.devices && chartData.devices.length) {
                new ApexCharts(devEl, {
                    chart: {
                        type: 'donut',
                        height: 170,
                        fontFamily: 'inherit'
                    },
                    series: chartData.devices.map(function(d) {
                        return d.total;
                    }),
                    labels: chartData.devices.map(function(d) {
                        var l = d.label || 'unknown';
                        return l.charAt(0).toUpperCase() + l.slice(1);
                    }),
                    colors: ['#245dac', '#198754', '#0dcaf0', '#ffc107', '#6c757d'],
                    dataLabels: {
                        enabled: false
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '65%'
                            }
                        }
                    },
                    legend: {
                        show: false
                    },
                    tooltip: {
                        theme: tooltipTheme
                    },
                }).render();
            }
        });
    })();
</script>
@endpush