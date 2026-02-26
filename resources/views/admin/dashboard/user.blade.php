@extends('admin.layout.master')

@section('title', 'My Dashboard')

@push('plugin-styles')
<link href="{{ asset('build/plugins/flag-icons/css/flag-icons.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
  <div>
    <h4 class="mb-1">My Dashboard</h4>
    <p class="text-secondary mb-0">Welcome back, {{ Auth::user()->name }}</p>
  </div>
  <div class="d-flex gap-2">
    <a href="#" class="btn btn-primary btn-sm">
      <i data-lucide="search" class="icon-sm me-1"></i>IP Lookup
    </a>
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.reload()">
      <i data-lucide="refresh-cw" class="icon-sm me-1"></i>Refresh
    </button>
  </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px">Total Lookups</p>
            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
          </div>
          <div class="p-2 bg-primary bg-opacity-10 rounded">
            <i data-lucide="globe" class="icon-md text-primary"></i>
          </div>
        </div>
        <p class="text-muted mb-0 mt-2 fs-13px">
          <i data-lucide="hash" class="icon-xs me-1"></i>{{ number_format($stats['unique_ips']) }} unique IPs
        </p>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px">Today</p>
            <h3 class="mb-0">{{ number_format($stats['today']) }}</h3>
          </div>
          <div class="p-2 bg-success bg-opacity-10 rounded">
            <i data-lucide="calendar" class="icon-md text-success"></i>
          </div>
        </div>
        <p class="text-success mb-0 mt-2 fs-13px">
          <i data-lucide="trending-up" class="icon-xs me-1"></i>{{ $stats['success_rate'] }}% success
        </p>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px">This Week</p>
            <h3 class="mb-0">{{ number_format($stats['this_week']) }}</h3>
          </div>
          <div class="p-2 bg-info bg-opacity-10 rounded">
            <i data-lucide="bar-chart-2" class="icon-md text-info"></i>
          </div>
        </div>
        <p class="text-info mb-0 mt-2 fs-13px">
          <i data-lucide="zap" class="icon-xs me-1"></i>{{ $stats['cache_hit_rate'] }}% cached
        </p>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px">This Month</p>
            <h3 class="mb-0">{{ number_format($stats['this_month']) }}</h3>
          </div>
          <div class="p-2 bg-warning bg-opacity-10 rounded">
            <i data-lucide="clock" class="icon-md text-warning"></i>
          </div>
        </div>
        <p class="text-warning mb-0 mt-2 fs-13px">
          <i data-lucide="timer" class="icon-xs me-1"></i>{{ $stats['avg_response_time'] }}ms avg
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
  <!-- Lookup Activity Chart -->
  <div class="col-12 col-lg-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i data-lucide="activity" class="icon-sm me-2"></i>Lookup Activity
        </h5>
        <div class="btn-group btn-group-sm" role="group" id="lookupChartPeriod">
          <button type="button" class="btn btn-outline-primary" data-period="today">Today</button>
          <button type="button" class="btn btn-outline-primary active" data-period="week">Week</button>
          <button type="button" class="btn btn-outline-primary" data-period="month">Month</button>
        </div>
      </div>
      <div class="card-body">
        <div id="lookupActivityChart" style="height: 300px;"></div>
      </div>
    </div>
  </div>

  <!-- Request Type Distribution -->
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i data-lucide="pie-chart" class="icon-sm me-2"></i>Request Types
        </h5>
      </div>
      <div class="card-body">
        <div id="requestTypeChart" style="height: 250px;"></div>
        <div class="row text-center mt-3">
          <div class="col-4">
            <h5 class="mb-0">{{ number_format($stats['single_lookups']) }}</h5>
            <small class="text-muted">Single</small>
          </div>
          <div class="col-4">
            <h5 class="mb-0">{{ number_format($stats['bulk_lookups']) }}</h5>
            <small class="text-muted">Bulk</small>
          </div>
          <div class="col-4">
            <h5 class="mb-0">{{ number_format($stats['successful']) }}</h5>
            <small class="text-muted">Success</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Status Overview & Recent Lookups -->
<div class="row g-3 mb-4">
  <!-- Status Distribution -->
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title mb-0">
          <i data-lucide="check-circle" class="icon-sm me-2"></i>Status Overview
        </h5>
      </div>
      <div class="card-body">
        <div id="statusChart" style="height: 200px;"></div>
        <hr>
        <div class="row text-center">
          <div class="col-6 mb-2">
            <span class="badge bg-success">{{ number_format($stats['successful']) }}</span>
            <small class="d-block text-muted">Successful</small>
          </div>
          <div class="col-6 mb-2">
            <span class="badge bg-danger">{{ number_format($stats['failed']) }}</span>
            <small class="d-block text-muted">Failed</small>
          </div>
          <div class="col-6">
            <span class="badge bg-info">{{ number_format($stats['cached']) }}</span>
            <small class="d-block text-muted">Cached</small>
          </div>
          <div class="col-6">
            <span class="badge bg-secondary">{{ number_format($stats['unique_ips']) }}</span>
            <small class="d-block text-muted">Unique IPs</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Lookups -->
  <div class="col-12 col-lg-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i data-lucide="history" class="icon-sm me-2"></i>Recent Lookups
        </h5>
        <a href="#" class="btn btn-sm btn-outline-primary">
          View All <i data-lucide="arrow-right" class="icon-xs ms-1"></i>
        </a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th>IP Address</th>
                <th>Location</th>
                <th>Type</th>
                <th>Status</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentLookups as $lookup)
              <tr>
                <td><code>{{ Str::limit($lookup->lookup_ip, 20) }}</code></td>
                <td>
                  @if($lookup->country)
                  <span class="fi fi-{{ strtolower($lookup->country_code ?? 'xx') }} me-1"></span>
                  {{ $lookup->city ? $lookup->city . ', ' : '' }}{{ $lookup->country }}
                  @else
                  <span class="text-muted">Unknown</span>
                  @endif
                </td>
                <td>{!! $lookup->request_type_badge !!}</td>
                <td>{!! $lookup->status_badge !!}</td>
                <td>
                  <small class="text-muted">{{ $lookup->created_at->diffForHumans() }}</small>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                  <i data-lucide="inbox" class="icon-lg mb-2"></i>
                  <p class="mb-0">No lookups yet. <a href="#">Start looking up IPs</a></p>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions -->
<div class="row g-3">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
          <a href="#" class="btn btn-primary btn-sm">
            <i data-lucide="search" class="icon-sm me-1"></i>IP Lookup
          </a>
          <a href="#" class="btn btn-info btn-sm">
            <i data-lucide="list" class="icon-sm me-1"></i>View All Logs
          </a>
          <a href="{{ route('admin.profile') }}" class="btn btn-secondary btn-sm">
            <i data-lucide="user" class="icon-sm me-1"></i>My Profile
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/apexcharts/apexcharts.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Chart data from controller
    const lookupChartData = @json($chartData['lookup_chart']);
    const requestTypeData = @json($chartData['request_type_distribution']);
    const statusData = @json($chartData['status_distribution']);

    // Initialize charts
    initLookupActivityChart(lookupChartData);
    initRequestTypeChart(requestTypeData);
    initStatusChart(statusData);
    initPeriodButtons();
  });

  let lookupChart = null;

  function initLookupActivityChart(data) {
    const options = {
      series: [{
        name: 'Successful',
        data: data.success
      }, {
        name: 'Failed',
        data: data.failed
      }],
      chart: {
        type: 'area',
        height: 300,
        stacked: false,
        toolbar: {
          show: false
        },
        zoom: {
          enabled: false
        }
      },
      colors: ['#28a745', '#dc3545'],
      dataLabels: {
        enabled: false
      },
      stroke: {
        curve: 'smooth',
        width: 2
      },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.4,
          opacityTo: 0.1,
        }
      },
      xaxis: {
        categories: data.labels,
        labels: {
          style: {
            fontSize: '11px'
          }
        }
      },
      yaxis: {
        labels: {
          formatter: val => Math.round(val)
        }
      },
      legend: {
        position: 'top'
      },
      tooltip: {
        shared: true
      }
    };

    lookupChart = new ApexCharts(document.querySelector("#lookupActivityChart"), options);
    lookupChart.render();
  }

  function initRequestTypeChart(data) {
    const options = {
      series: data.data,
      chart: {
        type: 'donut',
        height: 250
      },
      labels: data.labels,
      colors: ['#0d6efd', '#6f42c1', '#20c997'],
      legend: {
        position: 'bottom'
      },
      plotOptions: {
        pie: {
          donut: {
            size: '60%',
            labels: {
              show: true,
              total: {
                show: true,
                label: 'Total',
                formatter: function(w) {
                  return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                }
              }
            }
          }
        }
      }
    };

    new ApexCharts(document.querySelector("#requestTypeChart"), options).render();
  }

  function initStatusChart(data) {
    const options = {
      series: data.data,
      chart: {
        type: 'pie',
        height: 200
      },
      labels: data.labels,
      colors: ['#28a745', '#dc3545', '#ffc107', '#6c757d'],
      legend: {
        show: false
      }
    };

    new ApexCharts(document.querySelector("#statusChart"), options).render();
  }

  function initPeriodButtons() {
    document.querySelectorAll('#lookupChartPeriod button').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('#lookupChartPeriod button').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        updateLookupChart(this.dataset.period);
      });
    });
  }

  function updateLookupChart(period) {
    fetch(`{{ route('admin.dashboard.refresh') }}?type=lookup_chart&period=${period}`)
      .then(response => response.json())
      .then(result => {
        if (result.success && lookupChart) {
          lookupChart.updateOptions({
            xaxis: {
              categories: result.data.lookup_chart.labels
            }
          });
          lookupChart.updateSeries([{
              name: 'Successful',
              data: result.data.lookup_chart.success
            },
            {
              name: 'Failed',
              data: result.data.lookup_chart.failed
            }
          ]);
        }
      });
  }
</script>
@endpush