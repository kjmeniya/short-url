@extends('admin.layout.master')

@section('title', 'Dashboard')

@push('plugin-styles')
<style>
  .pulse-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #198754;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0% {
      box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.7);
    }

    70% {
      box-shadow: 0 0 0 10px rgba(25, 135, 84, 0);
    }

    100% {
      box-shadow: 0 0 0 0 rgba(25, 135, 84, 0);
    }
  }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
  <div>
    <h4 class="mb-1">Dashboard</h4>
    <p class="text-secondary mb-0">Welcome back, {{ Auth::user()->name }}</p>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.reload()">
      <i data-lucide="refresh-cw" class="icon-sm me-1"></i>Refresh
    </button>
  </div>
</div>

<!-- Quick Actions -->
<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-body py-3">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
          <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            <i data-lucide="user-plus" class="icon-sm me-1"></i>Add User
          </a>
          <a href="{{ route('admin.roles.create') }}" class="btn btn-success btn-sm">
            <i data-lucide="shield-plus" class="icon-sm me-1"></i>Add Role
          </a>
          <a href="{{ route('admin.email-templates.index') }}" class="btn btn-info btn-sm">
            <i data-lucide="mail" class="icon-sm me-1"></i>Email Templates
          </a>
          <a href="{{ route('admin.laravel-logs.index') }}" class="btn btn-warning btn-sm">
            <i data-lucide="file-text" class="icon-sm me-1"></i>System Logs
          </a>
          <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary btn-sm">
            <i data-lucide="settings" class="icon-sm me-1"></i>Settings
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Live Users Stats -->
@canAccess('admin.analytics.live')
<div class="row g-3 mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header bg-transparent border-0 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h6 class="card-title mb-1">
              <i data-lucide="activity" class="icon-sm me-2"></i>Live Users
              <span class="pulse-dot ms-2"></span>
            </h6>
            <p class="text-secondary fs-13px mb-0">Real-time user activity</p>
          </div>
          <a href="{{ route('admin.analytics.live') }}" class="btn btn-outline-primary btn-sm">
            <i data-lucide="external-link" class="icon-sm me-1"></i>View Details
          </a>
        </div>
      </div>
      <div class="card-body py-2">
        <div class="row">
          <div class="col-12">
            <div id="liveUsersChart" style="height: 300px;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endcanAccess

<!-- Primary Stats Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px">Total Users</p>
            <h3 class="mb-0">{{ number_format($userStats['total']) }}</h3>
          </div>
          <div class="p-2 bg-primary bg-opacity-10 rounded">
            <i data-lucide="users" class="icon-md text-primary"></i>
          </div>
        </div>
        <p class="text-success mb-0 mt-2 fs-13px">
          <i data-lucide="user-check" class="icon-xs me-1"></i>{{ number_format($userStats['active']) }} active
        </p>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px">Login Today</p>
            <h3 class="mb-0">{{ number_format($loginStats['today']) }}</h3>
          </div>
          <div class="p-2 bg-success bg-opacity-10 rounded">
            <i data-lucide="log-in" class="icon-md text-success"></i>
          </div>
        </div>
        <p class="text-success mb-0 mt-2 fs-13px">
          <i data-lucide="trending-up" class="icon-xs me-1"></i>{{ $loginStats['success_rate'] }}% success rate
        </p>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px">Emails Sent</p>
            <h3 class="mb-0">{{ number_format($emailStats['total']) }}</h3>
          </div>
          <div class="p-2 bg-info bg-opacity-10 rounded">
            <i data-lucide="mail" class="icon-md text-info"></i>
          </div>
        </div>
        <p class="text-info mb-0 mt-2 fs-13px">
          <i data-lucide="send" class="icon-xs me-1"></i>{{ number_format($emailStats['today']) }} today
        </p>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="text-secondary mb-1 fs-13px">Roles & Permissions</p>
            <h3 class="mb-0">{{ number_format($systemStats['roles']) }}</h3>
          </div>
          <div class="p-2 bg-warning bg-opacity-10 rounded">
            <i data-lucide="shield" class="icon-md text-warning"></i>
          </div>
        </div>
        <p class="text-warning mb-0 mt-2 fs-13px">
          <i data-lucide="key" class="icon-xs me-1"></i>{{ number_format($systemStats['permissions']) }} permissions
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
  <!-- User Growth Chart -->
  <div class="col-12">
    <div class="card h-100">
      <div class="card-header bg-transparent border-0 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h6 class="card-title mb-1">User Growth</h6>
            <p class="text-secondary fs-13px mb-0">Registration trends over time</p>
          </div>
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-primary chart-period-btn" data-period="today">Today</button>
            <button type="button" class="btn btn-outline-primary chart-period-btn d-none d-sm-inline-block" data-period="week">Week</button>
            <button type="button" class="btn btn-primary chart-period-btn" data-period="month">Month</button>
            <button type="button" class="btn btn-outline-primary chart-period-btn" data-period="year">Year</button>
          </div>
        </div>
      </div>
      <div class="card-body pt-2">
        <div id="userGrowthChart"></div>
      </div>
    </div>
  </div>
</div>

<!-- Secondary Stats Row -->
<div class="row g-3 mb-4">
  <!-- Login Statistics -->
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header bg-transparent border-0 pb-0">
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="card-title mb-0">Login Statistics</h6>
          <a href="{{ route('admin.login-logs.index') }}" class="btn btn-link btn-sm p-0">View Logs</a>
        </div>
      </div>
      <div class="card-body pt-2">
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">Today</span>
          <span class="fw-semibold">{{ number_format($loginStats['today']) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">This Week</span>
          <span class="fw-semibold">{{ number_format($loginStats['this_week']) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">Successful</span>
          <span class="fw-semibold text-success">{{ number_format($loginStats['successful']) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">Failed</span>
          <span class="fw-semibold text-danger">{{ number_format($loginStats['failed']) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2">
          <span class="text-secondary">Suspicious</span>
          <span class="fw-semibold text-warning">{{ number_format($loginStats['suspicious']) }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Email Statistics -->
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header bg-transparent border-0 pb-0">
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="card-title mb-0">Email Statistics</h6>
          <a href="{{ route('admin.email-logs.index') }}" class="btn btn-link btn-sm p-0">View Logs</a>
        </div>
      </div>
      <div class="card-body pt-2">
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">Total Sent</span>
          <span class="fw-semibold">{{ number_format($emailStats['total']) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">Today</span>
          <span class="fw-semibold">{{ number_format($emailStats['today']) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">Delivered</span>
          <span class="fw-semibold text-success">{{ number_format($emailStats['sent']) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">Failed</span>
          <span class="fw-semibold text-danger">{{ number_format($emailStats['failed']) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2">
          <span class="text-secondary">Active Templates</span>
          <span class="fw-semibold">{{ number_format($emailStats['templates']) }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- System & Storage -->
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-header bg-transparent border-0 pb-0">
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="card-title mb-0">System Overview</h6>
          <a href="{{ route('admin.settings.index') }}" class="btn btn-link btn-sm p-0">Settings</a>
        </div>
      </div>
      <div class="card-body pt-2">
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">Roles</span>
          <span class="fw-semibold">{{ number_format($systemStats['roles']) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">Permissions</span>
          <span class="fw-semibold">{{ number_format($systemStats['permissions']) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">Email Templates</span>
          <span class="fw-semibold">{{ number_format($systemStats['email_templates']) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <span class="text-secondary">Database Size</span>
          <span class="fw-semibold">{{ $systemStats['database_size'] }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2">
          <span class="text-secondary">Storage Used</span>
          <span class="fw-semibold">{{ $systemStats['storage_usage']['used'] ?? 'N/A' }}</span>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <!-- Recent Activity -->
  <div class="col-12">
    <div class="card h-100">
      <div class="card-header bg-transparent">
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="card-title mb-0">Recent Activity</h6>
          <a href="{{ route('admin.login-logs.index') }}" class="btn btn-link btn-sm p-0 text-primary">View All</a>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="activity-list overflow-y-auto p-3" style="max-height: 300px;">
          @forelse($recentActivity as $index => $activity)
          <div class="d-flex align-items-center py-2 border-bottom @if($index === 0) border-top @endif">
            <div class="p-2 bg-{{ $activity['color'] }} bg-opacity-10 rounded me-2 flex-shrink-0">
              <i data-lucide="{{ $activity['icon'] }}" class="icon-sm text-{{ $activity['color'] }}"></i>
            </div>
            <div class="flex-grow-1 min-width-0">
              <p class="mb-0 fw-medium text-truncate">{{ $activity['user'] }}</p>
              <p class="mb-0 text-secondary fs-12px">{{ $activity['action'] }}</p>
            </div>
            <p class="mb-0 text-muted fs-11px">{{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}</p>
          </div>
          @empty
          <p class="text-secondary text-center py-3 mb-0">No recent activity</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/apexcharts/apexcharts.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
@if(socket_enabled())
@vite(['resources/js/admin/live.js'])
@endif
<script>
  const chartData = @json($chartData);

  $(document).ready(function() {
    initUserGrowthChart();
    initChartPeriodButtons();

    @if(socket_enabled())
    // Initialize live users chart using reusable function
    if (typeof initLiveUsersChart === 'function') {
      window.dashboardLiveChart = initLiveUsersChart('liveUsersChart', {
        maxPoints: 60
      });
    }
    @endif
  });

  function initUserGrowthChart() {
    const el = document.querySelector('#userGrowthChart');
    if (!el) return;

    const options = {
      chart: {
        type: 'area',
        height: 300,
        fontFamily: 'inherit',
        toolbar: {
          show: false
        },
        zoom: {
          enabled: false
        }
      },
      colors: ['#245dac'],
      stroke: {
        curve: 'smooth',
        width: 2
      },
      dataLabels: {
        enabled: false
      },
      series: [{
        name: 'New Users',
        data: chartData.user_growth.data
      }],
      xaxis: {
        categories: chartData.user_growth.labels,
        labels: {
          style: {
            fontSize: '11px'
          }
        }
      },
      yaxis: {
        min: 0
      },
      grid: {
        borderColor: '#e9ecef'
      },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.5,
          opacityTo: 0.1,
          stops: [0, 90, 100]
        }
      },
      tooltip: {
        theme: 'dark'
      }
    };

    window.userGrowthChart = new ApexCharts(el, options);
    window.userGrowthChart.render();
  }

  function initChartPeriodButtons() {
    $('.chart-period-btn').on('click', function() {
      const $btn = $(this);
      const period = $btn.data('period');

      $('.chart-period-btn').removeClass('btn-primary').addClass('btn-outline-primary');
      $btn.removeClass('btn-outline-primary').addClass('btn-primary');

      updateUserGrowthChart(period);
    });
  }

  function updateUserGrowthChart(period) {
    $.ajax({
      url: "{{ route('admin.dashboard.refresh') }}",
      method: 'GET',
      data: {
        type: 'user_growth',
        period: period
      },
      success: function(response) {
        if (response.success && response.data.user_growth && window.userGrowthChart) {
          window.userGrowthChart.updateSeries([{
            name: 'New Users',
            data: response.data.user_growth.data
          }]);
          window.userGrowthChart.updateOptions({
            xaxis: {
              categories: response.data.user_growth.labels
            }
          });
        }
      },
      error: function() {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Failed to update chart.',
          timer: 3000,
          showConfirmButton: false
        });
      }
    });
  }
</script>
@endpush