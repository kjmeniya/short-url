<nav class="navbar navbar-expand-lg bg-body border-bottom sticky-top" role="navigation" aria-label="Main navigation">
  <div class="container">
    <!-- Logo (Left) -->
    <a class="navbar-brand h-60px d-flex align-items-center" href="{{ url('/') }}" aria-label="Go to {{ site_name() }} homepage">
      <img src="{{ logo_url('frontend', 'large', 'light') }}" class="logo logo-light object-fit-contain" alt="{{ site_name() }} - IP Geolocation Service Logo" width="120" height="30" fetchpriority="high">
      <img src="{{ logo_url('frontend', 'large', 'dark') }}" class="logo logo-dark object-fit-contain" alt="{{ site_name() }} - IP Geolocation Service Logo" width="120" height="30" fetchpriority="high">
    </a>

    <div class="d-flex align-items-center order-lg-last">
      <!-- Theme Switcher (Always visible) -->
      <div class="theme-switcher-wrapper nav-item d-flex align-items-center">
        <input type="checkbox" value="" id="theme-switcher">
        <label for="theme-switcher">
          <div class="box">
            <div class="ball"></div>
            <div class="icons">
              <i class="link-icon" data-lucide="sun"></i>
              <i class="link-icon" data-lucide="moon"></i>
            </div>
          </div>
        </label>
      </div>

      @auth
      <!-- User Dropdown -->
      <div class="dropdown">
        <a class="nav-link dropdown-toggle p-0 d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          @if(Auth::user()->hasAvatar())
          <img class="w-30px h-30px rounded-circle" src="{{ Auth::user()->avatar_url }}" alt="profile">
          @else
          <div class="w-30px h-30px rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="font-size: 12px; font-weight: 600;">
            {{ Auth::user()->initials }}
          </div>
          @endif
        </a>
        <div class="dropdown-menu dropdown-menu-end p-0" aria-labelledby="profileDropdown">
          <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
            <div class="mb-3">
              @if(Auth::user()->hasAvatar())
              <img class="w-80px h-80px rounded-circle" src="{{ Auth::user()->avatar_url }}" alt="">
              @else
              <div class="w-80px h-80px rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="font-size: 28px; font-weight: 600;">
                {{ Auth::user()->initials }}
              </div>
              @endif
            </div>
            <div class="text-center">
              <p class="fs-16px fw-bolder mb-1">{{ Auth::user()->name }}</p>
              <p class="fs-12px text-secondary mb-0">{{ Auth::user()->email }}</p>
              @if(Auth::user()->designation)
              <p class="fs-10px text-muted mb-0">{{ Auth::user()->designation }}</p>
              @endif
            </div>
          </div>
          <ul class="list-unstyled p-1 mb-0">
            <li>
              <a href="{{ route('admin.dashboard') }}" class="dropdown-item py-2 text-body ms-0">
                <i class="me-2 icon-md" data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
              </a>
            </li>
            <li>
              <a href="{{ route('admin.profile') }}" class="dropdown-item py-2 text-body ms-0">
                <i class="me-2 icon-md" data-lucide="user"></i>
                <span>Profile</span>
              </a>
            </li>
            <li>
              <a href="{{ route('admin.profile.edit') }}" class="dropdown-item py-2 text-body ms-0">
                <i class="me-2 icon-md" data-lucide="edit"></i>
                <span>Edit Profile</span>
              </a>
            </li>
            <li>
              <a href="javascript:;" class="dropdown-item py-2 text-body ms-0" onclick="event.preventDefault(); document.getElementById('front-logout-form').submit();">
                <i class="me-2 icon-md" data-lucide="log-out"></i>
                <span>Log Out</span>
              </a>
            </li>
          </ul>
          <form id="front-logout-form" method="POST" action="{{ route('auth.logout') }}" style="display: none;">
            @csrf
          </form>
        </div>
      </div>
      @endauth

      <!-- Mobile Toggle -->
      <button class="navbar-toggler border-0 p-0 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <i data-lucide="menu" class="icon"></i>
      </button>
    </div>
    <!-- Collapse Navigation -->
    <div class="collapse navbar-collapse border-top border-lg-0" id="navbarNav">
      <!-- Menu (Center) -->
      <ul class="navbar-nav mx-auto my-2 my-lg-0">
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('front.home') ? 'active' : '' }} d-flex align-items-center fs-6 fw-medium gap-2 gap-lg-1" href="{{ route('front.home') }}">
            <i data-lucide="globe" class="me-1 icon-sm"></i>Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center fs-6 fw-medium gap-2 gap-lg-1" href="#features">
            <i data-lucide="info" class="me-1 icon-sm"></i>Features
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center fs-6 fw-medium gap-2 gap-lg-1" href="#about">
            <i data-lucide="help-circle" class="me-1 icon-sm"></i>About
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link {{ request()->routeIs('front.blogs.*') ? 'active' : '' }} d-flex align-items-center fs-6 fw-medium gap-2 gap-lg-1" href="{{ route('front.blogs.index') }}">
            <i data-lucide="book-open" class="me-1 icon-sm"></i>Blog
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link d-flex align-items-center fs-6 fw-medium gap-2 gap-lg-1" href="#contact">
            <i data-lucide="mail" class="me-1 icon-sm"></i>Contact
          </a>
        </li>
        @guest
        <li class="nav-item d-lg-none">
          <a class="nav-link d-flex align-items-center fs-6 fw-medium gap-2 gap-lg-1" href="{{ route('auth.login') }}">
            <i data-lucide="log-in" class="me-1 icon-sm"></i>Login
          </a>
        </li>
        <li class="nav-item d-lg-none">
          <a class="nav-link d-flex align-items-center fs-6 fw-medium gap-2 gap-lg-1" href="{{ route('auth.register') }}">
            <i data-lucide="user-plus" class="me-1 icon-sm"></i>Register
          </a>
        </li>
        @endguest
      </ul>
      @guest
      <!-- Auth buttons for desktop -->
      <div class="d-none d-lg-flex gap-2 ms-3">
        <a href="{{ route('auth.login') }}" class="btn btn-outline-primary btn-sm">Login</a>
        <a href="{{ route('auth.register') }}" class="btn btn-primary btn-sm">Register</a>
      </div>
      @endguest
    </div>
  </div>
</nav>