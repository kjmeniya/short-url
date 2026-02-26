@extends('admin.layout.master')

@section('title', $title ?? 'Edit Profile')
@section('description', $description ?? 'Edit your admin profile information and account settings')
@section('keywords', $keywords ?? 'edit profile, update account, profile settings')

@push('plugin-styles')
<link href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.profile') }}">Profile</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Profile</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 col-xl-12 middle-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title">Edit Profile: {{ $admin->name }}</h6>

            @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            {{-- Google User Indication --}}
            @if($admin->isGoogleUser())
            <div class="alert alert-info d-flex align-items-center" role="alert">
              <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="-3 0 262 262" xmlns="http://www.w3.org/2000/svg">
                <path d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027" fill="#4285F4"></path>
                <path d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1" fill="#34A853"></path>
                <path d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782" fill="#FBBC05"></path>
                <path d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251" fill="#EB4335"></path>
              </svg>
              <div>
                <strong>Google Account Connected</strong><br>
                <small>This account is linked with Google. You can sign in using "Continue with Google" or with your password.</small>
              </div>
            </div>
            @endif

            {{-- Password Setup Prompt for Google Users --}}
            @if($admin->needsPasswordSetup())
            <div class="alert alert-warning d-flex align-items-center" role="alert">
              <i data-lucide="key" class="icon-sm me-2"></i>
              <div>
                <strong>Set Up Your Password</strong><br>
                <small>Since you signed up with Google, you don't have a password yet. Setting a password will allow you to sign in without Google if needed.</small>
              </div>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="forms-sample" id="profileEditForm">
              @csrf
              @method('PUT')

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                      id="name" name="name" value="{{ old('name', $admin->name) }}" placeholder="Enter full name"
                      maxlength="255" data-maxlength="true" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                      id="email" name="email" value="{{ old('email', $admin->email) }}" placeholder="Enter email address"
                      maxlength="255" data-maxlength="true" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              {{-- Password Section - Hidden for Google users who already have password --}}
              @if(!$admin->isGoogleUser() || $admin->needsPasswordSetup())
              <div class="row">
                <div class="col-sm-6">
                  @include('admin.partials.password-field', [
                  'name' => 'password',
                  'label' => $admin->needsPasswordSetup() ? 'Set Your Password' : 'New Password',
                  'placeholder' => $admin->needsPasswordSetup() ? 'Create a secure password' : 'Leave blank to keep current password',
                  'required' => false,
                  'showStrengthMeter' => true,
                  'autocomplete' => 'new-password',
                  'requirements' => [
                  'length' => ['enabled' => true, 'min' => 8],
                  'uppercase' => ['enabled' => false],
                  'lowercase' => ['enabled' => false],
                  'number' => ['enabled' => false],
                  'special' => ['enabled' => false]
                  ]
                  ])
                  @if($admin->needsPasswordSetup())
                  <small class="form-text text-success">
                    <i data-lucide="info" class="icon-xs me-1"></i>
                    Setting a password allows you to sign in without Google
                  </small>
                  @else
                  <small class="form-text text-muted">Leave blank to keep current password (min 8 characters if changing)</small>
                  @endif
                </div>
                <div class="col-sm-6">
                  @include('admin.partials.password-field', [
                  'name' => 'password_confirmation',
                  'label' => $admin->needsPasswordSetup() ? 'Confirm Your Password' : 'Confirm New Password',
                  'placeholder' => 'Confirm password',
                  'required' => false,
                  'showStrengthMeter' => false,
                  'autocomplete' => 'new-password'
                  ])
                  <small class="form-text text-muted">Required only if setting/changing password</small>
                </div>
              </div>
              @endif

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                      id="phone" name="phone" value="{{ old('phone', $admin->phone) }}" placeholder="Enter phone number"
                      maxlength="20" data-maxlength="true" pattern="[0-9+\-\s\(\)]+">
                    @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Format: +1234567890 or (123) 456-7890</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="text" class="form-control @error('date_of_birth') is-invalid @enderror"
                      id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $admin->date_of_birth?->format('Y-m-d')) }}"
                      placeholder="Select date of birth" readonly>
                    @error('date_of_birth')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Click to select date</small>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control @error('address') is-invalid @enderror"
                  id="address" name="address" rows="3" placeholder="Enter full address"
                  maxlength="500" data-maxlength="true">{{ old('address', $admin->address) }}</textarea>
                @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="timezone" class="form-label">Timezone</label>
                    <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                      <option value="">Use System Default ({{ app(\App\Services\SettingsService::class)->get('timezone', 'UTC') }})</option>
                      @foreach(getTimezoneOptions() as $value => $label)
                      <option value="{{ $value }}" {{ old('timezone', $admin->timezone) == $value ? 'selected' : '' }}>
                        {{ $label }}
                      </option>
                      @endforeach
                    </select>
                    @error('timezone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Your preferred timezone for date/time display</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="language" class="form-label">Language</label>
                    <select class="form-select @error('language') is-invalid @enderror" id="language" name="language">
                      @foreach(getLanguageOptions() as $value => $label)
                      <option value="{{ $value }}" {{ old('language', $admin->language ?? 'en') == $value ? 'selected' : '' }}>
                        {{ $label }}
                      </option>
                      @endforeach
                    </select>
                    @error('language')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Your preferred language</small>
                  </div>
                </div>
              </div>

              {{-- Avatar Upload with Cropper --}}
              @include('admin.partials.image-cropper', [
              'inputId' => 'avatar',
              'label' => 'Profile Picture',
              'currentImage' => $admin->avatar ? $admin->avatar_url : null
              ])

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <input type="text" class="form-control" id="role" value="{{ $admin->role ? $admin->role->display_name : 'No Role' }}" readonly>
                    <small class="form-text text-muted">Role cannot be changed from profile settings</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <input type="text" class="form-control" id="status" value="{{ $admin->is_active ? 'Active' : 'Inactive' }}" readonly>
                    <small class="form-text text-muted">Status is managed by system administrators</small>
                  </div>
                </div>

                {{-- Two-Factor Authentication Section --}}
                <div class="col-12 mt-4">
                  <h6 class="border-bottom pb-2 mb-3">
                    <i data-lucide="shield-check" class="icon-sm me-2"></i>
                    Two-Factor Authentication
                  </h6>
                  {{-- Step 1: Toggle Only --}}
                  <div class="mb-3">
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="two_factor_enabled"
                        {{ $admin->two_factor_enabled ? 'checked' : '' }}>
                      <label class="form-check-label" for="two_factor_enabled">
                        Enable Two-Factor Authentication
                      </label>
                    </div>
                    <small class="form-text text-muted">
                      Add an extra layer of security to your account with verification codes.
                    </small>
                  </div>

                  {{-- Step 2: Method Selection (Hidden by default) --}}
                  <div id="method-selection" style="display: none;">
                    <div class="mb-3">
                      <label class="form-label">Choose Authentication Method</label>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="radio" name="two_factor_method" id="method_email" value="email">
                            <label class="form-check-label" for="method_email">
                              <i data-lucide="mail" class="icon-sm me-2"></i>
                              <strong>Email Verification</strong>
                            </label>
                            <small class="form-text text-muted d-block mt-1">
                              Receive verification codes via email. Simple and convenient.
                            </small>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-check">
                            <input class="form-check-input" type="radio" name="two_factor_method" id="method_qr" value="qr_code">
                            <label class="form-check-label" for="method_qr">
                              <i data-lucide="qr-code" class="icon-sm me-2"></i>
                              <strong>Authenticator App</strong>
                            </label>
                            <small class="form-text text-muted d-block mt-1">
                              Use apps like Google Authenticator or Authy. More secure.
                            </small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  {{-- Step 3: Email Method Content --}}
                  <div id="email-setup" style="display: none;">
                    <div class="alert alert-info">
                      <i data-lucide="mail" class="icon-sm me-2"></i>
                      <strong>Email Verification Setup</strong>
                      <p class="mb-0 mt-2">
                        We'll send a 6-digit verification code to your email address ({{ $admin->email }})
                        to verify and enable two-factor authentication.
                      </p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="send-email-code">
                      <i data-lucide="mail" class="icon-sm me-1"></i>
                      Send Verification Code
                    </button>
                  </div>

                  {{-- Step 4: Email Verification Section --}}
                  <div id="email-verify" style="display: none;">
                    <div class="alert alert-success">
                      <i data-lucide="check-circle" class="icon-sm me-2"></i>
                      <strong>Verification code sent!</strong>
                      <p class="mb-0 mt-2">Please check your email and enter the 6-digit code below.</p>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="email_verification_code" class="form-label">Verification Code</label>
                          <input type="text" class="form-control" id="email_verification_code"
                            placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}">
                          <small class="form-text text-muted">Enter the code from your email</small>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label class="form-label">&nbsp;</label>
                          <div>
                            <button type="button" class="btn btn-success btn-sm" id="verify-email-2fa">
                              <i data-lucide="check" class="icon-sm me-1"></i>
                              Verify & Enable
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  {{-- Step 3: QR Code Method Content --}}
                  <div id="qr-setup" style="display: none;">
                    <div class="alert alert-info">
                      <i data-lucide="qr-code" class="icon-sm me-2"></i>
                      <strong>Authenticator App Setup</strong>
                      <p class="mb-0 mt-2">
                        Scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.)
                        and enter the verification code.
                      </p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" id="generate-qr">
                      <i data-lucide="qr-code" class="icon-sm me-1"></i>
                      Generate QR Code
                    </button>
                    <div id="qr-code-container" class="mt-3"></div>
                    <div id="qr-verify" style="display: none;">
                      <div class="row mt-3">
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label for="qr_verification_code" class="form-label">Verification Code</label>
                            <input type="text" class="form-control" id="qr_verification_code"
                              placeholder="Enter 6-digit code" maxlength="6" pattern="[0-9]{6}">
                            <small class="form-text text-muted">Enter the code from your authenticator app</small>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                              <button type="button" class="btn btn-success btn-sm" id="verify-qr-2fa">
                                <i data-lucide="check" class="icon-sm me-1"></i>
                                Verify & Enable
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  {{-- Step 8: Two-Factor Enabled Section --}}
                  <div id="two-factor-enabled" class="mt-3" style="display: <?= $admin->two_factor_enabled ? 'block' : 'none' ?>;">
                    <div class="alert alert-success">
                      <i data-lucide="shield-check" class="icon-sm me-2"></i>
                      <strong>Two-Factor Authentication is enabled</strong>
                      @if($admin->two_factor_enabled)
                      using <strong>{{ $admin->two_factor_method === 'email' ? 'Email Verification' : 'Authenticator App' }}</strong>.
                      @endif
                      Your account is protected with an additional security layer.
                    </div>

                    <div class="row">
                      <div class="col-md-3">
                        <button type="button" class="btn btn-outline-info btn-sm" id="change-method">
                          <i data-lucide="refresh-ccw" class="icon-sm me-1"></i>
                          Change Method
                        </button>
                      </div>
                      <div class="col-md-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="view-recovery-codes">
                          <i data-lucide="key" class="icon-sm me-1"></i>
                          View Recovery Codes
                        </button>
                      </div>
                      <div class="col-md-3">
                        <button type="button" class="btn btn-outline-warning btn-sm" id="regenerate-codes">
                          <i data-lucide="refresh-cw" class="icon-sm me-1"></i>
                          Regenerate Recovery Codes
                        </button>
                      </div>
                      <div class="col-md-3">
                        <button type="button" class="btn btn-outline-danger btn-sm" id="disable-2fa">
                          <i data-lucide="shield-off" class="icon-sm me-1"></i>
                          Disable Two-Factor Authentication
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('admin.profile') }}" class="btn btn-secondary btn-sm">
                      <i data-lucide="x" class="icon-sm me-1"></i>
                      <span class="d-none d-sm-inline">Cancel</span>
                      <span class="d-sm-none">Cancel</span>
                    </a>
                    <button type="button" class="btn btn-primary btn-sm" id="submitBtn">
                      <i data-lucide="save" class="icon-sm me-1"></i>
                      <span class="d-none d-sm-inline">Update Profile</span>
                      <span class="d-sm-none">Update</span>
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        {{-- Google Account Management Section --}}
        @if($admin->isGoogleUser())
        <div class="card mt-4">
          <div class="card-body">
            <h6 class="card-title text-warning">
              <i data-lucide="unlink" class="icon-sm me-2"></i>
              Google Account Management
            </h6>
            <p class="text-muted mb-3">
              Manage your Google account connection. Disconnecting will require email verification.
            </p>

            <div class="alert alert-warning" role="alert">
              <i data-lucide="alert-triangle" class="icon-sm me-2"></i>
              <strong>Important:</strong> Disconnecting your Google account will require you to use your password to sign in.
              @if($admin->needsPasswordSetup())
              You must set a password first before disconnecting.
              @endif
            </div>

            <button type="button" class="btn btn-warning btn-sm" id="disconnectGoogleBtn"
              @if($admin->needsPasswordSetup()) disabled @endif>
              <i data-lucide="unlink" class="icon-sm me-1"></i>
              Disconnect Google Account
            </button>
          </div>
        </div>
        @endif

        {{-- Account Deletion Section --}}
        <div class="card mt-4 border-danger">
          <div class="card-body">
            <h6 class="card-title text-danger">
              <i data-lucide="trash-2" class="icon-sm me-2"></i>
              Delete Account
            </h6>
            <p class="text-muted mb-3">
              Permanently delete your account and all associated data. This action cannot be undone.
            </p>

            <div class="alert alert-danger" role="alert">
              <i data-lucide="alert-triangle" class="icon-sm me-2"></i>
              <strong>Warning:</strong> This will permanently delete your account, profile data, and all associated information.
              Email verification will be required to confirm this action.
            </div>

            <button type="button" class="btn btn-danger btn-sm" id="deleteAccountBtn">
              <i data-lucide="trash-2" class="icon-sm me-1"></i>
              Delete My Account
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('build/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
<script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
@vite(['resources/js/admin/validation/profile.js'])
<script>
  $(document).ready(function() {
    // Two-Factor Authentication functionality
    const twoFactorToggle = $('#two_factor_enabled');
    const setupSection = $('#two-factor-setup');
    const enabledSection = $('#two-factor-enabled');
    const methodSelection = $('#method-selection');
    const emailSetup = $('#email-setup');
    const qrSetup = $('#qr-setup');

    // Initialize page state based on current 2FA status
    <?php if ($admin->two_factor_enabled): ?>
      enabledSection.show();
      methodSelection.hide();
      $('#email-setup, #qr-setup, #email-verify, #qr-verify').hide();
    <?php else: ?>
      enabledSection.hide();
      methodSelection.hide();
      $('#email-setup, #qr-setup, #email-verify, #qr-verify').hide();
    <?php endif; ?>

    // Step 2: Toggle change - show method selection
    twoFactorToggle.on('change', function() {
      if ($(this).is(':checked')) {
        // Show method selection only
        methodSelection.show();
        // Hide other sections
        $('#email-setup, #qr-setup').hide();
        enabledSection.hide();
      } else {
        // Ask for confirmation if 2FA is currently enabled
        if (<?= $admin->two_factor_enabled ? 'true' : 'false' ?>) {
          // Revert toggle and ask for confirmation
          $(this).prop('checked', true);
          confirmDisable2FA();
        } else {
          // Hide everything
          methodSelection.hide();
          $('#email-setup, #qr-setup').hide();
          enabledSection.hide();
        }
      }
    });

    // Step 3: Handle method selection
    $('input[name="two_factor_method"]').on('change', function() {
      const selectedMethod = $(this).val();

      // Hide all method content
      $('#email-setup, #qr-setup, #email-verify, #qr-verify').hide();

      // Show selected method content
      if (selectedMethod === 'email') {
        $('#email-setup').show();
      } else if (selectedMethod === 'qr_code') {
        $('#qr-setup').show();
      }
    });

    // Step 4: Send email verification code
    $('#send-email-code').on('click', function() {
      const btn = $(this);
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Sending...');

      $.post('{{ route("admin.profile.two-factor.send-email-code") }}', {
          _token: '{{ csrf_token() }}',
          setup: true
        })
        .done(function(response) {
          if (response.success) {
            // Hide send section, show verify section
            $('#email-setup').hide();
            $('#email-verify').show();

            // Swal.fire({
            //   title: 'Code Sent!',
            //   text: 'A verification code has been sent to your email address.',
            //   icon: 'success',
            //   confirmButtonText: 'OK',
            //   customClass: {
            //     confirmButton: 'btn btn-primary btn-sm'
            //   },
            //   buttonsStyling: false,
            //   didOpen: () => {
            //     lucide.createIcons();
            //   }
            // });
          } else {
            Swal.fire({
              title: 'Error!',
              text: response.message || 'Failed to send verification code.',
              icon: 'error',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          }
        })
        .fail(function(xhr) {
          let errorMessage = 'Failed to send verification code. Please try again.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          }
          Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
            customClass: {
              confirmButton: 'btn btn-primary btn-sm'
            },
            buttonsStyling: false,
            didOpen: () => {
              lucide.createIcons();
            }
          });
        })
        .always(function() {
          btn.prop('disabled', false).html('<i data-lucide="mail" class="icon-sm me-1"></i>Send Verification Code');
          lucide.createIcons();
        });
    });

    // Step 5: Verify email and enable 2FA (only on button click)
    $('#verify-email-2fa').on('click', function() {
      const btn = $(this);
      const code = $('#email_verification_code').val();

      if (!code || code.length !== 6) {
        Swal.fire({
          title: 'Invalid Code',
          text: 'Please enter a valid 6-digit verification code.',
          icon: 'warning',
          confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
          customClass: {
            confirmButton: 'btn btn-primary btn-sm'
          },
          buttonsStyling: false,
          didOpen: () => {
            lucide.createIcons();
          }
        });
        return;
      }

      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Verifying...');

      $.post('{{ route("admin.profile.two-factor.enable") }}', {
          _token: '{{ csrf_token() }}',
          method: 'email',
          email_verification_code: code
        })
        .done(function(response) {
          if (response.success) {
            // Hide all setup sections and show enabled section
            methodSelection.hide();
            $('#email-verify').hide();
            enabledSection.show();
            twoFactorToggle.prop('checked', true);

            // Show recovery codes
            showRecoveryCodes(response.recovery_codes);

            Swal.fire({
              title: 'Success!',
              text: 'Email-based two-factor authentication has been enabled successfully.',
              icon: 'success',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: response.message || 'Failed to enable two-factor authentication.',
              icon: 'error',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          }
        })
        .fail(function(xhr) {
          let errorMessage = 'Failed to enable two-factor authentication. Please try again.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          }
          Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
            customClass: {
              confirmButton: 'btn btn-primary btn-sm'
            },
            buttonsStyling: false,
            didOpen: () => {
              lucide.createIcons();
            }
          });
        })
        .always(function() {
          btn.prop('disabled', false).html('<i data-lucide="check" class="icon-sm me-1"></i>Verify & Enable');
          lucide.createIcons();
        });
    });

    // Step 6: Generate QR Code
    $('#generate-qr').on('click', function() {
      const btn = $(this);
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Generating...');

      $.post('{{ route("admin.profile.two-factor.generate-qr") }}', {
          _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
          if (response.success) {
            $('#qr-code-container').html(response.qr_code);
            $('#qr-verify').show();
            btn.hide();

            Swal.fire({
              title: 'QR Code Generated!',
              text: 'Scan the QR code with your authenticator app and enter the verification code.',
              icon: 'success',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: response.message || 'Failed to generate QR code.',
              icon: 'error',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          }
        })
        .always(function() {
          btn.prop('disabled', false).html('<i data-lucide="qr-code" class="icon-sm me-1"></i>Generate QR Code');
          lucide.createIcons();
        });
    });

    // Step 6: Verify QR and enable 2FA
    $('#verify-qr-2fa').on('click', function() {
      const btn = $(this);
      const code = $('#qr_verification_code').val();

      if (!code || code.length !== 6) {
        Swal.fire({
          title: 'Invalid Code',
          text: 'Please enter a valid 6-digit verification code.',
          icon: 'warning',
          confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
          customClass: {
            confirmButton: 'btn btn-primary btn-sm'
          },
          buttonsStyling: false,
          didOpen: () => {
            lucide.createIcons();
          }
        });
        return;
      }

      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Verifying...');

      $.post('{{ route("admin.profile.two-factor.enable") }}', {
          _token: '{{ csrf_token() }}',
          method: 'qr_code',
          verification_code: code
        })
        .done(function(response) {
          if (response.success) {
            // Hide all setup sections and show enabled section
            methodSelection.hide();
            $('#qr-setup').hide();
            enabledSection.show();
            twoFactorToggle.prop('checked', true);

            // Show recovery codes
            showRecoveryCodes(response.recovery_codes);

            Swal.fire({
              title: 'Success!',
              text: 'Authenticator-based two-factor authentication has been enabled successfully.',
              icon: 'success',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: response.message || 'Failed to enable two-factor authentication.',
              icon: 'error',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          }
        })
        .always(function() {
          btn.prop('disabled', false).html('<i data-lucide="check" class="icon-sm me-1"></i>Verify & Enable');
          lucide.createIcons();
        });
    });

    // Step 7: Confirmation before disabling 2FA
    function confirmDisable2FA() {
      Swal.fire({
        title: 'Disable Two-Factor Authentication?',
        text: 'Are you sure you want to disable two-factor authentication? This will make your account less secure.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="shield-off" class="icon-sm me-1"></i>Yes, Disable',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-danger btn-sm me-2',
          cancelButton: 'btn btn-secondary btn-sm'
        },
        buttonsStyling: false,
        didOpen: () => {
          lucide.createIcons();
        }
      }).then((result) => {
        if (result.isConfirmed) {
          disable2FA();
        } else {
          // Keep toggle checked
          twoFactorToggle.prop('checked', true);
        }
      });
    }

    function disable2FA() {
      $.post('{{ route("admin.profile.two-factor.disable") }}', {
          _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
          if (response.success) {
            // Update UI
            twoFactorToggle.prop('checked', false);
            methodSelection.hide();
            $('#email-setup, #qr-setup, #email-verify, #qr-verify').hide();
            enabledSection.hide();

            Swal.fire({
              title: 'Disabled!',
              text: 'Two-factor authentication has been disabled.',
              icon: 'success',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: response.message || 'Failed to disable two-factor authentication.',
              icon: 'error',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          }
        });
    }

    // Step 8: Change verification method
    $('#change-method').on('click', function() {
      Swal.fire({
        title: 'Change Verification Method?',
        text: 'This will disable your current 2FA and allow you to set up a new method.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="refresh-ccw" class="icon-sm me-1"></i>Yes, Change',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-warning btn-sm me-2',
          cancelButton: 'btn btn-secondary btn-sm'
        },
        buttonsStyling: false,
        didOpen: () => {
          lucide.createIcons();
        }
      }).then((result) => {
        if (result.isConfirmed) {
          // Disable current 2FA and show method selection
          $.post('{{ route("admin.profile.two-factor.disable") }}', {
              _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
              if (response.success) {
                // Show method selection
                enabledSection.hide();
                methodSelection.show();
                // Reset all method content
                $('#email-setup, #qr-setup, #email-verify, #qr-verify').hide();
                $('input[name="two_factor_method"]').prop('checked', false);

                Swal.fire({
                  title: 'Your Old Method Disabled!',
                  text: 'Please select a new verification method.',
                  icon: 'success',
                  confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
                  customClass: {
                    confirmButton: 'btn btn-primary btn-sm'
                  },
                  buttonsStyling: false,
                  didOpen: () => {
                    lucide.createIcons();
                  }
                });
              }
            });
        }
      });
    });

    // Disable 2FA button
    $('#disable-2fa').on('click', function() {
      confirmDisable2FA();
    });

    // Recovery codes function
    function showRecoveryCodes(recoveryCodes) {
      let recoveryCodesHtml = '<div class="alert alert-warning mt-3 mb-3">';
      recoveryCodesHtml += '<h6><i data-lucide="key" class="icon-sm me-2"></i>Recovery Codes</h6>';
      recoveryCodesHtml += '<p>Save these recovery codes in a safe place. You can use them to access your account if you lose access to your two-factor authentication method:</p>';
      recoveryCodesHtml += '<div class="row">';

      recoveryCodes.forEach(function(code, index) {
        if (index % 2 === 0) recoveryCodesHtml += '<div class="col-md-6">';
        recoveryCodesHtml += '<div class="mb-2"><code class="bg-light p-2 d-block text-center">' + code + '</code></div>';
        if (index % 2 === 1 || index === recoveryCodes.length - 1) recoveryCodesHtml += '</div>';
      });

      recoveryCodesHtml += '</div>';
      recoveryCodesHtml += '<div class="mt-3">';
      recoveryCodesHtml += '<button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="downloadCodes()">';
      recoveryCodesHtml += '<i data-lucide="download" class="icon-sm me-1"></i>Download Codes</button>';
      recoveryCodesHtml += '<button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyCodes()">';
      recoveryCodesHtml += '<i data-lucide="copy" class="icon-sm me-1"></i>Copy to Clipboard</button>';
      recoveryCodesHtml += '</div>';
      recoveryCodesHtml += '<p class="mt-3 mb-0"><strong><i data-lucide="alert-triangle" class="icon-sm me-1"></i>Important: These codes will not be shown again!</strong></p>';
      recoveryCodesHtml += '</div>';

      // Store codes globally
      window.currentRecoveryCodes = recoveryCodes;

      // Show in SweetAlert
      Swal.fire({
        title: 'Recovery Codes',
        html: recoveryCodesHtml,
        width: '600px',
        showConfirmButton: true,
        confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>I have saved these codes',
        customClass: {
          confirmButton: 'btn btn-primary btn-sm'
        },
        buttonsStyling: false,
        didOpen: () => {
          lucide.createIcons();
        }
      });
    }

    // Global functions for recovery codes
    window.downloadCodes = function() {
      if (window.currentRecoveryCodes) {
        const content = 'Two-Factor Authentication Recovery Codes\n' +
          '========================================\n\n' +
          'Save these codes in a safe place.\n\n' +
          'Recovery Codes:\n' +
          window.currentRecoveryCodes.map((code, index) => `${index + 1}. ${code}`).join('\n') +
          '\n\nGenerated on: ' + new Date().toLocaleString();

        const blob = new Blob([content], {
          type: 'text/plain'
        });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'two-factor-recovery-codes.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
      }
    };

    window.copyCodes = function() {
      if (window.currentRecoveryCodes) {
        const content = window.currentRecoveryCodes.join('\n');

        if (navigator.clipboard) {
          navigator.clipboard.writeText(content);
        } else {
          const textArea = document.createElement('textarea');
          textArea.value = content;
          document.body.appendChild(textArea);
          textArea.select();
          document.execCommand('copy');
          document.body.removeChild(textArea);
        }

        Swal.fire({
          title: 'Copied!',
          text: 'Recovery codes copied to clipboard.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
          didOpen: () => {
            lucide.createIcons();
          }
        });
      }
    };



    // Generate QR Code
    $('#generate-qr').on('click', function() {
      const btn = $(this);
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Generating...');

      $.post('{{ route("admin.profile.two-factor.generate-qr") }}', {
          _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
          if (response.success) {
            $('#qr-code-container').html(response.qr_code);
            btn.html('<i data-lucide="refresh-cw" class="icon-sm me-1"></i>Regenerate QR Code');
            lucide.createIcons();
          } else {
            Swal.fire({
              title: 'Error!',
              text: response.message || 'Failed to generate QR code.',
              icon: 'error',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          }
        })
        .fail(function(xhr) {
          let errorMessage = 'Failed to generate QR code. Please try again.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          }
          Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
            customClass: {
              confirmButton: 'btn btn-primary btn-sm'
            },
            buttonsStyling: false,
            didOpen: () => {
              lucide.createIcons();
            }
          });
        })
        .always(function() {
          btn.prop('disabled', false);
        });
    });



    // Disable 2FA
    $('#disable-2fa').on('click', function() {
      const btn = $(this);

      Swal.fire({
        title: 'Disable Two-Factor Authentication?',
        text: 'This will make your account less secure. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="shield-off" class="icon-sm me-1"></i>Yes, disable it',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-danger btn-sm me-2',
          cancelButton: 'btn btn-secondary btn-sm'
        },
        buttonsStyling: false,
        didOpen: () => {
          lucide.createIcons();
        }
      }).then((result) => {
        if (result.isConfirmed) {
          btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Disabling...');

          $.post('{{ route("admin.profile.two-factor.disable") }}', {
              _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
              if (response.success) {
                setupSection.show();
                enabledSection.hide();
                twoFactorToggle.prop('checked', false);
                $('#qr-code-container').empty();
                $('#verification_code').val('');

                Swal.fire({
                  title: 'Disabled!',
                  text: 'Two-factor authentication has been disabled.',
                  icon: 'success',
                  confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
                  customClass: {
                    confirmButton: 'btn btn-primary btn-sm'
                  },
                  buttonsStyling: false,
                  didOpen: () => {
                    lucide.createIcons();
                  }
                });
              } else {
                Swal.fire({
                  title: 'Error!',
                  text: response.message || 'Failed to disable two-factor authentication.',
                  icon: 'error',
                  confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
                  customClass: {
                    confirmButton: 'btn btn-primary btn-sm'
                  },
                  buttonsStyling: false,
                  didOpen: () => {
                    lucide.createIcons();
                  }
                });
              }
            })
            .fail(function(xhr) {
              let errorMessage = 'Failed to disable two-factor authentication. Please try again.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
              Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
                customClass: {
                  confirmButton: 'btn btn-primary btn-sm'
                },
                buttonsStyling: false,
                didOpen: () => {
                  lucide.createIcons();
                }
              });
            })
            .always(function() {
              btn.prop('disabled', false).html('<i data-lucide="shield-off" class="icon-sm me-1"></i>Disable Two-Factor Authentication');
              lucide.createIcons();
            });
        }
      });
    });

    // Regenerate Recovery Codes
    $('#regenerate-codes').on('click', function() {
      const btn = $(this);

      Swal.fire({
        title: 'Regenerate Recovery Codes?',
        text: 'This will invalidate your current recovery codes and generate new ones.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="refresh-cw" class="icon-sm me-1"></i>Yes, regenerate',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-sm btn-warning me-2',
          cancelButton: 'btn btn-sm btn-secondary'
        },
        buttonsStyling: false,
        didOpen: () => {
          lucide.createIcons();
        }
      }).then((result) => {
        if (result.isConfirmed) {
          btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Regenerating...');

          $.post('{{ route("admin.profile.two-factor.regenerate-codes") }}', {
              _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
              if (response.success) {
                // Show new recovery codes
                let recoveryCodesHtml = '<div class="alert alert-warning mt-3"><h6>New Recovery Codes</h6><p>Save these new recovery codes in a safe place:</p><ul>';
                response.recovery_codes.forEach(function(code) {
                  recoveryCodesHtml += '<li><code>' + code + '</code></li>';
                });
                recoveryCodesHtml += '</ul><p><strong>Your old recovery codes are no longer valid!</strong></p></div>';

                // Remove old recovery codes and add new ones
                enabledSection.find('.alert-warning').remove();
                enabledSection.prepend(recoveryCodesHtml);

                Swal.fire({
                  title: 'Success!',
                  text: 'Recovery codes have been regenerated.',
                  icon: 'success',
                  confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
                  customClass: {
                    confirmButton: 'btn btn-primary btn-sm'
                  },
                  buttonsStyling: false,
                  didOpen: () => {
                    lucide.createIcons();
                  }
                });
              } else {
                Swal.fire({
                  title: 'Error!',
                  text: response.message || 'Failed to regenerate recovery codes.',
                  icon: 'error',
                  confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
                  customClass: {
                    confirmButton: 'btn btn-primary btn-sm'
                  },
                  buttonsStyling: false,
                  didOpen: () => {
                    lucide.createIcons();
                  }
                });
              }
            })
            .fail(function(xhr) {
              let errorMessage = 'Failed to regenerate recovery codes. Please try again.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
              }
              Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
                customClass: {
                  confirmButton: 'btn btn-sm btn-primary'
                },
                buttonsStyling: false,
                didOpen: () => {
                  lucide.createIcons();
                }
              });
            })
            .always(function() {
              btn.prop('disabled', false).html('<i data-lucide="refresh-cw" class="icon-sm me-1"></i>Regenerate Recovery Codes');
              lucide.createIcons();
            });
        }
      });
    });

    // Reset two-factor form
    function resetTwoFactorForm() {
      $('#email_verification_code').val('').prop('disabled', true);
      $('#verify-email-2fa').prop('disabled', true);
      $('#qr-code-container').empty();
      $('input[name="two_factor_method"]').prop('checked', false);
      $('#email-setup, #qr-setup').hide();
    }

    // Disable two-factor authentication
    function disableTwoFactor() {
      $.post('{{ route("admin.profile.two-factor.disable") }}', {
          _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
          if (response.success) {
            // Update UI to reflect disabled state
            setupSection.hide();
            enabledSection.hide();
            methodSelection.hide();
            resetTwoFactorForm();

            Swal.fire({
              title: 'Disabled!',
              text: 'Two-factor authentication has been disabled.',
              icon: 'success',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          }
        })
        .fail(function(xhr) {
          let errorMessage = 'Failed to disable two-factor authentication.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          }
          Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
            customClass: {
              confirmButton: 'btn btn-primary btn-sm'
            },
            buttonsStyling: false,
            didOpen: () => {
              lucide.createIcons();
            }
          });
          // Revert toggle state
          twoFactorToggle.prop('checked', true);
        });
    }

    // Format verification code inputs (numbers only)
    $('#email_verification_code, #qr_verification_code, #verification_code').on('input', function() {
      const value = $(this).val().replace(/[^0-9]/g, '');
      $(this).val(value);
    });

    // Download recovery codes functionality
    $(document).on('click', '#download-recovery-codes', function() {
      if (window.recoveryCodesData) {
        const content = 'Two-Factor Authentication Recovery Codes\n' +
          '========================================\n\n' +
          'Save these codes in a safe place. You can use them to access your account\n' +
          'if you lose access to your two-factor authentication method.\n\n' +
          'Recovery Codes:\n' +
          window.recoveryCodesData.map((code, index) => `${index + 1}. ${code}`).join('\n') +
          '\n\nIMPORTANT: These codes will not be shown again!\n' +
          'Generated on: ' + new Date().toLocaleString();

        const blob = new Blob([content], {
          type: 'text/plain'
        });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'two-factor-recovery-codes.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);

        Swal.fire({
          title: 'Downloaded!',
          text: 'Recovery codes have been downloaded successfully.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
          didOpen: () => {
            lucide.createIcons();
          }
        });
      }
    });

    // Copy recovery codes to clipboard
    $(document).on('click', '#copy-recovery-codes', function() {
      if (window.recoveryCodesData) {
        const content = window.recoveryCodesData.join('\n');

        if (navigator.clipboard) {
          navigator.clipboard.writeText(content).then(function() {
            Swal.fire({
              title: 'Copied!',
              text: 'Recovery codes have been copied to clipboard.',
              icon: 'success',
              timer: 2000,
              showConfirmButton: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          });
        } else {
          // Fallback for older browsers
          const textArea = document.createElement('textarea');
          textArea.value = content;
          document.body.appendChild(textArea);
          textArea.select();
          document.execCommand('copy');
          document.body.removeChild(textArea);

          Swal.fire({
            title: 'Copied!',
            text: 'Recovery codes have been copied to clipboard.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false,
            didOpen: () => {
              lucide.createIcons();
            }
          });
        }
      }
    });

    // View recovery codes
    $('#view-recovery-codes').on('click', function() {
      const btn = $(this);
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');

      $.post('{{ route("admin.profile.two-factor.regenerate-codes") }}', {
          _token: '{{ csrf_token() }}',
          view_only: true
        })
        .done(function(response) {
          if (response.success) {
            let recoveryCodesHtml = '<div class="row">';
            response.recovery_codes.forEach(function(code, index) {
              if (index % 2 === 0) recoveryCodesHtml += '<div class="col-md-6">';
              recoveryCodesHtml += '<div class="mb-2"><code class="bg-light p-2 d-block text-center">' + code + '</code></div>';
              if (index % 2 === 1 || index === response.recovery_codes.length - 1) recoveryCodesHtml += '</div>';
            });
            recoveryCodesHtml += '</div>';

            recoveryCodesHtml += '<div class="mt-3 text-center">';
            recoveryCodesHtml += '<button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="downloadRecoveryCodes()">';
            recoveryCodesHtml += '<i data-lucide="download" class="icon-sm me-1"></i>Download</button>';
            recoveryCodesHtml += '<button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyRecoveryCodes()">';
            recoveryCodesHtml += '<i data-lucide="copy" class="icon-sm me-1"></i>Copy</button>';
            recoveryCodesHtml += '</div>';

            // Store codes for download/copy
            window.currentRecoveryCodes = response.recovery_codes;

            Swal.fire({
              title: 'Recovery Codes',
              html: recoveryCodesHtml,
              width: '600px',
              showConfirmButton: true,
              confirmButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Close',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          } else {
            Swal.fire({
              title: 'Error!',
              text: response.message || 'Failed to load recovery codes.',
              icon: 'error',
              confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
              customClass: {
                confirmButton: 'btn btn-primary btn-sm'
              },
              buttonsStyling: false,
              didOpen: () => {
                lucide.createIcons();
              }
            });
          }
        })
        .fail(function(xhr) {
          let errorMessage = 'Failed to load recovery codes.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
          }
          Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
            customClass: {
              confirmButton: 'btn btn-primary btn-sm'
            },
            buttonsStyling: false,
            didOpen: () => {
              lucide.createIcons();
            }
          });
        })
        .always(function() {
          btn.prop('disabled', false).html('<i data-lucide="key" class="icon-sm me-1"></i>View Recovery Codes');
          lucide.createIcons();
        });
    });

    // Global functions for SweetAlert buttons
    window.downloadRecoveryCodes = function() {
      if (window.currentRecoveryCodes) {
        const content = 'Two-Factor Authentication Recovery Codes\n' +
          '========================================\n\n' +
          'Save these codes in a safe place. You can use them to access your account\n' +
          'if you lose access to your two-factor authentication method.\n\n' +
          'Recovery Codes:\n' +
          window.currentRecoveryCodes.map((code, index) => `${index + 1}. ${code}`).join('\n') +
          '\n\nGenerated on: ' + new Date().toLocaleString();

        const blob = new Blob([content], {
          type: 'text/plain'
        });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'two-factor-recovery-codes.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
      }
    };

    window.copyRecoveryCodes = function() {
      if (window.currentRecoveryCodes) {
        const content = window.currentRecoveryCodes.join('\n');

        if (navigator.clipboard) {
          navigator.clipboard.writeText(content);
        } else {
          const textArea = document.createElement('textarea');
          textArea.value = content;
          document.body.appendChild(textArea);
          textArea.select();
          document.execCommand('copy');
          document.body.removeChild(textArea);
        }

        // Show brief success message
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 2000
        });
        Toast.fire({
          icon: 'success',
          title: 'Copied to clipboard!'
        });
      }
    };

    // Google Account Disconnect
    $('#disconnectGoogleBtn').on('click', function() {
      Swal.fire({
        title: 'Disconnect Google Account?',
        text: 'You will need to verify your email address to confirm this action. After disconnecting, you will need to use your password to sign in.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f39c12',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i data-lucide="mail" class="icon-sm me-1"></i>Send Verification Email',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-warning btn-sm',
          cancelButton: 'btn btn-secondary btn-sm'
        },
        buttonsStyling: false,
        allowOutsideClick: false,
        didOpen: () => {
          if (typeof lucide !== 'undefined') lucide.createIcons();
        },
        preConfirm: () => {
          // Show spinner in button
          const confirmBtn = Swal.getConfirmButton();
          const originalText = confirmBtn.innerHTML;
          confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
          confirmBtn.disabled = true;

          // Send OTP for Google disconnect
          return $.ajax({
            url: '{{ route("admin.profile.google.disconnect.request") }}',
            method: 'POST',
            data: {
              _token: '{{ csrf_token() }}'
            }
          }).then(function(response) {
            if (response.success) {
              return response;
            } else {
              throw new Error(response.message || 'Failed to send verification email.');
            }
          }).catch(function(xhr) {
            const response = xhr.responseJSON;
            throw new Error(response?.message || 'Failed to send verification email.');
          });
        }
      }).then((result) => {
        if (result.isConfirmed && result.value) {
          // Close current modal and show OTP verification modal
          showOtpVerificationModal('disconnect', result.value.message);
        }
      }).catch((error) => {
        if (error !== 'cancel') {
          Swal.fire('Error', error.message || 'Failed to send verification email.', 'error');
        }
      });
    });

    // Account Deletion
    $('#deleteAccountBtn').on('click', function() {
      Swal.fire({
        title: 'Delete Account?',
        html: '<p>This will <strong>permanently delete</strong> your account and all associated data.</p><p>You will need to verify your email address to confirm this action.</p><p class="text-danger"><strong>This action cannot be undone!</strong></p>',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i data-lucide="mail" class="icon-sm me-1"></i>Send Verification Email',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-danger btn-sm me-2',
          cancelButton: 'btn btn-secondary btn-sm'
        },
        buttonsStyling: false,
        allowOutsideClick: false,
        didOpen: () => {
          if (typeof lucide !== 'undefined') lucide.createIcons();
        },
        preConfirm: () => {
          // Show spinner in button
          const confirmBtn = Swal.getConfirmButton();
          const originalText = confirmBtn.innerHTML;
          confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
          confirmBtn.disabled = true;

          // Send OTP for account deletion
          return $.ajax({
            url: '{{ route("admin.profile.delete.request") }}',
            method: 'POST',
            data: {
              _token: '{{ csrf_token() }}'
            }
          }).then(function(response) {
            if (response.success) {
              return response;
            } else {
              throw new Error(response.message || 'Failed to send verification email.');
            }
          }).catch(function(xhr) {
            const response = xhr.responseJSON;
            throw new Error(response?.message || 'Failed to send verification email.');
          });
        }
      }).then((result) => {
        if (result.isConfirmed && result.value) {
          // Close current modal and show OTP verification modal
          showOtpVerificationModal('delete', result.value.message);
        }
      }).catch((error) => {
        if (error !== 'cancel') {
          Swal.fire('Error', error.message || 'Failed to send verification email.', 'error');
        }
      });
    });

    // OTP Verification Modal
    function showOtpVerificationModal(action, message) {
      const actionText = action === 'disconnect' ? 'Disconnect Google Account' : 'Delete Account';
      const actionColor = action === 'disconnect' ? 'warning' : 'danger';

      Swal.fire({
        title: 'Email Verification Required',
        html: `
          <p class="mb-3">${message}</p>
          <div class="mb-3 text-start fw-bold">
            <label for="otpCode" class="form-label">Enter Verification Code</label>
            <input type="text" class="form-control" id="otpCode" placeholder="Enter 6-digit code" maxlength="6">
            <div id="otpError" class="invalid-feedback d-none"></div>
          </div>
          <div class="mb-3 text-center">
            <button type="button" class="btn btn-outline-secondary btn-sm" id="resendOtpBtn">
              <i data-lucide="refresh-cw" class="icon-sm me-1"></i>Resend Code
            </button>
            <div id="resendTimer" class="text-muted small mt-1 d-none">
              Resend available in <span id="countdown">60</span> seconds
            </div>
          </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: action === 'disconnect' ? '#f39c12' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `<i data-lucide="check" class="icon-sm me-1"></i>${actionText}`,
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: `btn btn-${actionColor} btn-sm me-2`,
          cancelButton: 'btn btn-secondary btn-sm',
          actions: 'mt-1'
        },
        buttonsStyling: false,
        allowOutsideClick: false,
        didOpen: () => {
          if (typeof lucide !== 'undefined') lucide.createIcons();
          const otpInput = document.getElementById('otpCode');
          const otpError = document.getElementById('otpError');

          // Ensure cancel button is always enabled
          const cancelBtn = Swal.getCancelButton();
          if (cancelBtn) cancelBtn.disabled = false;

          otpInput.focus();

          // Real-time validation
          otpInput.addEventListener('input', function() {
            const value = this.value;

            // Clear any previous server-side error when user starts typing
            if (otpError.textContent.includes('Invalid') || otpError.textContent.includes('expired') || otpError.textContent.includes('failed')) {
              otpError.classList.add('d-none');
              otpError.style.display = 'none';
              this.classList.remove('is-invalid');
            }

            if (value.length > 0 && (!/^\d+$/.test(value) || value.length > 6)) {
              this.classList.add('is-invalid');
              otpError.textContent = 'Please enter only numbers (max 6 digits)';
              otpError.classList.remove('d-none');
              otpError.style.display = 'block';
            } else if (value.length > 0 && value.length < 6) {
              this.classList.add('is-invalid');
              otpError.textContent = 'Please enter a valid 6-digit verification code';
              otpError.classList.remove('d-none');
              otpError.style.display = 'block';
            } else if (value.length === 6) {
              this.classList.remove('is-invalid');
              this.classList.add('is-valid');
              otpError.classList.add('d-none');
              otpError.style.display = 'none';
            } else {
              this.classList.remove('is-invalid', 'is-valid');
              otpError.classList.add('d-none');
              otpError.style.display = 'none';
            }
          });

          // Only allow numbers
          otpInput.addEventListener('keypress', function(e) {
            if (!/\d/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'Enter'].includes(e.key)) {
              e.preventDefault();
            }
          });

          // Resend OTP functionality
          const resendBtn = document.getElementById('resendOtpBtn');
          const resendTimer = document.getElementById('resendTimer');
          const countdown = document.getElementById('countdown');
          let resendCountdown = 60;
          let countdownInterval;

          // Start countdown timer
          function startResendTimer() {
            resendBtn.disabled = true;
            resendBtn.innerHTML = '<i data-lucide="clock" class="icon-sm me-1"></i>Wait...';
            resendTimer.classList.remove('d-none');

            countdownInterval = setInterval(() => {
              resendCountdown--;
              countdown.textContent = resendCountdown;

              if (resendCountdown <= 0) {
                clearInterval(countdownInterval);
                resendBtn.disabled = false;
                resendBtn.innerHTML = '<i data-lucide="refresh-cw" class="icon-sm me-1"></i>Resend Code';
                resendTimer.classList.add('d-none');
                resendCountdown = 60;
                if (typeof lucide !== 'undefined') lucide.createIcons();
              }
            }, 1000);
          }

          // Start initial timer
          startResendTimer();

          // Resend button click handler
          resendBtn.addEventListener('click', function() {
            if (this.disabled) return;

            const originalText = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Sending...';
            this.disabled = true;

            const resendUrl = action === 'disconnect'
              ? '{{ route("admin.profile.google.disconnect.request") }}'
              : '{{ route("admin.profile.delete.request") }}';

            $.ajax({
              url: resendUrl,
              method: 'POST',
              data: {
                _token: '{{ csrf_token() }}'
              },
              success: function(response) {
                if (response.success) {
                  // Clear any existing errors
                  otpInput.classList.remove('is-invalid');
                  otpError.classList.add('d-none');
                  otpError.style.display = 'none';

                  // Clear OTP input
                  otpInput.value = '';
                  otpInput.focus();

                  // Show success message briefly
                  const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                  });
                  Toast.fire({
                    icon: 'success',
                    title: 'New verification code sent!'
                  });

                  // Restart countdown
                  startResendTimer();
                } else {
                  resendBtn.innerHTML = originalText;
                  resendBtn.disabled = false;
                  if (typeof lucide !== 'undefined') lucide.createIcons();

                  // Ensure cancel button stays enabled
                  const cancelBtn = Swal.getCancelButton();
                  if (cancelBtn) cancelBtn.disabled = false;

                  // Show error
                  otpError.textContent = response.message || 'Failed to resend code. Please try again.';
                  otpError.classList.remove('d-none');
                  otpError.style.display = 'block';
                }
              },
              error: function(xhr) {
                resendBtn.innerHTML = originalText;
                resendBtn.disabled = false;
                if (typeof lucide !== 'undefined') lucide.createIcons();

                // Ensure cancel button stays enabled
                const cancelBtn = Swal.getCancelButton();
                if (cancelBtn) cancelBtn.disabled = false;

                const response = xhr.responseJSON;
                otpError.textContent = response?.message || 'Failed to resend code. Please try again.';
                otpError.classList.remove('d-none');
                otpError.style.display = 'block';
              }
            });
          });
        },
        preConfirm: () => {
          const otpCode = document.getElementById('otpCode').value;
          const otpError = document.getElementById('otpError');
          const confirmBtn = Swal.getConfirmButton();
          const cancelBtn = Swal.getCancelButton();

          if (!otpCode || otpCode.length !== 6 || !/^\d{6}$/.test(otpCode)) {
            document.getElementById('otpCode').classList.add('is-invalid');
            otpError.textContent = 'Please enter a valid 6-digit verification code';
            otpError.classList.remove('d-none');
            otpError.style.display = 'block';
            return false;
          }

          // Show spinner in button and disable only confirm button
          const originalText = confirmBtn.innerHTML;
          confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verifying...';
          confirmBtn.disabled = true;
          // Ensure cancel button stays enabled
          if (cancelBtn) cancelBtn.disabled = false;

          const url = action === 'disconnect' ?
            '{{ route("admin.profile.google.disconnect.verify") }}' :
            '{{ route("admin.profile.delete.verify") }}';

          // Verify OTP and perform action
          return $.ajax({
            url: url,
            method: 'POST',
            data: {
              _token: '{{ csrf_token() }}',
              otp_code: otpCode
            }
          }).then(function(response) {
            if (response.success) {
              return response;
            } else {
              throw new Error(response.message || 'Verification failed.');
            }
          }).catch(function(xhr) {
            // Reset confirm button and ensure cancel button is enabled
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = false;
            if (cancelBtn) cancelBtn.disabled = false;
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const response = xhr.responseJSON;
            const errorMessage = response?.message || 'Verification failed. Please try again.';

            // Show error inline
            document.getElementById('otpCode').classList.add('is-invalid');
            otpError.textContent = errorMessage;
            otpError.classList.remove('d-none');
            otpError.style.display = 'block';

            throw new Error('validation_error'); // Prevent modal from closing
          });
        }
      }).then((result) => {
        if (result.isConfirmed && result.value && result.value.success) {
          // Verification successful, show success message and redirect
          Swal.fire({
            title: 'Success!',
            text: result.value.message,
            icon: 'success',
            confirmButtonText: 'OK',
            customClass: {
              confirmButton: 'btn btn-success btn-sm'
            },
            buttonsStyling: false
          }).then(() => {
            if (action === 'delete') {
              // Redirect to login page after account deletion
              window.location.href = '{{ route("auth.login") }}';
            } else {
              // Reload page after Google disconnect
              window.location.reload();
            }
          });
        }
      }).catch((error) => {
        // Handle any other errors that might occur
        if (error && error.message && error.message !== 'validation_error') {
          console.error('OTP verification error:', error);
        }
        // For validation_error, we don't show anything as the error is already shown inline
      }).finally(() => {
        // Cleanup countdown interval when modal is closed
        if (countdownInterval) {
          clearInterval(countdownInterval);
        }
      });
    }
  });
</script>
@endpush