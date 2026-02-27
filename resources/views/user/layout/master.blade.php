<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>@yield('title', 'My Dashboard') | {{ site_name() }}</title>
  <meta name="description" content="@yield('description', 'Manage your shortened links and track performance.')">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="_token" content="{{ csrf_token() }}">

  @auth
  <meta name="user-id" content="{{ auth()->id() }}">
  <meta name="user-name" content="{{ auth()->user()->name }}">
  <meta name="user-email" content="{{ auth()->user()->email }}">
  @endauth

  <link rel="shortcut icon" href="{{ favicon_url() }}">
  <link href="{{ asset('splash-screen.css') }}" rel="stylesheet" />
  <link href="{{ asset('build/plugins/perfect-scrollbar/perfect-scrollbar.css') }}" rel="stylesheet" />

  <!-- color-modes -->
  @vite(['resources/js/pages/color-modes.js'])
  <script>
    (function() {
      const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-bs-theme', theme);
    })();
  </script>

  @stack('plugin-styles')
  @vite(['resources/sass/app.scss', 'resources/css/custom.css'])
  <link href="{{ route('theme.css') }}" rel="stylesheet" />
  @stack('style')
</head>

<body data-base-url="{{ url('/') }}">
  <script>
    var splash = document.createElement("div");
    splash.innerHTML = `<div class="splash-screen"><div class="logo"></div><div class="spinner"></div></div>`;
    document.body.insertBefore(splash, document.body.firstChild);
    document.addEventListener("DOMContentLoaded", function() {
      document.body.classList.add("loaded");
    });
  </script>

  <div class="main-wrapper" id="app">

    {{-- Sidebar --}}
    @include('user.layout.partials.sidebar')

    <div class="page-wrapper">
      {{-- Header --}}
      @include('user.layout.partials.header')

      <div class="page-content container-xxl">
        @yield('content')
      </div>

      {{-- Footer --}}
      @include('user.layout.partials.footer')
    </div>
  </div>

  <!-- base js -->
  @vite(['resources/js/app.js'])
  <script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('build/plugins/bootstrap/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('build/plugins/lucide/lucide.min.js') }}"></script>
  <script src="{{ asset('build/plugins/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
  <!-- end base js -->

  @stack('plugin-scripts')

  @vite(['resources/js/pages/template.js'])
  @vite(['resources/js/admin/password-utils.js'])

  <script>
    $(document).ready(function() {
      if (typeof lucide !== 'undefined') lucide.createIcons();

      $(document).ajaxComplete(function() {
        setTimeout(function() {
          if (typeof lucide !== 'undefined') lucide.createIcons();
        }, 50);
      });
    });
  </script>

  @stack('custom-scripts')
</body>

</html>
