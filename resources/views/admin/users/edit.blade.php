@extends('admin.layout.master')

@section('title', $title ?? 'Edit User')
@section('description', $description ?? 'Edit user account details, roles and permissions')
@section('keywords', $keywords ?? 'edit user, modify account, update profile, user settings')

@push('plugin-styles')
<link href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit User</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 col-xl-12 middle-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title">Edit User: {{ $user->name }}</h6>

            @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="forms-sample" id="userEditForm">
              @csrf
              @method('PUT')

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                      id="name" name="name" value="{{ old('name', $user->name) }}" placeholder="Enter full name"
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
                      id="email" name="email" value="{{ old('email', $user->email) }}" placeholder="Enter email address"
                      maxlength="255" data-maxlength="true" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                      id="password" name="password" placeholder="Leave blank to keep current password"
                      minlength="8">
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Leave blank to keep current password (min 8 characters if changing)</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control"
                      id="password_confirmation" name="password_confirmation" placeholder="Confirm new password"
                      minlength="8">
                    <small class="form-text text-muted">Required only if changing password</small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                      id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Enter phone number"
                      maxlength="20" data-maxlength="true">
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
                      id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
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
                  maxlength="500" data-maxlength="true">{{ old('address', $user->address) }}</textarea>
                @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="role_id" class="form-label">User Role *</label>
                    <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                      <option value="">Select Role</option>
                      @foreach($roles as $role)
                      <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                        {{ $role->display_name }}
                        @if($role->description)
                        - {{ Str::limit($role->description, 50) }}
                        @endif
                      </option>
                      @endforeach
                    </select>
                    @error('role_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Assign user role and permissions</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="is_active" class="form-label">Account Status *</label>
                    <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
                      <option value="1" {{ old('is_active', $user->is_active) == '1' ? 'selected' : '' }}>Active</option>
                      <option value="0" {{ old('is_active', $user->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Active users can login to the system</small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="timezone" class="form-label">Timezone</label>
                    <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                      <option value="">Use System Default ({{ app(\App\Services\SettingsService::class)->get('timezone', 'UTC') }})</option>
                      @foreach(getTimezoneOptions() as $value => $label)
                      <option value="{{ $value }}" {{ old('timezone', $user->timezone) == $value ? 'selected' : '' }}>
                        {{ $label }}
                      </option>
                      @endforeach
                    </select>
                    @error('timezone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">User's preferred timezone for date/time display</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="language" class="form-label">Language</label>
                    <select class="form-select @error('language') is-invalid @enderror" id="language" name="language">
                      @foreach(getLanguageOptions() as $value => $label)
                      <option value="{{ $value }}" {{ old('language', $user->language ?? 'en') == $value ? 'selected' : '' }}>
                        {{ $label }}
                      </option>
                      @endforeach
                    </select>
                    @error('language')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">User's preferred language</small>
                  </div>
                </div>
              </div>

              {{-- Avatar Upload with Cropper --}}
              @include('admin.partials.image-cropper', [
              'inputId' => 'avatar',
              'label' => 'Profile Picture',
              'currentImage' => $user->avatar ? $user->avatar_url : null
              ])
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
                      {{ $user->two_factor_enabled ? 'checked' : '' }}>
                    <label class="form-check-label" for="two_factor_enabled">
                      Enable Two-Factor Authentication
                    </label>
                  </div>
                  <small class="form-text text-muted">
                    Add an extra layer of security to this user's account with verification codes.
                  </small>
                </div>

                {{-- Step 2: Method Selection (hidden by default) --}}
                <div id="method-selection" class="mt-3" style="display: none;">
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
                            Receive verification codes via email ({{ $user->email }})
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
                            Use Google Authenticator, Authy, or similar apps
                          </small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                {{-- Step 3: Email Setup --}}
                <div id="email-setup" class="mt-3" style="display: none;">
                  <div class="alert alert-info">
                    <i data-lucide="mail" class="icon-sm me-2"></i>
                    <strong>Email Verification Setup</strong>
                    <p class="mb-0 mt-2">
                      We'll send a 6-digit verification code to the user's email address ({{ $user->email }})
                      to verify and enable two-factor authentication.
                    </p>
                  </div>
                  <button type="button" class="btn btn-primary btn-sm" id="send-email-code">
                    <i data-lucide="mail" class="icon-sm me-1"></i>
                    Send Verification Code
                  </button>
                </div>

                {{-- Step 4: Email Verification --}}
                <div id="email-verify" class="mt-3" style="display: none;">
                  <div class="alert alert-success">
                    <i data-lucide="check-circle" class="icon-sm me-2"></i>
                    Verification code sent to {{ $user->email }}. Please ask the user to check their email.
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="email_verification_code" class="form-label">Enter Verification Code</label>
                        <input type="text" class="form-control" id="email_verification_code"
                          placeholder="Enter 6-digit code" maxlength="6">
                        <small class="form-text text-muted">Ask the user to provide the code from their email</small>
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

                {{-- Step 5: QR Code Setup --}}
                <div id="qr-setup" class="mt-3" style="display: none;">
                  <div class="alert alert-info">
                    <i data-lucide="qr-code" class="icon-sm me-2"></i>
                    <strong>Authenticator App Setup</strong>
                    <p class="mb-0 mt-2">
                      Ask the user to scan the QR code with their authenticator app and enter the verification code.
                    </p>
                  </div>
                  <button type="button" class="btn btn-primary btn-sm" id="generate-qr">
                    <i data-lucide="qr-code" class="icon-sm me-1"></i>
                    Generate QR Code
                  </button>
                  <div id="qr-code-container" class="mt-3"></div>
                </div>

                {{-- Step 6: QR Code Verification --}}
                <div id="qr-verify" class="mt-3" style="display: none;">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="qr_verification_code" class="form-label">Enter Verification Code</label>
                        <input type="text" class="form-control" id="qr_verification_code"
                          placeholder="Enter 6-digit code" maxlength="6">
                        <small class="form-text text-muted">Ask the user to provide the code from their authenticator app</small>
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

                {{-- Step 7: Two-Factor Enabled Section --}}
                <div id="two-factor-enabled" class="mt-3" style="display: <?= $user->two_factor_enabled ? 'block' : 'none' ?>;">
                  <div class="alert alert-success">
                    <i data-lucide="shield-check" class="icon-sm me-2"></i>
                    <strong>Two-Factor Authentication is enabled</strong>
                    @if($user->two_factor_enabled)
                    using <strong>{{ $user->two_factor_method === 'email' ? 'Email Verification' : 'Authenticator App' }}</strong>.
                    @endif
                    This user's account is protected with an additional security layer.
                  </div>

                  <div class="row">
                    <div class="col-md-3">
                      <button type="button" class="btn btn-outline-primary btn-sm" id="change-method">
                        <i data-lucide="refresh-ccw" class="icon-sm me-1"></i>
                        Change Method
                      </button>
                    </div>
                    <div class="col-md-3">
                      <button type="button" class="btn btn-outline-secondary btn-sm" id="view-recovery-codes">
                        <i data-lucide="eye" class="icon-sm me-1"></i>
                        View Recovery Codes
                      </button>
                    </div>
                    <div class="col-md-3">
                      <button type="button" class="btn btn-outline-warning btn-sm" id="regenerate-recovery-codes">
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
              </div>

              <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                  <i data-lucide="x" class="icon-sm me-1"></i>
                  <span class="d-none d-sm-inline">Cancel</span>
                  <span class="d-sm-none">Cancel</span>
                </a>
                <button type="button" class="btn btn-primary btn-sm" id="submitBtn">
                  <i data-lucide="save" class="icon-sm me-1"></i>
                  <span class="d-none d-sm-inline">Update User</span>
                  <span class="d-sm-none">Update</span>
                </button>
              </div>
            </form>
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
@endpush

@push('custom-scripts')
@vite(['resources/js/admin/validation/users.js'])
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
    <?php if ($user->two_factor_enabled): ?>
      enabledSection.show();
      methodSelection.hide();
      $('#email-setup, #qr-setup, #email-verify, #qr-verify').hide();
    <?php else: ?>
      enabledSection.hide();
      methodSelection.hide();
      $('#email-setup, #qr-setup, #email-verify, #qr-verify').hide();
    <?php endif; ?>

    // Toggle change - show method selection
    twoFactorToggle.on('change', function() {
      if ($(this).is(':checked')) {
        // Show method selection only
        methodSelection.show();
        // Hide other sections
        $('#email-setup, #qr-setup').hide();
        enabledSection.hide();
      } else {
        // Ask for confirmation if 2FA is currently enabled
        if (<?= $user->two_factor_enabled ? 'true' : 'false' ?>) {
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

    // Handle method selection
    $('input[name="two_factor_method"]').on('change', function() {
      const selectedMethod = $(this).val();

      // Hide all method content
      $('#email-setup, #qr-setup, #email-verify, #qr-verify').hide();

      if (selectedMethod === 'email') {
        emailSetup.show();
      } else if (selectedMethod === 'qr_code') {
        qrSetup.show();
      }
    });

    // Send email verification code
    $('#send-email-code').on('click', function() {
      const btn = $(this);
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Sending...');

      $.post('{{ route("admin.users.two-factor.send-email-code", $user) }}', {
          _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
          if (response.success) {
            // Hide send section, show verify section
            emailSetup.hide();
            $('#email-verify').show();

            Swal.fire({
              title: 'Code Sent!',
              text: 'Verification code has been sent to the user\'s email address.',
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
        .fail(function() {
          Swal.fire({
            title: 'Error!',
            text: 'Failed to send verification code. Please try again.',
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

    // Verify email and enable 2FA
    $('#verify-email-2fa').on('click', function() {
      const btn = $(this);
      const code = $('#email_verification_code').val();

      if (!code || code.length !== 6) {
        Swal.fire({
          title: 'Invalid Code!',
          text: 'Please enter a valid 6-digit verification code.',
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
        return;
      }

      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Verifying...');

      $.post('{{ route("admin.users.two-factor.enable", $user) }}', {
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

    // Generate QR Code
    $('#generate-qr').on('click', function() {
      const btn = $(this);
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Generating...');

      $.post('{{ route("admin.users.two-factor.generate-qr", $user) }}', {
          _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
          if (response.success) {
            $('#qr-code-container').html(response.qr_code);
            $('#qr-verify').show();

            Swal.fire({
              title: 'QR Code Generated!',
              text: 'Ask the user to scan the QR code with their authenticator app.',
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
        .fail(function() {
          Swal.fire({
            title: 'Error!',
            text: 'Failed to generate QR code. Please try again.',
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
          btn.prop('disabled', false).html('<i data-lucide="qr-code" class="icon-sm me-1"></i>Generate QR Code');
          lucide.createIcons();
        });
    });

    // Verify QR and enable 2FA
    $('#verify-qr-2fa').on('click', function() {
      const btn = $(this);
      const code = $('#qr_verification_code').val();

      if (!code || code.length !== 6) {
        Swal.fire({
          title: 'Invalid Code!',
          text: 'Please enter a valid 6-digit verification code.',
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
        return;
      }

      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Verifying...');

      $.post('{{ route("admin.users.two-factor.enable", $user) }}', {
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
        .fail(function() {
          Swal.fire({
            title: 'Error!',
            text: 'Failed to enable two-factor authentication. Please try again.',
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

    // Confirmation before disabling 2FA
    function confirmDisable2FA() {
      Swal.fire({
        title: 'Disable Two-Factor Authentication?',
        text: 'Are you sure you want to disable two-factor authentication for this user? This will make their account less secure.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="shield-off" class="icon-sm me-1"></i>Yes, Disable',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-danger btn-sm',
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
      $.post('{{ route("admin.users.two-factor.disable", $user) }}', {
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
              text: 'Two-factor authentication has been disabled for this user.',
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

    // Disable 2FA button
    $('#disable-2fa').on('click', function() {
      confirmDisable2FA();
    });

    // Recovery codes function
    function showRecoveryCodes(recoveryCodes) {
      let recoveryCodesHtml = '<div class="alert alert-warning mt-3 mb-3">';
      recoveryCodesHtml += '<h6><i data-lucide="key" class="icon-sm me-2"></i>Recovery Codes</h6>';
      recoveryCodesHtml += '<p>Save these recovery codes in a safe place. The user can use them to access their account if they lose access to their two-factor authentication method:</p>';
      recoveryCodesHtml += '<div class="row">';

      recoveryCodes.forEach(function(code, index) {
        if (index % 2 === 0) recoveryCodesHtml += '<div class="col-md-6">';
        recoveryCodesHtml += '<div class="mb-2"><code class="bg-light p-2 d-block text-center">' + code + '</code></div>';
        if (index % 2 === 1 || index === recoveryCodes.length - 1) recoveryCodesHtml += '</div>';
      });

      recoveryCodesHtml += '</div>';
      recoveryCodesHtml += '<div class="text-center mt-3">';
      recoveryCodesHtml += '<button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="downloadRecoveryCodes()"><i data-lucide="download" class="icon-sm me-1"></i>Download</button>';
      recoveryCodesHtml += '<button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyRecoveryCodes()"><i data-lucide="copy" class="icon-sm me-1"></i>Copy</button>';
      recoveryCodesHtml += '</div>';
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

    // Format verification code inputs (numbers only)
    $('#email_verification_code, #qr_verification_code').on('input', function() {
      $(this).val($(this).val().replace(/[^0-9]/g, ''));
    });

    // Global functions for recovery codes
    window.downloadRecoveryCodes = function() {
      if (window.currentRecoveryCodes) {
        const content = 'Two-Factor Authentication Recovery Codes\n' +
          '========================================\n\n' +
          'Save these codes in a safe place. The user can use them to access their account\n' +
          'if they lose access to their two-factor authentication method.\n\n' +
          'Recovery Codes:\n' +
          window.currentRecoveryCodes.map((code, index) => `${index + 1}. ${code}`).join('\n') +
          '\n\nGenerated on: ' + new Date().toLocaleString();

        const blob = new Blob([content], {
          type: 'text/plain'
        });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'user-{{ $user->id }}-two-factor-recovery-codes.txt';
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

    // Change verification method
    $('#change-method').on('click', function() {
      Swal.fire({
        title: 'Change Verification Method?',
        text: 'This will disable the current 2FA and allow you to set up a new method for this user.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="refresh-ccw" class="icon-sm me-1"></i>Yes, Change',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-warning btn-sm',
          cancelButton: 'btn btn-secondary btn-sm'
        },
        buttonsStyling: false,
        didOpen: () => {
          lucide.createIcons();
        }
      }).then((result) => {
        if (result.isConfirmed) {
          // Disable current 2FA and show method selection
          $.post('{{ route("admin.users.two-factor.disable", $user) }}', {
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
                  title: 'Old Method Disabled!',
                  text: 'Please select a new verification method for this user.',
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

    // View recovery codes
    $('#view-recovery-codes').on('click', function() {
      const btn = $(this);
      btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Loading...');

      $.post('{{ route("admin.users.two-factor.regenerate-codes", $user) }}', {
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
            recoveryCodesHtml += '<div class="text-center mt-3">';
            recoveryCodesHtml += '<button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="downloadRecoveryCodes()"><i data-lucide="download" class="icon-sm me-1"></i>Download</button>';
            recoveryCodesHtml += '<button type="button" class="btn btn-outline-secondary btn-sm" onclick="copyRecoveryCodes()"><i data-lucide="copy" class="icon-sm me-1"></i>Copy</button>';
            recoveryCodesHtml += '</div>';

            // Store codes globally
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
        .always(function() {
          btn.prop('disabled', false).html('<i data-lucide="eye" class="icon-sm me-1"></i>View Recovery Codes');
          lucide.createIcons();
        });
    });

    // Regenerate recovery codes
    $('#regenerate-recovery-codes').on('click', function() {
      const btn = $(this);

      Swal.fire({
        title: 'Regenerate Recovery Codes?',
        text: 'This will invalidate all existing recovery codes for this user. Make sure they have saved the new codes.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="refresh-cw" class="icon-sm me-1"></i>Yes, regenerate',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        customClass: {
          confirmButton: 'btn btn-warning btn-sm',
          cancelButton: 'btn btn-secondary btn-sm'
        },
        buttonsStyling: false,
        didOpen: () => {
          lucide.createIcons();
        }
      }).then((result) => {
        if (result.isConfirmed) {
          btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Regenerating...');

          $.post('{{ route("admin.users.two-factor.regenerate-codes", $user) }}', {
              _token: '{{ csrf_token() }}'
            })
            .done(function(response) {
              if (response.success) {
                // Show new recovery codes
                showRecoveryCodes(response.recovery_codes);

                Swal.fire({
                  title: 'Regenerated!',
                  text: 'New recovery codes have been generated for this user.',
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
            .always(function() {
              btn.prop('disabled', false).html('<i data-lucide="refresh-cw" class="icon-sm me-1"></i>Regenerate Recovery Codes');
              lucide.createIcons();
            });
        }
      });
    });
  });
</script>
@endpush