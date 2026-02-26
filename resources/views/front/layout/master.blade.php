<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta http-equiv="content-language" content="en">
  <meta name="theme-color" content="#245dac">

  <title>{{ isset($page_title) ? $page_title . ' - ' . site_name() : site_name() }}</title>
  <meta name="description" content="{{ $page_description ?? site_description() }}">
  <meta name="keywords" content="{{ $page_keywords ?? '' }}">
  <meta name="author" content="{{ site_name() }}">


  <!-- CSRF Token -->
  <meta name="_token" content="{{ csrf_token() }}">
  <meta name="session-id" content="{{ session()->getId() }}">

  <!-- User Tracking Meta Tags -->
  @auth
  <meta name="user-id" content="{{ auth()->id() }}">
  <meta name="user-name" content="{{ auth()->user()->name }}">
  <meta name="user-email" content="{{ auth()->user()->email }}">
  <meta name="user-avatar" content="{{ auth()->user()->avatar ?? '' }}">
  @endauth

  <!-- Favicon -->
  <link rel="shortcut icon" href="{{ favicon_url() }}">
  <link rel="icon" type="image/x-icon" href="{{ favicon_url() }}">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">

  <link rel="shortcut icon" href="{{ favicon_url() }}">
  <link href="{{ asset('splash-screen.css') }}" rel="stylesheet" />

  <!-- color-modes:js -->
  @vite(['resources/js/pages/color-modes.js'])
  <script>
    (function() {
      const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-bs-theme', theme);
    })();
  </script>

  <!-- CSS -->
  @vite(['resources/sass/app.scss', 'resources/css/custom.css', 'resources/sass/frontend-accessibility.scss', 'resources/css/front.css'])

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

  <!-- Skip link for keyboard navigation -->
  <a href="#main-content" class="skip-link">Skip to main content</a>

  @include('front.layout.partials.header')
  <div class="main-wrapper d-block" id="app">
    <main id="main-content" class="w-100" tabindex="-1">
      @yield('content')
    </main>
  </div>
  @include('front.layout.partials.footer')

  <!-- base js -->
  @vite(['resources/js/app.js'])
  <script src="{{ asset('build/plugins/bootstrap/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('build/plugins/lucide/lucide.min.js') }}"></script>
  <!-- end base js -->

  <!-- plugin js -->
  @stack('plugin-scripts')
  <!-- end plugin js -->

  <!-- common js -->
  @vite(['resources/js/pages/template.js', 'resources/js/front.js'])
  <!-- end common js -->

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