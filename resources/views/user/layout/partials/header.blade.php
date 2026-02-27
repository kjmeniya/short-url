<nav class="navbar">
  <div class="navbar-content">

    <div class="logo-mini-wrapper">
      <img src="{{ logo_url('admin', 'small', 'light') }}" class="logo-mini logo-mini-light h-auto" alt="logo">
      <img src="{{ logo_url('admin', 'small', 'dark') }}" class="logo-mini logo-mini-dark h-auto" alt="logo">
    </div>

    <!-- Replace standard user panel title block with the admin themed nav item wrapper -->
    <ul class="navbar-nav ms-auto">
      <li class="theme-switcher-wrapper nav-item mx-1">
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
      </li>

      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          @if(Auth::user()->hasAvatar())
          <img class="w-30px h-30px ms-1 rounded-circle" src="{{ Auth::user()->avatar_url }}" alt="profile">
          @else
          <div class="w-30px h-30px ms-1 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="font-size: 12px; font-weight: 600;">
            {{ Auth::user()->initials }}
          </div>
          @endif
        </a>
        <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
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
              @auth
              <p class="fs-16px fw-bolder">{{ Auth::user()->name }}</p>
              <p class="fs-12px text-secondary">{{ Auth::user()->email }}</p>
              @if(Auth::user()->designation)
              <p class="fs-10px text-muted">{{ Auth::user()->designation }}</p>
              @endif
              @endauth
            </div>
          </div>
          <ul class="list-unstyled p-1">
            <li>
              <a href="{{ route('user.profile') }}" class="dropdown-item py-2 text-body ms-0">
                <i class="me-2 icon-md" data-lucide="user"></i>
                <span>Profile</span>
              </a>
            </li>
            <li>
              <a href="{{ route('user.links') }}" class="dropdown-item py-2 text-body ms-0">
                <i class="me-2 icon-md" data-lucide="file-text"></i>
                <span>My Links</span>
              </a>
            </li>
            <li>
              <a href="javascript:;" class="dropdown-item py-2 text-body ms-0" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="me-2 icon-md" data-lucide="log-out"></i>
                <span>Log Out</span>
              </a>
            </li>
            <form id="logout-form" method="POST" action="{{ route('user.logout') }}" style="display: none;">
              @csrf
            </form>
          </ul>
        </div>
      </li>
    </ul>

    <a href="#" class="sidebar-toggler">
      <i data-lucide="menu"></i>
    </a>

  </div>
</nav>