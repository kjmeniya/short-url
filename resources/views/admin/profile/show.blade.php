@extends('admin.layout.master')

@section('title', $title ?? 'Profile')
@section('description', $description ?? 'View and manage your admin profile information')
@section('keywords', $keywords ?? 'admin profile, account settings, personal information')

@push('plugin-styles')
<link href="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Profile</li>
  </ol>
</nav>

<!-- Header Section -->
<div class="profile-page tx-13">
  <div class="row">
    <div class="col-12 grid-margin">
      <div class="card">
        <div class="position-relative">
          <figure class="overflow-hidden mb-0 d-flex justify-content-center align-items-center rounded-top"
            style="height: 200px; background: linear-gradient(135deg, #245dac 0%, #1a4a8a 100%);">
            <div class="w-100 text-center">
              <i data-lucide="user-circle" class="text-white" style="width: 80px; height: 80px; opacity: 0.3;"></i>
              <h1 class="text-white fw-bold mb-0" style="opacity: 0.3; text-transform: uppercase; letter-spacing: 0.2rem;">{{ $admin->name }}</h1>
            </div>
          </figure>
          <div class="d-flex justify-content-between align-items-center position-absolute top-90 w-100 px-2 px-md-4 mt-n4">
            <div class="d-flex align-items-center">
              <div class="me-3">
                <div class="w-70px h-70px bg-white dark:bg-dark rounded-circle shadow d-flex align-items-center justify-content-center position-relative">
                  @if($admin->hasAvatar())
                  <img class="w-70px h-70px rounded-circle border border-3 border-light" src="{{ $admin->avatar_url }}" alt="profile" style="object-fit: cover;">
                  @else
                  <div class="w-70px h-70px rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center border border-3 border-light" style="font-size: 24px; font-weight: 600;">
                    {{ $admin->initials }}
                  </div>
                  @endif
                  @if($admin->is_active)
                  <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white"
                    style="width: 20px; height: 20px;"
                    title="Active User"></span>
                  @else
                  <span class="position-absolute bottom-0 end-0 bg-danger rounded-circle border border-2 border-white"
                    style="width: 20px; height: 20px;"
                    title="Inactive User"></span>
                  @endif
                </div>
              </div>
              <div>
                @php
                // Use new role system only
                if ($admin->role_id && $admin->role) {
                $roleName = $admin->role->name;
                $label = $admin->role->display_name;

                $roleColors = [
                'user' => 'bg-primary',
                'admin' => 'bg-warning',
                'super_admin' => 'bg-danger'
                ];
                $color = $roleColors[$roleName] ?? 'bg-secondary';
                } else {
                $label = 'No Role';
                $color = 'bg-secondary';
                }
                @endphp
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex justify-content-center p-3 rounded-bottom">
          <ul class="d-flex align-items-center m-0 p-0">
            <!-- <li class="d-flex align-items-center active">
              <i class="me-1 icon-md text-primary" data-lucide="user-circle"></i>
              <a class="pt-1px d-none d-md-block text-primary" href="#">My Profile</a>
            </li> -->
            <li class="ms-3 ps-3 border-start d-flex align-items-center">
              <i class="me-1 icon-md" data-lucide="shield"></i>
              <a class="pt-1px d-none d-md-block text-body" href="#">
                <span class="badge {{ $color }} text-white">{{ $label }}</span>
              </a>
            </li>
            <!-- <li class="ms-3 ps-3 border-start d-flex align-items-center">
              <i class="me-1 icon-md" data-lucide="mail"></i>
              <a class="pt-1px d-none d-md-block text-body" href="mailto:{{ $admin->email }}">{{ $admin->email }}</a>
            </li> -->
            <li class="ms-3 ps-3 border-start d-flex align-items-center">
              <i class="me-1 icon-md" data-lucide="edit"></i>
              <a class="pt-1px d-none d-md-block text-body" href="{{ route('admin.profile.edit') }}">Edit Profile</a>
            </li>
            <li class="ms-3 ps-3 border-start d-flex align-items-center">
              <i class="me-1 icon-md" data-lucide="arrow-left"></i>
              <a class="pt-1px d-none d-md-block text-body" href="{{ route('admin.dashboard') }}">Dashboard</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Content -->
<div class="row profile-body">
  <!-- Profile Content with Tabs -->
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs nav-tabs-line" id="profileTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="basic-info-tab" data-bs-toggle="tab" data-bs-target="#basic-info" type="button" role="tab">
              <i data-lucide="user" class="icon-sm me-2"></i>
              Basic Information
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="account-status-tab" data-bs-toggle="tab" data-bs-target="#account-status" type="button" role="tab">
              <i data-lucide="shield" class="icon-sm me-2"></i>
              Account Status
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
              <i data-lucide="settings" class="icon-sm me-2"></i>
              Preferences
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
              <i data-lucide="shield-check" class="icon-sm me-2"></i>
              Security
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
              <i data-lucide="activity" class="icon-sm me-2"></i>
              Activity
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="login-history-tab" data-bs-toggle="tab" data-bs-target="#login-history" type="button" role="tab">
              <i data-lucide="log-in" class="icon-sm me-2"></i>
              Login History
            </button>
          </li>
          @if(isSuperAdmin() || hasPermission('admin.profile.email-history'))
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="email-history-tab" data-bs-toggle="tab" data-bs-target="#email-history" type="button" role="tab">
              <i data-lucide="mail" class="icon-sm me-2"></i>
              Email History
            </button>
          </li>
          @else
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="email-history-restricted-tab" data-bs-toggle="tab" data-bs-target="#email-history-restricted" type="button" role="tab">
              <i data-lucide="lock" class="icon-sm me-2"></i>
              Email History
            </button>
          </li>
          @endif
        </ul>

        <!-- Tab Content -->
        <div class="tab-content p-4" id="profileTabsContent">
          <!-- Basic Information Tab -->
          <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
            <div class="row">
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="user" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Full Name</p>
                    <p class="mb-0 fw-medium">{{ $admin->name }}</p>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="mail" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Email Address</p>
                    <p class="mb-0 fw-medium">{{ $admin->email }}</p>
                  </div>
                </div>
              </div>

              @if($admin->phone)
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="phone" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Phone Number</p>
                    <p class="mb-0 fw-medium">{{ $admin->phone }}</p>
                  </div>
                </div>
              </div>
              @endif

              @if($admin->date_of_birth)
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="calendar" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Date of Birth</p>
                    <p class="mb-0 fw-medium">{{ formatUserDate($admin->date_of_birth) }}</p>
                  </div>
                </div>
              </div>
              @endif

              @if($admin->address)
              <div class="col-12">
                <div class="d-flex align-items-start mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="map-pin" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Address</p>
                    <p class="mb-0 fw-medium">{{ $admin->address }}</p>
                  </div>
                </div>
              </div>
              @endif

              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="calendar-plus" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Member Since</p>
                    <p class="mb-0 fw-medium">{{ formatUserDate($admin->created_at) }}</p>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="edit" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Last Profile Update</p>
                    <p class="mb-0 fw-medium">{{ formatUserDateTime($admin->updated_at) }}</p>
                  </div>
                </div>
              </div>

              @if($admin->isGoogleUser())
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="chrome" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Google Account</p>
                    <p class="mb-0 fw-medium">
                      <span class="badge bg-info">
                        <i data-lucide="check-circle" class="icon-xs me-1"></i>
                        Connected
                      </span>
                    </p>
                  </div>
                </div>
              </div>

              @if($admin->google_id)
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="key" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Google ID</p>
                    <p class="mb-0 fw-medium">{{ $admin->google_id }}</p>
                  </div>
                </div>
              </div>
              @endif
              @endif

              @if($admin->preferences && is_array($admin->preferences) && count($admin->preferences) > 0)
              <div class="col-12">
                <div class="d-flex align-items-start mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="settings" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">User Preferences</p>
                    <div class="mt-1">
                      @foreach($admin->preferences as $key => $value)
                      <span class="badge bg-light text-dark me-1 mb-1">{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</span>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>
              @endif
            </div>
          </div>
          <!-- Account Status Tab -->
          <div class="tab-pane fade" id="account-status" role="tabpanel">
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Account Status</span>
                  @if($admin->is_active)
                  <span class="badge bg-success">
                    <i data-lucide="check-circle" class="icon-xs me-1"></i>
                    Active
                  </span>
                  @else
                  <span class="badge bg-danger">
                    <i data-lucide="x-circle" class="icon-xs me-1"></i>
                    Inactive
                  </span>
                  @endif
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Email Verified</span>
                  @if($admin->email_verified_at)
                  <span class="badge bg-success">
                    <i data-lucide="mail-check" class="icon-xs me-1"></i>
                    Verified
                  </span>
                  @else
                  <span class="badge bg-warning">
                    <i data-lucide="mail-x" class="icon-xs me-1"></i>
                    Pending
                  </span>
                  @endif
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Role</span>
                  <span class="badge {{ $color }}">{{ $label }}</span>
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Account Type</span>
                  @if($admin->isGoogleUser())
                  <span class="badge bg-info">
                    <i data-lucide="chrome" class="icon-xs me-1"></i>
                    Google User
                  </span>
                  @else
                  <span class="badge bg-primary">
                    <i data-lucide="user" class="icon-xs me-1"></i>
                    Regular User
                  </span>
                  @endif
                </div>
              </div>

              @if($admin->email_verified_at)
              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Email Verified On</span>
                  <span class="small">{{ formatUserDateTime($admin->email_verified_at) }}</span>
                </div>
              </div>
              @endif

              @if($admin->locked_until)
              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Account Locked Until</span>
                  <span class="badge bg-danger">
                    <i data-lucide="lock" class="icon-xs me-1"></i>
                    {{ formatUserDateTime($admin->locked_until) }}
                  </span>
                </div>
              </div>
              @endif

              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Login Attempts</span>
                  <span class="badge {{ $admin->login_attempts > 0 ? 'bg-warning' : 'bg-success' }}">
                    {{ $admin->login_attempts ?? 0 }}
                  </span>
                </div>
              </div>

              @if($admin->force_password_change)
              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Password Change Required</span>
                  <span class="badge bg-warning">
                    <i data-lucide="alert-triangle" class="icon-xs me-1"></i>
                    Yes
                  </span>
                </div>
              </div>
              @endif

              @if($admin->password_changed_at)
              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Password Last Changed</span>
                  <span class="small">{{ formatUserDateTime($admin->password_changed_at) }}</span>
                </div>
              </div>
              @endif

              @if($admin->last_login_at)
              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Last Login</span>
                  <span class="small">{{ formatUserDateTime($admin->last_login_at) }}</span>
                </div>
              </div>
              @endif

              @if($admin->last_login_ip)
              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Last Login IP</span>
                  <span class="small font-monospace">{{ $admin->last_login_ip }}</span>
                </div>
              </div>
              @endif
            </div>
          </div>

          <!-- Preferences Tab -->
          <div class="tab-pane fade" id="preferences" role="tabpanel">
            <div class="row">
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="globe" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Timezone</p>
                    <p class="mb-0 fw-medium">
                      @if($admin->timezone)
                      {{ $admin->timezone }}
                      @else
                      <span class="text-muted">System Default</span>
                      @endif
                    </p>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="languages" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Language</p>
                    <p class="mb-0 fw-medium">{{ getLanguageOptions()[$admin->language ?? 'en'] ?? 'English' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Security Tab -->
          <div class="tab-pane fade" id="security" role="tabpanel">
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Two-Factor Authentication</span>
                  @if($admin->two_factor_enabled)
                  <span class="badge bg-success">
                    <i data-lucide="shield-check" class="icon-xs me-1"></i>
                    Enabled
                  </span>
                  @else
                  <span class="badge bg-secondary">
                    <i data-lucide="shield-off" class="icon-xs me-1"></i>
                    Disabled
                  </span>
                  @endif
                </div>
              </div>

              <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                  <span class="text-muted fw-medium">Email Verification Status</span>
                  @if($admin->email_verified_at)
                  <span class="badge bg-success">
                    <i data-lucide="check-circle" class="icon-xs me-1"></i>
                    Verified
                  </span>
                  @else
                  <span class="badge bg-warning">
                    <i data-lucide="alert-circle" class="icon-xs me-1"></i>
                    Unverified
                  </span>
                  @endif
                </div>
              </div>

              @if($admin->two_factor_enabled)
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="smartphone" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Authentication Method</p>
                    <p class="mb-0 fw-medium">
                      @if($admin->two_factor_method === 'email')
                      <i data-lucide="mail" class="icon-xs me-1"></i>
                      Email Verification
                      @else
                      <i data-lucide="qr-code" class="icon-xs me-1"></i>
                      Authenticator App
                      @endif
                    </p>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="calendar-check" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">2FA Enabled Since</p>
                    <p class="mb-0 fw-medium">{{ formatUserDateTime($admin->two_factor_confirmed_at) }}</p>
                  </div>
                </div>
              </div>

              @if($admin->two_factor_recovery_codes && count($admin->two_factor_recovery_codes) > 0)
              <div class="col-12">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="key" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Recovery Codes Available</p>
                    <p class="mb-0 fw-medium">
                      <span class="badge bg-info">{{ count($admin->two_factor_recovery_codes) }} codes</span>
                    </p>
                  </div>
                </div>
              </div>
              @endif
              @endif

              @if($admin->password_changed_at)
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="lock" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Password Last Changed</p>
                    <p class="mb-0 fw-medium">{{ formatUserDateTime($admin->password_changed_at) }}</p>
                  </div>
                </div>
              </div>
              @endif

              @if($admin->force_password_change)
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="alert-triangle" class="icon-sm text-warning"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Password Change Required</p>
                    <p class="mb-0 fw-medium">
                      <span class="badge bg-warning">Action Required</span>
                    </p>
                  </div>
                </div>
              </div>
              @endif

              @if($admin->isGoogleUser() && $admin->needsPasswordSetup())
              <div class="col-12">
                <div class="alert alert-info d-flex align-items-center" role="alert">
                  <i data-lucide="info" class="icon-sm me-2"></i>
                  <div>
                    <strong>Google Account:</strong> You can set a password to enable email/password login in addition to Google authentication.
                  </div>
                </div>
              </div>
              @endif

              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="shield" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Account Security Level</p>
                    <p class="mb-0 fw-medium">
                      @php
                      $securityLevel = 'Basic';
                      $securityColor = 'bg-warning';

                      if ($admin->two_factor_enabled && $admin->email_verified_at) {
                      $securityLevel = 'High';
                      $securityColor = 'bg-success';
                      } elseif ($admin->email_verified_at) {
                      $securityLevel = 'Medium';
                      $securityColor = 'bg-info';
                      }
                      @endphp
                      <span class="badge {{ $securityColor }}">{{ $securityLevel }}</span>
                    </p>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="activity" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Failed Login Attempts</p>
                    <p class="mb-0 fw-medium">
                      <span class="badge {{ $admin->login_attempts > 0 ? 'bg-danger' : 'bg-success' }}">
                        {{ $admin->login_attempts ?? 0 }}
                      </span>
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Activity Tab -->
          <div class="tab-pane fade" id="activity" role="tabpanel">
            <div class="row">
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="calendar-plus" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Account Created</p>
                    <p class="mb-0 fw-medium">{{ formatUserDateTime($admin->created_at) }}</p>
                  </div>
                </div>
              </div>

              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="edit" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Last Profile Update</p>
                    <p class="mb-0 fw-medium">{{ formatUserDateTime($admin->updated_at) }}</p>
                  </div>
                </div>
              </div>

              @if($admin->email_verified_at)
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="mail-check" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Email Verified</p>
                    <p class="mb-0 fw-medium">{{ formatUserDateTime($admin->email_verified_at) }}</p>
                  </div>
                </div>
              </div>
              @endif

              @if($admin->two_factor_confirmed_at)
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="shield-check" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">2FA Enabled</p>
                    <p class="mb-0 fw-medium">{{ formatUserDateTime($admin->two_factor_confirmed_at) }}</p>
                  </div>
                </div>
              </div>
              @endif

              @if($admin->last_login_at)
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="log-in" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Last Login</p>
                    <p class="mb-0 fw-medium">{{ formatUserDateTime($admin->last_login_at) }}</p>
                  </div>
                </div>
              </div>
              @endif

              @if($admin->password_changed_at)
              <div class="col-md-6">
                <div class="d-flex align-items-center mb-3">
                  <div class="flex-shrink-0">
                    <i data-lucide="key" class="icon-sm text-primary"></i>
                  </div>
                  <div class="flex-grow-1 ms-3">
                    <p class="text-muted mb-0 small">Password Last Changed</p>
                    <p class="mb-0 fw-medium">{{ formatUserDateTime($admin->password_changed_at) }}</p>
                  </div>
                </div>
              </div>
              @endif

              <!-- Activity Statistics -->
              <div class="col-12 mt-4">
                <h6 class="mb-3">
                  <i data-lucide="bar-chart" class="icon-sm me-2"></i>
                  Activity Statistics
                </h6>
                <div class="row">
                  <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                      <h5 class="mb-1 text-primary">{{ $admin->loginLogs()->count() }}</h5>
                      <small class="text-muted">Total Logins</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                      <h5 class="mb-1 text-success">{{ $admin->loginLogs()->where('status', 'success')->count() }}</h5>
                      <small class="text-muted">Successful Logins</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                      <h5 class="mb-1 text-danger">{{ $admin->loginLogs()->where('status', 'failed')->count() }}</h5>
                      <small class="text-muted">Failed Logins</small>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="text-center p-3 bg-light rounded">
                      <h5 class="mb-1 text-info">{{ $admin->emailLogs()->count() }}</h5>
                      <small class="text-muted">Emails Sent</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Login History Tab -->
          <div class="tab-pane fade" id="login-history" role="tabpanel">
            <div class="row">
              <div class="col-12">
                <div class="card border-0">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                      <i data-lucide="log-in" class="icon-sm me-2"></i>
                      Login History
                    </h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshLoginHistory()">
                      <i data-lucide="refresh-cw" class="icon-sm me-1"></i>
                      Refresh
                    </button>
                  </div>
                  <div class="card-body">
                    <div class="row g-3">
                      <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                        <label for="loginSearchBox" class="form-label">Search</label>
                        <input type="text" id="loginSearchBox" class="form-control form-control-sm" placeholder="Search login history...">
                      </div>
                    </div>
                    <div class="table-responsive mb-3">
                      <table id="loginHistoryTable" class="table table-hover">
                        <thead>
                          <tr class="bg-dark">
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>Device</th>
                            <th>Browser</th>
                            <th>Location</th>
                            <th>Type</th>
                          </tr>
                        </thead>
                        <tbody>
                          <!-- Data will be loaded via AJAX -->
                        </tbody>
                      </table>
                    </div>
                    <div class="row gap-2 gap-sm-0">
                      <div class="col-12 col-sm-6 col-md-6 d-flex align-items-center justify-content-center justify-content-sm-start gap-2 flex-wrap">
                        <select id="loginCustomLength" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                          <option value="10">10</option>
                          <option value="25" selected>25</option>
                          <option value="50">50</option>
                          <option value="100">100</option>
                        </select>
                        <div id="loginCustomTableInfo" class="text-muted"></div>
                      </div>
                      <div class="col-12 col-sm-6 col-md-6 d-flex align-items-center justify-content-center justify-content-sm-end">
                        <nav id="loginCustomPaginationWrapper" aria-label="Page navigation example" class="">
                          <ul id="loginCustomPagination" class="pagination mb-0 pagination-sm"></ul>
                        </nav>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Email History Tab -->
          @if(isSuperAdmin() || hasPermission('admin.profile.email-history'))
          <div class="tab-pane fade" id="email-history" role="tabpanel">
            <div class="row">
              <div class="col-12">
                <div class="card border-0">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                      <i data-lucide="mail" class="icon-sm me-2"></i>
                      Email History
                    </h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshEmailHistory()">
                      <i data-lucide="refresh-cw" class="icon-sm me-1"></i>
                      Refresh
                    </button>
                  </div>
                  <div class="card-body">
                    <!-- Email Statistics -->
                    <div class="row mb-4">
                      <div class="col-md-2 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                          <h6 class="mb-1 text-primary">{{ $admin->emailLogs()->count() }}</h6>
                          <small class="text-muted">Total</small>
                        </div>
                      </div>
                      <div class="col-md-2 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                          <h6 class="mb-1 text-success">{{ $admin->emailLogs()->where('status', 'sent')->count() }}</h6>
                          <small class="text-muted">Sent</small>
                        </div>
                      </div>
                      <div class="col-md-2 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                          <h6 class="mb-1 text-info">{{ $admin->emailLogs()->where('status', 'delivered')->count() }}</h6>
                          <small class="text-muted">Delivered</small>
                        </div>
                      </div>
                      <div class="col-md-2 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                          <h6 class="mb-1 text-warning">{{ $admin->emailLogs()->whereNotNull('opened_at')->count() }}</h6>
                          <small class="text-muted">Opened</small>
                        </div>
                      </div>
                      <div class="col-md-2 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                          <h6 class="mb-1 text-secondary">{{ $admin->emailLogs()->whereNotNull('clicked_at')->count() }}</h6>
                          <small class="text-muted">Clicked</small>
                        </div>
                      </div>
                      <div class="col-md-2 mb-3">
                        <div class="text-center p-3 bg-light rounded">
                          <h6 class="mb-1 text-danger">{{ $admin->emailLogs()->where('status', 'failed')->count() }}</h6>
                          <small class="text-muted">Failed</small>
                        </div>
                      </div>
                    </div>

                    <div class="row g-3">
                      <div class="col-12 col-sm-6 col-md-3 mt-0 mb-2">
                        <label for="emailSearchBox" class="form-label">Search</label>
                        <input type="text" id="emailSearchBox" class="form-control form-control-sm" placeholder="Search email history...">
                      </div>
                    </div>
                    <div class="table-responsive mb-3">
                      <table id="emailHistoryTable" class="table table-hover">
                        <thead>
                          <tr class="bg-dark">
                            <th>Date & Time</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Recipient</th>
                            <th>Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                          <!-- Data will be loaded via AJAX -->
                        </tbody>
                      </table>
                    </div>
                    <div class="row gap-2 gap-sm-0">
                      <div class="col-12 col-sm-6 col-md-6 d-flex align-items-center justify-content-center justify-content-sm-start gap-2 flex-wrap">
                        <select id="emailCustomLength" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                          <option value="10">10</option>
                          <option value="25" selected>25</option>
                          <option value="50">50</option>
                          <option value="100">100</option>
                        </select>
                        <div id="emailCustomTableInfo" class="text-muted"></div>
                      </div>
                      <div class="col-12 col-sm-6 col-md-6 d-flex align-items-center justify-content-center justify-content-sm-end">
                        <nav id="emailCustomPaginationWrapper" aria-label="Page navigation example" class="">
                          <ul id="emailCustomPagination" class="pagination mb-0 pagination-sm"></ul>
                        </nav>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @else
          <!-- Email History Restricted Tab -->
          <div class="tab-pane fade" id="email-history-restricted" role="tabpanel">
            <div class="row">
              <div class="col-12">
                <div class="card border-0">
                  <div class="card-body text-center py-5">
                    <i data-lucide="lock" class="icon-lg text-muted mb-3"></i>
                    <h5 class="text-muted mb-2">Access Restricted</h5>
                    <p class="text-muted">You don't have permission to view email history. Contact your administrator for access.</p>
                    <div class="mt-3">
                      <span class="badge bg-warning">
                        <i data-lucide="shield-alert" class="icon-xs me-1"></i>
                        Super Admin Access Required
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/datatables.net/dataTables.min.js') }}"></script>
<script src="{{ asset('build/plugins/datatables.net-bs5/dataTables.bootstrap5.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
  $(document).ready(function() {
    // Initialize Login History DataTable
    const loginTable = $('#loginHistoryTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '{{ route("admin.profile.login-history") }}',
        type: 'GET'
      },
      pageLength: 25,
      dom: 'rt',
      responsive: true,
      order: [
        [0, 'desc']
      ],
      columnDefs: [{
          orderable: false,
          targets: [6]
        },
        {
          className: 'align-content-center',
          targets: '_all'
        }
      ],
      columns: [{
          data: 'login_at',
          name: 'login_at'
        },
        {
          data: 'status',
          name: 'status'
        },
        {
          data: 'ip_address',
          name: 'ip_address'
        },
        {
          data: 'device_type',
          name: 'device_type'
        },
        {
          data: 'browser',
          name: 'browser'
        },
        {
          data: 'location',
          name: 'location'
        },
        {
          data: 'type',
          name: 'type'
        }
      ],
      drawCallback: function() {
        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }
      }
    });

    // Initialize Email History DataTable (only if table exists)
    let emailTable = null;
    if ($('#emailHistoryTable').length > 0) {
      emailTable = $('#emailHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
          url: '{{ route("admin.profile.email-history") }}',
          type: 'GET'
        },
        pageLength: 25,
        dom: 'rt',
        responsive: true,
        order: [
          [0, 'desc']
        ],
        columnDefs: [{
            orderable: false,
            targets: [5]
          },
          {
            className: 'align-content-center',
            targets: '_all'
          }
        ],
        columns: [{
            data: 'sent_at',
            name: 'sent_at'
          },
          {
            data: 'subject',
            name: 'subject'
          },
          {
            data: 'type',
            name: 'type'
          },
          {
            data: 'status',
            name: 'status'
          },
          {
            data: 'recipient_email',
            name: 'recipient_email'
          },
          {
            data: 'actions',
            name: 'actions'
          }
        ],
        drawCallback: function() {
          if (typeof lucide !== 'undefined') {
            lucide.createIcons();
          }
        }
      });
    }

    // Custom pagination and info for Login History
    setupCustomDataTable(loginTable, 'login');

    // Custom pagination and info for Email History (only if table exists)
    if (emailTable) {
      setupCustomDataTable(emailTable, 'email');
    }

    function setupCustomDataTable(table, prefix) {
      table.on('draw', function() {
        var pageInfo = table.page.info();
        $(`#${prefix}CustomTableInfo`).html(
          `Showing ${pageInfo.start + 1} to ${pageInfo.end} of ${pageInfo.recordsTotal} entries`
        );

        var currentPage = pageInfo.page;
        var totalPages = pageInfo.pages;

        let paginationHtml = `
            <li class="page-item ${currentPage === 0 ? 'disabled' : ''}">
                <a class="page-link ${prefix}-prev-page" href="#" aria-label="Previous">
                    <i data-lucide="chevron-left"></i>
                </a>
            </li>`;

        let rangeStart = Math.max(currentPage - 2, 0);
        let rangeEnd = Math.min(rangeStart + 5, totalPages);

        for (let i = rangeStart; i < rangeEnd; i++) {
          paginationHtml += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link ${prefix}-page-btn" href="#" data-page="${i}">${i + 1}</a>
                </li>`;
        }

        paginationHtml += `
            <li class="page-item ${currentPage === totalPages - 1 ? 'disabled' : ''}">
                <a class="page-link ${prefix}-next-page" href="#" aria-label="Next">
                    <i data-lucide="chevron-right"></i>
                </a>
            </li>`;

        $(`#${prefix}CustomPagination`).html(paginationHtml);

        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }
      });

      // Trigger initial draw
      table.draw();

      // Custom pagination handlers
      $(document).on('click', `.${prefix}-prev-page`, function(e) {
        e.preventDefault();
        if (!$(this).parent().hasClass('disabled')) {
          table.page('previous').draw('page');
        }
      });

      $(document).on('click', `.${prefix}-next-page`, function(e) {
        e.preventDefault();
        if (!$(this).parent().hasClass('disabled')) {
          table.page('next').draw('page');
        }
      });

      $(document).on('click', `.${prefix}-page-btn`, function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        table.page(page).draw('page');
      });

      // Custom length selector
      $(`#${prefix}CustomLength`).on('change', function() {
        var length = $(this).val();
        table.page.len(length).draw();
      });

      // Custom search
      $(`#${prefix}SearchBox`).on('keyup', function() {
        table.search(this.value).draw();
      });
    }

    // Refresh functions
    window.refreshLoginHistory = function() {
      loginTable.ajax.reload();
    };

    window.refreshEmailHistory = function() {
      if (emailTable) {
        emailTable.ajax.reload();
      }
    };
  });
</script>
@endpush