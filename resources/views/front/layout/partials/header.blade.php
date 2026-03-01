<nav class="front-navbar sticky-top" role="navigation" aria-label="Main navigation" id="frontNavbar">
  <div class="container">
    <div class="front-navbar-inner">

      {{-- ── Logo ── --}}
      <a class="front-navbar-brand" href="{{ url('/') }}" aria-label="{{ site_name() }} homepage">
        <img src="{{ logo_url('frontend', 'large', 'light') }}" class="logo logo-light object-fit-contain" alt="{{ site_name() }}" width="120" height="30" fetchpriority="high">
        <img src="{{ logo_url('frontend', 'large', 'dark') }}" class="logo logo-dark object-fit-contain" alt="{{ site_name() }}" width="120" height="30" fetchpriority="high">
      </a>

      {{-- ── Desktop Nav (centered pill) ── --}}
      <nav class="front-nav-pill d-none d-lg-flex" aria-label="Site links">
        <a href="{{ route('front.home') }}"
          class="front-nav-link {{ request()->routeIs('front.home') ? 'active' : '' }}">
          Home
        </a>
        <a href="/#features" class="front-nav-link">Features</a>
        <a href="/#about" class="front-nav-link">About</a>
        <a href="{{ route('front.blogs.index') }}"
          class="front-nav-link {{ request()->routeIs('front.blogs.*') ? 'active' : '' }}">
          Blog
        </a>
        <a href="{{ route('front.pricing') }}"
          class="front-nav-link {{ request()->routeIs('front.pricing') ? 'active' : '' }}">
          Pricing
        </a>
        <a href="/#contact" class="front-nav-link">Contact</a>
      </nav>

      {{-- ── Right side controls ── --}}
      <div class="front-navbar-right">

        {{-- Theme Switcher --}}
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
        {{-- User Avatar Dropdown --}}
        <div class="dropdown">
          <button class="front-avatar-btn" id="profileDropdown" data-bs-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false" aria-label="User menu">
            @if(Auth::user()->hasAvatar())
            <img class="front-avatar-img" src="{{ Auth::user()->avatar_url }}" alt="profile">
            @else
            <div class="front-avatar-initials">{{ Auth::user()->initials }}</div>
            @endif
            <i data-lucide="chevron-down" class="front-avatar-caret"></i>
          </button>

          <div class="dropdown-menu dropdown-menu-end front-dropdown shadow-lg p-0 mt-2" aria-labelledby="profileDropdown">
            {{-- Profile Header --}}
            <div class="front-dropdown-header">
              @if(Auth::user()->hasAvatar())
              <img class="front-dropdown-avatar" src="{{ Auth::user()->avatar_url }}" alt="">
              @else
              <div class="front-dropdown-avatar-initials">{{ Auth::user()->initials }}</div>
              @endif
              <div class="mt-2 text-center">
                <p class="fw-bold mb-0 fs-6">{{ Auth::user()->name }}</p>
                <p class="text-muted small mb-0">{{ Auth::user()->email }}</p>
                @if(Auth::user()->designation)
                <span class="badge bg-primary bg-opacity-10 text-primary mt-1 rounded-pill px-2 py-1" style="font-size: 10px;">
                  {{ Auth::user()->designation }}
                </span>
                @endif
              </div>
            </div>
            {{-- Menu Items --}}
            <ul class="list-unstyled p-2 mb-0">
              @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
              <li>
                <a href="{{ route('admin.dashboard') }}" class="front-dropdown-item">
                  <span class="front-dropdown-icon bg-primary bg-opacity-10 text-primary">
                    <i data-lucide="layout-dashboard"></i>
                  </span>
                  Dashboard
                </a>
              </li>
              <li>
                <a href="{{ route('admin.profile') }}" class="front-dropdown-item">
                  <span class="front-dropdown-icon bg-info bg-opacity-10 text-info">
                    <i data-lucide="user"></i>
                  </span>
                  Profile
                </a>
              </li>
              <li>
                <a href="{{ route('admin.profile.edit') }}" class="front-dropdown-item">
                  <span class="front-dropdown-icon bg-warning bg-opacity-10 text-warning">
                    <i data-lucide="edit"></i>
                  </span>
                  Edit Profile
                </a>
              </li>
              @else
              <li>
                <a href="{{ route('user.dashboard') }}" class="front-dropdown-item">
                  <span class="front-dropdown-icon bg-primary bg-opacity-10 text-primary">
                    <i data-lucide="layout-dashboard"></i>
                  </span>
                  Dashboard
                </a>
              </li>
              <li>
                <a href="{{ route('user.profile') }}" class="front-dropdown-item">
                  <span class="front-dropdown-icon bg-info bg-opacity-10 text-info">
                    <i data-lucide="user"></i>
                  </span>
                  Profile
                </a>
              </li>
              @endif
              <li>
                <hr class="dropdown-divider my-1 mx-2">
              </li>
              <li>
                <a href="javascript:;" class="front-dropdown-item text-danger"
                  onclick="event.preventDefault(); document.getElementById('front-logout-form').submit();">
                  <span class="front-dropdown-icon bg-danger bg-opacity-10 text-danger">
                    <i data-lucide="log-out"></i>
                  </span>
                  Log Out
                </a>
              </li>
            </ul>
            <form id="front-logout-form" method="POST" action="{{ (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin()) ? route('auth.logout') : route('user.logout') }}" class="d-none">
              @csrf
            </form>
          </div>
        </div>
        @endauth

        @guest
        {{-- Desktop Auth Buttons --}}
        <div class="d-none d-lg-flex align-items-center gap-2">
          <a href="{{ route('user.login') }}" class="btn btn-sm rounded-pill px-4 py-2 fw-medium front-btn-ghost">
            Login
          </a>
          <a href="{{ route('user.register') }}" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-semibold btn-hover-elevate shadow-sm d-flex align-items-center gap-1">
            Get Started <i data-lucide="arrow-right" class="icon-xs"></i>
          </a>
        </div>
        @endguest

        {{-- Mobile Hamburger --}}
        <button class="front-hamburger d-lg-none" type="button"
          data-bs-toggle="collapse" data-bs-target="#frontMobileNav"
          aria-controls="frontMobileNav" aria-expanded="false" aria-label="Toggle navigation"
          id="frontHamburger">
          <span></span>
          <span></span>
          <span></span>
        </button>

      </div>{{-- end right --}}
    </div>{{-- end inner --}}
  </div>{{-- end container --}}

  {{-- ── Mobile Drawer ── --}}
  <div class="collapse front-mobile-nav" id="frontMobileNav">
    <div class="container pb-4">
      <ul class="list-unstyled mb-0">
        <li>
          <a href="{{ route('front.home') }}"
            class="front-mobile-link {{ request()->routeIs('front.home') ? 'active' : '' }}">
            <span class="front-mobile-icon"><i data-lucide="home"></i></span> Home
          </a>
        </li>
        <li>
          <a href="/#features" class="front-mobile-link">
            <span class="front-mobile-icon"><i data-lucide="zap"></i></span> Features
          </a>
        </li>
        <li>
          <a href="/#about" class="front-mobile-link">
            <span class="front-mobile-icon"><i data-lucide="info"></i></span> About
          </a>
        </li>
        <li>
          <a href="{{ route('front.blogs.index') }}"
            class="front-mobile-link {{ request()->routeIs('front.blogs.*') ? 'active' : '' }}">
            <span class="front-mobile-icon"><i data-lucide="book-open"></i></span> Blog
          </a>
        </li>
        <li>
          <a href="{{ route('front.pricing') }}"
            class="front-mobile-link {{ request()->routeIs('front.pricing') ? 'active' : '' }}">
            <span class="front-mobile-icon"><i data-lucide="tag"></i></span> Pricing
          </a>
        </li>
        <li>
          <a href="/#contact" class="front-mobile-link">
            <span class="front-mobile-icon"><i data-lucide="mail"></i></span> Contact
          </a>
        </li>
      </ul>
      @guest
      <div class="front-mobile-auth mt-4 d-flex flex-column gap-2">
        <a href="{{ route('user.login') }}" class="btn btn-outline-primary rounded-pill fw-medium py-2">
          <i data-lucide="log-in" class="icon-sm me-2"></i>Login
        </a>
        <a href="{{ route('user.register') }}" class="btn btn-primary rounded-pill fw-semibold py-2">
          <i data-lucide="user-plus" class="icon-sm me-2"></i>Get Started
        </a>
      </div>
      @endguest
    </div>
  </div>
</nav>