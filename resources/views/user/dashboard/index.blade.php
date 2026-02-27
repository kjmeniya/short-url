@extends('user.layout.master')

@section('title', 'My Dashboard')

@push('plugin-styles')
<link href="{{ asset('build/plugins/apexcharts/apexcharts.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
  <div>
    <h4 class="mb-1">My Dashboard</h4>
    <p class="text-secondary mb-0">Welcome to your {{ site_name() }} link management portal.</p>
  </div>
  <div class="d-flex gap-2 mt-3 mt-md-0">
    <a href="{{ route('user.links') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
      <i data-lucide="plus" class="icon-sm"></i> New Link
    </a>
  </div>
</div>

{{-- Stats Row --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card h-100 border-0 shadow-sm rounded-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px fw-medium">Total Links</p>
            <h3 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h3>
          </div>
          <div class="p-2 bg-primary bg-opacity-10 rounded-3">
            <i data-lucide="link" class="icon-md text-primary"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100 border-0 shadow-sm rounded-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px fw-medium">Active Links</p>
            <h3 class="mb-0 fw-bold">{{ number_format($stats['active']) }}</h3>
          </div>
          <div class="p-2 bg-success bg-opacity-10 rounded-3">
            <i data-lucide="check-circle" class="icon-md text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100 border-0 shadow-sm rounded-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px fw-medium">Total Clicks</p>
            <h3 class="mb-0 fw-bold">{{ number_format($stats['total_clicks']) }}</h3>
          </div>
          <div class="p-2 bg-warning bg-opacity-10 rounded-3">
            <i data-lucide="mouse-pointer-click" class="icon-md text-warning"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100 border-0 shadow-sm rounded-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px fw-medium">New This Month</p>
            <h3 class="mb-0 fw-bold">{{ number_format($stats['this_month']) }}</h3>
          </div>
          <div class="p-2 bg-info bg-opacity-10 rounded-3">
            <i data-lucide="calendar" class="icon-md text-info"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  {{-- Chart --}}
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm rounded-3 h-100">
      <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
        <h6 class="card-title mb-0 fw-bold">Clicks Performance (Last 7 Days)</h6>
      </div>
      <div class="card-body p-4">
        <div id="clicksChart" style="height: 300px;"></div>
      </div>
    </div>
  </div>

  {{-- Recent Links --}}
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm rounded-3 h-100">
      <div class="card-header bg-transparent border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0 fw-bold">Recently Created Links</h6>
        <a href="{{ route('user.links') }}" class="btn btn-sm btn-light">View All</a>
      </div>
      <div class="card-body p-4 pt-1">
        @if($recentLinks->isEmpty())
        <div class="text-center py-5">
          <i data-lucide="link" class="text-muted opacity-50 mb-3" style="width:40px;height:40px;"></i>
          <p class="text-muted">You haven't created any links yet.</p>
          <a href="{{ route('user.links') }}" class="btn btn-sm btn-primary mt-2">Create Your First Link</a>
        </div>
        @else
        <div class="d-flex flex-column gap-3 mt-3">
          @foreach($recentLinks as $link)
          <div class="d-flex justify-content-between align-items-center p-3 rounded-3 bg-light bg-opacity-50">
            <div class="overflow-hidden me-3" style="max-width: 70%;">
              <p class="mb-1 fw-semibold text-primary text-truncate">
                <a href="{{ $link->short_url }}" target="_blank" class="text-decoration-none">
                  {{ rtrim(url('/'), '/') }}/{{ $link->custom_alias ?: $link->code }}
                </a>
              </p>
              <p class="mb-0 text-muted small text-truncate" title="{{ $link->original_url }}">
                {{ $link->original_url }}
              </p>
            </div>
            <div class="text-end flex-shrink-0">
              <span class="badge bg-secondary bg-opacity-10 text-secondary mb-1">
                {{ number_format($link->clicks) }} clicks
              </span>
              <br>
              <span class="small text-muted" style="font-size: .7rem;">{{ $link->created_at->diffForHumans() }}</span>
            </div>
          </div>
          @endforeach
        </div>
        @endif
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
    const chartData = @json($chartData);

    if (document.querySelector('#clicksChart') && chartData.labels.length > 0) {
      const options = {
        series: [{
          name: 'Clicks',
          data: chartData.clicks
        }],
        chart: {
          type: 'area',
          height: 300,
          toolbar: {
            show: false
          },
          fontFamily: 'inherit'
        },
        colors: ['#245dac'], // Primary color
        fill: {
          type: 'gradient',
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.4,
            opacityTo: 0.05,
            stops: [0, 90, 100]
          }
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          curve: 'smooth',
          width: 2
        },
        xaxis: {
          categories: chartData.labels,
          axisBorder: {
            show: false
          },
          axisTicks: {
            show: false
          }
        },
        yaxis: {
          labels: {
            formatter: val => Math.round(val)
          }
        },
        grid: {
          borderColor: 'rgba(0,0,0,0.05)',
          strokeDashArray: 4,
        }
      };

      new ApexCharts(document.querySelector("#clicksChart"), options).render();
    }
  });
</script>
@endpush