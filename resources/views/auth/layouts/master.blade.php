<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  @php
  $seo = seo_data(
  $page_title ?? 'Login',
  $page_description ?? 'Secure login page for accessing your dashboard.',
  $page_keywords ?? 'login, user login, secure login, authentication, dashboard access'
  );
  @endphp

  <meta name="description" content="{{ $seo['description'] }}">
  <meta name="author" content="{{ $seo['site_name'] }}">
  <meta name="robots" content="noindex, nofollow">
  <meta name="keywords" content="{{ $seo['keywords'] }}">

  <title>{{ $seo['title'] }}</title>

  {{-- Schema Markup --}}
  @if(isset($schema_type))
  {!! schema_markup($schema_type, $schema_data ?? []) !!}
  @else
  {!! schema_markup('login') !!}
  @endif

  <!-- color-modes:js -->
  @vite(['resources/js/pages/color-modes.js'])
  <script>
    (function() {
      const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-bs-theme', theme);
    })();
  </script>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
  <!-- End fonts -->

  <!-- CSRF Token -->
  <meta name="_token" content="{{ csrf_token() }}">

  <!-- User Tracking Meta Tags -->
  @auth
  <meta name="user-id" content="{{ auth()->id() }}">
  <meta name="user-name" content="{{ auth()->user()->name }}">
  <meta name="user-email" content="{{ auth()->user()->email }}">
  <meta name="user-avatar" content="{{ auth()->user()->avatar ?? '' }}">
  @endauth

  <link rel="shortcut icon" href="{{ favicon_url() }}">

  <!-- Splash Screen -->
  <link href="{{ asset('splash-screen.css') }}" rel="stylesheet" />

  <!-- plugin css -->
  <link href="{{ asset('build/plugins/perfect-scrollbar/perfect-scrollbar.css') }}" rel="stylesheet" />

  @stack('plugin-styles')

  <!-- CSS for LTR layout-->
  @vite(['resources/sass/app.scss', 'resources/css/custom.css', 'resources/sass/frontend-accessibility.scss'])

  @stack('style')
</head>

<body data-base-url="{{url('/')}}">

  <script>
    // Create splash screen container
    var splash = document.createElement("div");
    splash.innerHTML = `
      <div class="splash-screen">
        <div class="logo"></div>
        <div class="spinner"></div>
      </div>`;

    // Insert splash screen as the first child of the body
    document.body.insertBefore(splash, document.body.firstChild);

    // Add 'loaded' class to body once DOM is fully loaded
    document.addEventListener("DOMContentLoaded", function() {
      document.body.classList.add("loaded");
    });
  </script>

  <!-- Skip link for keyboard navigation -->
  <a href="#main-content" class="skip-link">Skip to main content</a>

  <div class="main-wrapper" id="app">
    <div class="page-wrapper full-page">
      <main class="page-content container-xxl d-flex align-items-center justify-content-center" tabindex="-1">
        @yield('content')
      </main>
    </div>
  </div>

  <!-- base js -->
  @vite(['resources/js/app.js'])
  <script src="{{ asset('build/plugins/bootstrap/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('build/plugins/lucide/lucide.min.js') }}"></script>
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

  @stack('custom-scripts')
</body>

</html>