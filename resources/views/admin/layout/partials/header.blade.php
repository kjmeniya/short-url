<nav class="navbar">
  <div class="navbar-content">

    <div class="logo-mini-wrapper">
      <img src="{{ logo_url('admin', 'small', 'light') }}" class="logo-mini logo-mini-light h-auto" alt="logo">
      <img src="{{ logo_url('admin', 'small', 'dark') }}" class="logo-mini logo-mini-dark h-auto" alt="logo">
    </div>

    <!-- Search Input with Modal Trigger -->
    @if(hasPermission('admin.search.get'))
    <form class="search-form position-relative">
      <div class="input-group border rounded px-2">
        <div class="input-group-text">
          <i data-lucide="search"></i>
        </div>
        <input type="text" class="form-control globalSearch" placeholder="Search here..." autocomplete="off" readonly>
        <div class="input-group-text">
          <div class="d-flex align-items-center gap-1">
            <kbd class="bg-light border text-muted small">⌘</kbd>
            <kbd class="bg-light border text-muted small px-2">K</kbd>
          </div>
        </div>
      </div>
    </form>
    @endif

    <ul class="navbar-nav">
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
      @if(hasPermission('admin.search.get'))
      <li class="nav-item d-flex d-md-none">
        <a class="nav-link d-flex globalSearch" href="javascript:;" role="button" title="Search...">
          <i data-lucide="search" class=""></i>
        </a>
      </li>
      @endif
      <!-- <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle d-flex" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fi fi-us" title="en" alt="en"></i>
          <span class="ms-2 d-none d-md-inline-block">English</span>
        </a>
        <div class="dropdown-menu" aria-labelledby="languageDropdown">
          <a href="javascript:;" class="dropdown-item py-2 d-flex"><i class="fi fi-us" title="en" alt="en"></i> <span class="ms-2"> English </span></a>
          <a href="javascript:;" class="dropdown-item py-2 d-flex"><i class="fi fi-in" title="gu" alt="gu"></i> <span class="ms-2"> Gujarati </span></a>
          <a href="javascript:;" class="dropdown-item py-2 d-flex"><i class="fi fi-in" title="hi" alt="hi"></i> <span class="ms-2"> Hindi </span></a>
        </div>
      </li> -->
      @canAccess('admin.analytics.live')
      <li class="nav-item dropdown px-2">
        <a class="nav-link dropdown-toggle" href="javascript:;" id="liveDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i data-lucide="activity"></i>
          <span class="badge bg-success" id="header-live-count">0</span>
        </a>
        <div class="dropdown-menu p-0" aria-labelledby="liveDropdown" style="min-width: 300px;">
          <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
            <p class="mb-0 fw-bold">Live Users</p>
            <h5 class="mb-0" id="header-stat-total">0</h5>
          </div>
          <div class="row g-2 px-3 py-2">
            <div class="col-6 col-md-6">
              <div class="d-flex align-items-center justify-content-between">
                <p class="mb-0 fw-bold small text-muted">Platform</p>
              </div>
              <div class="d-flex gap-2 flex-column pt-2">
                <div class="d-flex align-items-center justify-content-between p-2 border rounded gap-3">
                  <div class="d-flex align-items-center">
                    <i data-lucide="monitor" class="icon-sm me-2 text-primary"></i>
                    <small class="text-muted">Web</small>
                  </div>
                  <h5 class="mb-0" id="header-stat-web">0</h5>
                </div>
                <div class="d-flex align-items-center justify-content-between p-2 border rounded gap-3">
                  <div class="d-flex align-items-center">
                    <i data-lucide="shield" class="icon-sm me-2 text-danger"></i>
                    <small class="text-muted">Admin</small>
                  </div>
                  <h5 class="mb-0" id="header-stat-admin">0</h5>
                </div>
                <div class="d-flex align-items-center justify-content-between p-2 border rounded gap-3">
                  <div class="d-flex align-items-center">
                    <i data-lucide="layers" class="icon-sm me-2 text-success"></i>
                    <small class="text-muted">App</small>
                  </div>
                  <h5 class="mb-0" id="header-stat-app">0</h5>
                </div>
              </div>
            </div>
            <div class="col-6 col-md-6">
              <div class="d-flex align-items-center justify-content-between">
                <p class="mb-0 fw-bold small text-muted">Devices</p>
              </div>
              <div class="d-flex gap-2 flex-column pt-2">
                <div class="d-flex align-items-center justify-content-between p-2 border rounded gap-3">
                  <div class="d-flex align-items-center">
                    <i data-lucide="monitor" class="icon-sm me-2 text-primary"></i>
                    <small class="text-muted">Desktop</small>
                  </div>
                  <h5 class="mb-0" id="header-stat-desktop">0</h5>
                </div>
                <div class="d-flex align-items-center justify-content-between p-2 border rounded gap-3">
                  <div class="d-flex align-items-center">
                    <i data-lucide="smartphone" class="icon-sm me-2 text-info"></i>
                    <small class="text-muted">Mobile</small>
                  </div>
                  <h5 class="mb-0" id="header-stat-mobile">0</h5>
                </div>
                <div class="d-flex align-items-center justify-content-between p-2 border rounded gap-3">
                  <div class="d-flex align-items-center">
                    <i data-lucide="tablet" class="icon-sm me-2 text-warning"></i>
                    <small class="text-muted">Tablet</small>
                  </div>
                  <h5 class="mb-0" id="header-stat-tablet">0</h5>
                </div>
              </div>
            </div>
            <div class="col-12">
              <div class="d-flex align-items-center justify-content-between">
                <p class="mb-0 fw-bold small text-muted">Authenticated</p>
              </div>
              <div class="d-flex gap-2 flex-column pt-2">
                <div class="d-flex align-items-center justify-content-between p-2 border rounded gap-3">
                  <div class="d-flex align-items-center">
                    <i data-lucide="user-check" class="icon-sm me-2 text-primary"></i>
                    <small class="text-muted">Authenticated</small>
                  </div>
                  <h5 class="mb-0" id="header-stat-authenticated">0</h5>
                </div>
                <div class="d-flex align-items-center justify-content-between p-2 border rounded gap-3">
                  <div class="d-flex align-items-center">
                    <i data-lucide="user-x" class="icon-sm me-2 text-info"></i>
                    <small class="text-muted">Guest</small>
                  </div>
                  <h5 class="mb-0" id="header-stat-guest">0</h5>
                </div>
              </div>
            </div>
          </div>
          <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
            <a href="{{ route('admin.analytics.live') }}">View Details</a>
          </div>
      </li>
      @endcanAccess
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i data-lucide="bell"></i>
          <div class="indicator" id="notification-indicator" style="display: none;">
            <div class="circle"></div>
          </div>
        </a>
        <div class="dropdown-menu p-0" aria-labelledby="notificationDropdown" style="width: 350px;">
          <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
            <p id="notification-count">0 Notifications</p>
            <div class="d-flex align-items-center gap-3">
              <a href="javascript:;" class="text-secondary" id="refresh-notifications-header" title="Refresh">
                <i data-lucide="refresh-cw" class="icon-sm"></i>
              </a>
              <a href="javascript:;" class="text-secondary" id="clear-all-notifications">Clear all</a>
            </div>
          </div>
          <div class="p-1" id="notification-list">
            <div class="text-center py-4">
              <i data-lucide="bell" class="icon-lg text-muted mb-2"></i>
              <p class="text-muted">No notifications yet</p>
            </div>
          </div>
          <div class="px-3 py-2 d-flex align-items-center justify-content-center border-top">
            <a href="{{ route('admin.notifications.index') }}">View all</a>
          </div>
        </div>
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
              @else
              <p class="fs-16px fw-bolder">Guest User</p>
              <p class="fs-12px text-secondary">guest@example.com</p>
              @endauth
            </div>
          </div>
          <ul class="list-unstyled p-1">
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
            <!-- <li>
              <a href="{{ route('admin.users.index') }}" class="dropdown-item py-2 text-body ms-0">
                <i class="me-2 icon-md" data-lucide="users"></i>
                <span>Manage Users</span>
              </a>
            </li> -->
            <li>
              <!-- <a href="ja" class="dropdown-item py-2 text-body ms-0"> -->
              <a href="javascript:;" class="dropdown-item py-2 text-body ms-0" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="me-2 icon-md" data-lucide="log-out"></i>
                <span>Log Out</span>
              </a>
            </li>
            <form id="logout-form" method="POST" action="{{ route('auth.logout') }}" style="display: none;">
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
<!-- Search Modal -->
@if(hasPermission('admin.search.get'))
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="false" data-bs-backdrop="static" data-bs-keyboard="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <!-- Modal Header -->
      <div class="modal-header border-bottom-0">
        <div class="w-100">
          <div class="position-relative">
            <!-- Search Icon (Left) -->
            <i data-lucide="search" class="position-absolute top-50 start-0 translate-middle-y ms-3 icon-md text-muted"></i>

            <!-- Search Input -->
            <input
              type="text"
              class="form-control shadow-none ps-5 pe-5"
              id="modalSearchInput"
              placeholder="Search for anything..."
              autocomplete="off">

            <!-- Spinner Loader (Right) -->
            <div class="position-absolute top-50 translate-middle-y end-0 me-5 d-none" id="modalSearchLoader">
              <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <!-- Close Button (Right Inside Input) -->
            <button type="button" class="btn btn-sm position-absolute top-50 translate-middle-y end-0 me-3 py-0 px-1" id="clearSearch" aria-label="Clear" data-bs-dismiss="modal">
              <i class="text-muted icon-sm" data-lucide="x"></i>
            </button>
          </div>
        </div>
      </div>
      <!-- Modal Body -->
      <div class="modal-body pt-0">
        <!-- Quick Actions -->
        <div id="quickActions" class="mb-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0 text-uppercase text-muted fw-semibold small">Quick Actions</h6>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <a href="{{ route('admin.users.create') }}" class="d-flex align-items-center p-2 text-decoration-none border rounded">
                <div class="d-flex align-items-center justify-content-center rounded-circle me-2 bg-primary bg-opacity-10 avatar-sm">
                  <i data-lucide="user-plus" class="icon-sm text-primary"></i>
                </div>
                <div>
                  <div class="fw-medium small">Add User</div>
                  <div class="text-muted small">Create new user</div>
                </div>
              </a>
            </div>
            <div class="col-6">
              <a href="{{ route('admin.roles.create') }}" class="d-flex align-items-center p-2 text-decoration-none border rounded">
                <div class="d-flex align-items-center justify-content-center rounded-circle me-2 bg-success bg-opacity-10 avatar-sm">
                  <i data-lucide="shield-plus" class="icon-sm text-success"></i>
                </div>
                <div>
                  <div class="fw-medium small">Add Role</div>
                  <div class="text-muted small">Create new role</div>
                </div>
              </a>
            </div>
          </div>
        </div>

        <!-- Search Results -->
        <div id="searchResultsSection" class="d-none">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="mb-0 text-uppercase text-muted fw-semibold small">Search Results</h6>
            <small class="text-muted" id="modalSearchResultsCount">0 results</small>
          </div>
          <div id="modalSearchResultsContent">
            <!-- Results will be populated here -->
          </div>
        </div>

        <!-- Empty State -->
        <div id="emptySearchState" class="text-center py-4 d-none">
          <div class="d-flex justify-content-center mb-3">
            <div class="d-flex align-items-center justify-content-center rounded-circle bg-light avatar-lg">
              <i data-lucide="search" class="icon-lg text-muted"></i>
            </div>
          </div>
          <h6 class="mb-2 fw-semibold">No results found</h6>
          <p class="mb-0 text-muted small">Try searching with different keywords</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endif