<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>@yield('title', 'Admin Dashboard') | {{ site_name() }}</title>
  <meta name="description" content="@yield('description', 'Admin panel for managing users, roles, permissions and system settings.')">
  <meta name="keywords" content="@yield('keywords', 'admin, dashboard, management, users, roles, permissions')">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
  <link rel="canonical" href="{{ url()->current() }}">
  <meta name="_token" content="{{ csrf_token() }}">
  <meta name="session-id" content="{{ session()->getId() }}">

  <!-- User Tracking Meta Tags -->
  @auth
  <meta name="user-id" content="{{ auth()->id() }}">
  <meta name="user-name" content="{{ auth()->user()->name }}">
  <meta name="user-email" content="{{ auth()->user()->email }}">
  <meta name="user-avatar" content="{{ auth()->user()->avatar ?? '' }}">
  @endauth

  <link rel="shortcut icon" href="{{ favicon_url() }}">
  <link href="{{ asset('splash-screen.css') }}" rel="stylesheet" />
  <link href="{{ asset('build/plugins/perfect-scrollbar/perfect-scrollbar.css') }}" rel="stylesheet" />
  <link href="{{ asset('build/plugins/flag-icons/css/flag-icons.min.css') }}" rel="stylesheet" />
  @vite(['resources/js/pages/color-modes.js'])
  <script>
    (function() {
      // Get admin default theme from settings
      const adminDefaultTheme = '{{ app(\App\Services\SettingsService::class)->get("admin_default_theme", "light") }}';

      // Make admin default theme and timezone available globally
      window.adminDefaultTheme = adminDefaultTheme;
      <?php

      use Illuminate\Support\Facades\Auth;

      $tz = Auth::check() && Auth::user()->timezone ? Auth::user()->timezone : timezone_setting();
      ?>
      window.appTimezone = @json($tz);

      // Priority: localStorage > admin setting > system preference
      let theme = localStorage.getItem('theme');

      if (!theme) {
        if (adminDefaultTheme === 'auto') {
          theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        } else {
          theme = adminDefaultTheme;
        }
        localStorage.setItem('theme', theme);
      }

      document.documentElement.setAttribute('data-bs-theme', theme);
    })();
  </script>
  @stack('plugin-styles')
  @vite(['resources/sass/app.scss', 'resources/css/custom.css'])
  <link href="{{ route('theme.css') }}" rel="stylesheet" />
  @stack('style')
</head>

<body data-base-url="{{url('/')}}">
  <script>
    var splash = document.createElement("div");
    splash.innerHTML = `
      <div class="splash-screen">
        <div class="logo"></div>
        <div class="spinner"></div>
      </div>`;
    document.body.insertBefore(splash, document.body.firstChild);
    document.addEventListener("DOMContentLoaded", function() {
      document.body.classList.add("loaded");
    });
  </script>
  <div class="main-wrapper" id="app">
    @include('admin.layout.partials.sidebar')
    <div class="page-wrapper">
      @include('admin.layout.partials.header')
      <div class="page-content container-xxl">
        @yield('content')
      </div>
      @include('admin.layout.partials.footer')
    </div>
  </div>

  <!-- base js -->
  @vite(['resources/js/app.js'])
  <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('build/plugins/bootstrap/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('build/plugins/lucide/lucide.min.js') }}"></script>
  <script src="{{ asset('build/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
  <!-- end base js -->

  <!-- plugin js -->
  @stack('plugin-scripts')
  <!-- end plugin js -->

  <!-- common js -->
  @vite(['resources/js/pages/template.js'])
  <!-- end common js -->

  <!-- password utilities -->
  @vite(['resources/js/admin/password-utils.js'])
  <!-- end password utilities -->

  <!-- global search -->
  @vite(['resources/js/admin/global-search.js'])
  <!-- end global search -->

  <!-- Lucide Icons Global Loader -->
  <script>
    // Global Lucide icon loader
    function ensureLucideIcons() {
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      } else {
        // Fallback: try to load lucide if not available
        setTimeout(ensureLucideIcons, 100);
      }
    }

    // Initialize on DOM ready
    $(document).ready(function() {
      ensureLucideIcons();

      // Re-initialize icons after any AJAX content changes
      $(document).ajaxComplete(function() {
        setTimeout(ensureLucideIcons, 50);
      });

      // Re-initialize icons after modal shows
      $('.modal').on('shown.bs.modal', function() {
        setTimeout(ensureLucideIcons, 50);
      });

      // Re-initialize icons after tab changes
      $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
        setTimeout(ensureLucideIcons, 50);
      });

      // Re-initialize icons after collapse changes
      $('.collapse').on('shown.bs.collapse hidden.bs.collapse', function() {
        setTimeout(ensureLucideIcons, 50);
      });
    });

    // Global SweetAlert default configuration
    if (typeof Swal !== 'undefined') {
      Swal.mixin({
        customClass: {
          confirmButton: 'btn btn-sm btn-primary',
          cancelButton: 'btn btn-sm btn-secondary'
        },
        buttonsStyling: false,
        didOpen: function() {
          ensureLucideIcons();
        }
      });
    }
  </script>
  <!-- end Lucide Icons Global Loader -->

  <!-- Socket.IO Live User Tracking -->
  @if(socket_enabled())
  <script src="{{ asset('build/plugins/socket.io/socket.io.min.js') }}"></script>
  <script>
    window.SOCKET_SERVER_URL = '{{ socket_server_url() }}';
    window.SOCKET_CONFIG = @json(socket_config());
  </script>
  @vite(['resources/js/socket-client.js'])
  @endif
  <!-- end Socket.IO -->

  @canAccess('admin.analytics.live')
  <!-- Update Header Live Count -->
  <script>
    $(document).ready(function() {
      // Update header live count when socket data updates
      if (window.SocketManager) {
        // Listen for users update
        window.SocketManager.on('users:update', function(data) {
          $('#header-live-count').text(data.total || 0);
          $('#header-stat-total').text(data.total || 0);
          $('#header-stat-authenticated').text(data.authenticated || 0);
          $('#header-stat-guest').text(data.guest || 0);
          $('#header-stat-web').text(data.web || 0);
          $('#header-stat-admin').text(data.admin || 0);
          $('#header-stat-app').text(data.app || 0);
          $('#header-stat-desktop').text(data.desktop || 0);
          $('#header-stat-mobile').text(data.mobile || 0);
          $('#header-stat-tablet').text(data.tablet || 0);
        });
      }
    });
  </script>
  <!-- end Update Header Live Count -->
  @endcanAccess

  @stack('custom-scripts')

  <!-- Notification System -->
  <script src="{{ asset('js/notifications.js') }}"></script>
</body>

</html>