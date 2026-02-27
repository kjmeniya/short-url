<nav class="sidebar">
  <div class="sidebar-header">
    <a href="{{ route('user.dashboard') }}" class="sidebar-brand">
      <img src="{{ logo_url('admin', 'large', 'light') }}" class="logo-mini logo-mini-light w-80px h-auto" alt="logo">
      <img src="{{ logo_url('admin', 'large', 'dark') }}" class="logo-mini logo-mini-dark w-80px h-auto" alt="logo">
    </a>
    <div class="sidebar-toggler not-active">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </div>
  <div class="sidebar-body">
    <ul class="nav" id="sidebarNav">
      @auth
      <li class="nav-item nav-category">Main</li>
      
      <li class="nav-item {{ active_class(['user/dashboard']) }}">
        <a href="{{ route('user.dashboard') }}" class="nav-link">
          <i class="link-icon" data-lucide="home"></i>
          <span class="link-title">Dashboard</span>
        </a>
      </li>

      <li class="nav-item nav-category">Link Management</li>
      <li class="nav-item {{ active_class(['user/my-links*', 'user/links*']) }}">
        <a href="{{ route('user.my-links') }}" class="nav-link">
          <i class="link-icon" data-lucide="link"></i>
          <span class="link-title">My Links</span>
        </a>
      </li>

      <li class="nav-item nav-category">Account Settings</li>
      <li class="nav-item {{ active_class(['user/profile*']) }}">
        <a href="{{ route('user.profile') }}" class="nav-link">
          <i class="link-icon" data-lucide="user"></i>
          <span class="link-title">My Profile</span>
        </a>
      </li>

      <li class="nav-item nav-category">Quick Links</li>
      <li class="nav-item">
        <a href="{{ url('/') }}" class="nav-link" target="_blank">
          <i class="link-icon" data-lucide="earth"></i>
          <span class="link-title">Visit Website</span>
        </a>
      </li>
      @endauth
    </ul>
  </div>
</nav>
