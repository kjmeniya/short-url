<nav class="sidebar">
  <div class="sidebar-header">
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
      <!-- Noble<span>UI</span> -->
      <!-- <img src="{{ url('build/images/logo-light.png') }}" class="logo-mini logo-mini-light w-125px h-auto" alt="logo">
      <img src="{{ url('build/images/logo-dark.png') }}" class="logo-mini logo-mini-dark w-125px h-auto" alt="logo"> -->
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
      <!-- Main Dashboard -->
      @canAccess('admin.dashboard')
      <li class="nav-item nav-category">Main</li>
      <li class="nav-item {{ active_class(['admin/dashboard']) }}">
        <a href="{{ route('admin.dashboard') }}" class="nav-link">
          <i class="link-icon" data-lucide="home"></i>
          <span class="link-title">Dashboard</span>
        </a>
      </li>
      @endcanAccess
      @canAccess('admin.profile')
      <li class="nav-item {{ active_class(['admin/profile*']) }}">
        <a href="{{ route('admin.profile') }}" class="nav-link">
          <i class="link-icon" data-lucide="user"></i>
          <span class="link-title">My Profile</span>
        </a>
      </li>
      @endcanAccess

      <li class="nav-item">
        <a href="{{ url('/') }}" class="nav-link" target="_blank">
          <i class="link-icon" data-lucide="earth"></i>
          <span class="link-title">Visit Website</span>
        </a>
      </li>
      {{-- Short URLs --}}
      @if(canAccessRoute('admin.short-urls.index'))
      <li class="nav-item nav-category">Short URLs</li>
      @endif
      @canAccess('admin.short-urls.index')
      <li class="nav-item {{ active_class(['admin/short-urls*']) }}">
        <a href="{{ route('admin.short-urls.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="link"></i>
          <span class="link-title">Short URLs</span>
        </a>
      </li>
      @endcanAccess

      @canAccess('admin.short-urls.index')
      <li class="nav-item {{ active_class(['admin/global-ip-blocks*']) }}">
        <a href="{{ route('admin.global-ip-blocks.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="shield-alert"></i>
          <span class="link-title">Global IP Blocks</span>
        </a>
      </li>
      @endcanAccess

      <!-- Subscriptions Section -->
      @if(canAccessRoute('admin.plans.index') || canAccessRoute('admin.subscriptions.index'))
      <li class="nav-item nav-category">Subscriptions</li>
      @endif
      @canAccess('admin.plans.index')
      <li class="nav-item {{ active_class(['admin/plans*']) }}">
        <a href="{{ route('admin.plans.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="credit-card"></i>
          <span class="link-title">Plans</span>
        </a>
      </li>
      @endcanAccess
      @canAccess('admin.subscriptions.index')
      <li class="nav-item {{ active_class(['admin/subscriptions*']) }}">
        <a href="{{ route('admin.subscriptions.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="package"></i>
          <span class="link-title">Subscriptions</span>
        </a>
      </li>
      @endcanAccess

      @if(canAccessRoute('admin.analytics.live') || canAccessRoute('admin.analytics.page-views'))
      <!-- Analytics Section -->
      <li class="nav-item nav-category">Analytics</li>
      @endif
      @canAccess('admin.analytics.live')
      <li class="nav-item {{ active_class(['admin/analytics/live*']) }}">
        <a href="{{ route('admin.analytics.live') }}" class="nav-link">
          <i class="link-icon" data-lucide="activity"></i>
          <span class="link-title">Live</span>
        </a>
      </li>
      @endcanAccess
      @canAccess('admin.analytics.page-views')
      <li class="nav-item {{ active_class(['admin/analytics/page-views*']) }}">
        <a href="{{ route('admin.analytics.page-views') }}" class="nav-link">
          <i class="link-icon" data-lucide="activity"></i>
          <span class="link-title">Page Views</span>
        </a>
      </li>
      @endcanAccess

      <!-- User Management Section -->
      @if(canAccessRoute('admin.users.index') || canAccessRoute('admin.roles.index') || canAccessRoute('admin.permissions.index'))
      <li class="nav-item nav-category">User Management</li>
      @endif
      @canAccess('admin.users.index')
      <li class="nav-item {{ active_class(['admin/users*']) }}">
        <a href="{{ route('admin.users.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="users"></i>
          <span class="link-title">Users</span>
        </a>
      </li>
      @endcanAccess

      @canAccess('admin.roles.index')
      <li class="nav-item {{ active_class(['admin/roles*']) }}">
        <a href="{{ route('admin.roles.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="user-check"></i>
          <span class="link-title">Roles</span>
        </a>
      </li>
      @endcanAccess

      @canAccess('admin.permissions.index')
      <li class="nav-item {{ active_class(['admin/permissions*']) }}">
        <a href="{{ route('admin.permissions.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="shield"></i>
          <span class="link-title">Permissions</span>
        </a>
      </li>
      @endcanAccess

      <!-- Email -->
      @if(canAccessRoute('admin.email-templates.index') || canAccessRoute('admin.email-logs.index'))
      <li class="nav-item nav-category">Email</li>
      @endif

      @canAccess('admin.email-templates.index')
      <li class="nav-item {{ active_class(['admin/email-templates*']) }}">
        <a href="{{ route('admin.email-templates.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="mail"></i>
          <span class="link-title">Email Templates</span>
        </a>
      </li>
      @endcanAccess

      @canAccess('admin.email-logs.index')
      <li class="nav-item {{ active_class(['admin/email-logs*']) }}">
        <a href="{{ route('admin.email-logs.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="send"></i>
          <span class="link-title">Email Logs</span>
        </a>
      </li>
      @endcanAccess

      <!-- Notifications -->
      @if(canAccessRoute('admin.notifications.index'))
      <li class="nav-item nav-category">Notifications</li>
      @endif
      @canAccess('admin.notifications.index')
      <li class="nav-item {{ active_class(['admin/notifications*']) }}">
        <a href="{{ route('admin.notifications.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="bell"></i>
          <span class="link-title">Notifications</span>
        </a>
      </li>
      @endcanAccess

      <!-- Logs -->
      @if(canAccessRoute('admin.laravel-logs.index') || canAccessRoute('admin.login-logs.index'))
      <li class="nav-item nav-category">Logs</li>
      @endif
      @canAccess('admin.laravel-logs.index')
      <li class="nav-item {{ active_class(['admin/laravel-logs*']) }}">
        <a href="{{ route('admin.laravel-logs.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="file-text"></i>
          <span class="link-title">Laravel Logs</span>
        </a>
      </li>
      @endcanAccess

      @canAccess('admin.login-logs.index')
      <li class="nav-item {{ active_class(['admin/login-logs*']) }}">
        <a href="{{ route('admin.login-logs.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="log-in"></i>
          <span class="link-title">Login Logs</span>
        </a>
      </li>
      @endcanAccess

      <!-- Contacts -->
      @if(canAccessRoute('admin.contacts.index'))
      <li class="nav-item nav-category">Contacts</li>
      @endif
      @canAccess('admin.contacts.index')
      <li class="nav-item {{ active_class(['admin/contacts*']) }}">
        <a href="{{ route('admin.contacts.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="message-square"></i>
          <span class="link-title">Contacts</span>
        </a>
      </li>
      @endcanAccess

      <!-- Blogs -->
      @if(canAccessRoute('admin.blogs.index'))
      <li class="nav-item nav-category">Blogs</li>
      @endif
      @canAccess('admin.blogs.index')
      <li class="nav-item {{ active_class(['admin/blogs*']) }}">
        <a href="{{ route('admin.blogs.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="book-open"></i>
          <span class="link-title">Blogs</span>
        </a>
      </li>
      @endcanAccess


      <!-- System Configuration Section -->
      @canAccess('admin.settings.index')
      <li class="nav-item nav-category">System Configuration</li>
      <li class="nav-item {{ active_class(['admin/settings*']) }}">
        <a href="{{ route('admin.settings.index') }}" class="nav-link">
          <i class="link-icon" data-lucide="settings"></i>
          <span class="link-title">Settings</span>
        </a>
      </li>
      @endcanAccess
      @endauth
    </ul>
  </div>
</nav>