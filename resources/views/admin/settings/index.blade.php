@extends('admin.layout.master')

@section('title', 'System Settings')
@section('description', 'Manage system settings and configuration options')
@section('keywords', 'system settings, configuration, admin settings')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('build/plugins/cropperjs/cropper.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center">
  <div>
    <nav class="page-breadcrumb mb-0">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item active" aria-current="page">Settings</li>
      </ol>
    </nav>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
          <h6 class="card-title mb-0">System Settings</h6>
          <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-warning btn-sm" id="resetAllBtn">
              <i data-lucide="rotate-ccw" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Reset All Settings</span>
              <span class="d-sm-none">Reset</span>
            </button>
          </div>
        </div>

        <!-- Settings Overview -->
        <div class="row">
          @foreach($settingGroups as $groupKey => $groupData)
          <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                  <i data-lucide="{{ $groupData['icon'] }}" class="icon-md text-primary me-3"></i>
                  <h6 class="card-title mb-0">{{ $groupData['name'] }}</h6>
                </div>
                <p class="text-muted small mb-3">
                  {{ $groupData['description'] }}
                  @if(isset($groupedSettings[$groupKey]))
                    <br><small class="text-success">{{ $groupedSettings[$groupKey]->count() }} settings configured</small>
                  @else
                    <br><small class="text-warning">No settings configured</small>
                  @endif
                </p>
                <a href="{{ route('admin.settings.group', $groupKey) }}" class="btn btn-primary btn-sm">
                  <i data-lucide="arrow-right" class="icon-sm me-1"></i>Manage Settings
                </a>
              </div>
            </div>
          </div>
          @endforeach
        </div>


      </div>
    </div>
  </div>
</div>

<!-- Device Logout Section -->
@if(auth()->user()->role_id === 1)
<div class="row mt-4">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h6 class="card-title">Security Actions</h6>
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="card border border-warning">
              <div class="card-body">
                <h6 class="card-title text-warning">Logout Other Devices</h6>
                <p class="text-muted small mb-2">Logout from all other devices while keeping current session active.</p>
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#logoutOtherDevicesModal">
                  <i data-lucide="log-out" class="icon-sm me-1"></i>Logout Other Devices
                </button>
              </div>
            </div>
          </div>
          <div class="col-md-6 mb-3">
            <div class="card border border-danger">
              <div class="card-body">
                <h6 class="card-title text-danger">Logout All Users</h6>
                <p class="text-muted small mb-2">Force logout all users from the system. Use with caution!</p>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#logoutAllUsersModal">
                  <i data-lucide="user-x" class="icon-sm me-1"></i>Logout All Users
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif



<!-- Logout Other Devices Modal -->
<div class="modal fade" id="logoutOtherDevicesModal" tabindex="-1" aria-labelledby="logoutOtherDevicesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutOtherDevicesModalLabel">
          <i data-lucide="log-out" class="icon-sm me-2"></i>Logout Other Devices
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.settings.logout-other-devices') }}" method="POST" id="logoutOtherDevicesForm">
        @csrf
        <div class="modal-body">
          <div class="text-center mb-3">
            <i data-lucide="shield-alert" class="icon-lg text-warning mb-3"></i>
            <h6>Confirm Password to Logout Other Devices</h6>
            <p class="text-muted">This will logout all your other active sessions while keeping your current session active.</p>
          </div>

          <!-- Error Alert (hidden by default) -->
          <div class="alert alert-danger d-none" id="logoutOtherDevicesError">
            <i data-lucide="alert-circle" class="icon-sm me-2"></i>
            <span id="logoutOtherDevicesErrorText"></span>
          </div>

          <div class="mb-3">
            <label for="logout_password" class="form-label">Your Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control"
              id="logout_password" name="password" required
              placeholder="Enter your current password">
            <div class="invalid-feedback"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <i data-lucide="x" class="icon-sm me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-sm btn-warning" id="logoutOtherDevicesBtn">
            <i data-lucide="log-out" class="icon-sm me-1"></i>Logout Other Devices
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Logout All Users Modal -->
<div class="modal fade" id="logoutAllUsersModal" tabindex="-1" aria-labelledby="logoutAllUsersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title text-danger" id="logoutAllUsersModalLabel">
          <i data-lucide="user-x" class="icon-sm me-2"></i>Logout All Users
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.settings.logout-all-users') }}" method="POST" id="logoutAllUsersForm">
        @csrf
        <div class="modal-body">
          <div class="text-center mb-3">
            <i data-lucide="alert-triangle" class="icon-lg text-danger mb-3"></i>
            <h6 class="text-danger">⚠️ DANGER ZONE ⚠️</h6>
            <p class="text-muted">This will forcefully logout <strong>ALL USERS</strong> from the system, including yourself!</p>
            <div class="alert alert-danger">
              <strong>Warning:</strong> This action will:
              <ul class="mb-0 mt-2 text-start">
                <li>Clear all active user sessions</li>
                <li>Force all users to login again</li>
                <li>Log you out as well</li>
                <li>Cannot be undone</li>
              </ul>
            </div>
          </div>

          <!-- Error Alert (hidden by default) -->
          <div class="alert alert-danger d-none" id="logoutAllUsersError">
            <i data-lucide="alert-circle" class="icon-sm me-2"></i>
            <span id="logoutAllUsersErrorText"></span>
          </div>

          <div class="mb-3">
            <label for="admin_password" class="form-label">Your Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control"
              id="admin_password" name="password" required
              placeholder="Enter your password to confirm">
            <div class="invalid-feedback"></div>
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="confirmLogoutAll" required>
              <label class="form-check-label text-danger" for="confirmLogoutAll">
                I understand this will logout ALL users including myself
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            <i data-lucide="x" class="icon-sm me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-sm btn-danger" id="logoutAllUsersBtn">
            <i data-lucide="user-x" class="icon-sm me-1"></i>Logout All Users
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('build/plugins/cropperjs/cropper.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
  $(document).ready(function() {
    // Reset all settings with password confirmation
    $('#resetAllBtn').on('click', function(e) {
      e.preventDefault();
      let isProcessing = false;

      const showResetAllPasswordModal = () => {
        Swal.fire({
          title: 'Reset All Settings?',
          html: `
            <div class="text-start">
              <p class="mb-3">This will reset ALL settings to their default values. This action cannot be undone.</p>
              <p class="mb-3">Please enter your password to confirm:</p>
              <div class="mb-3">
                <label for="swal-reset-all-password" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" id="swal-reset-all-password" class="form-control" placeholder="Enter your password">
                  <button class="btn btn-sm btn-outline-secondary" type="button" onclick="toggleSwalResetAllPassword()">
                    <i data-lucide="eye" class="icon-sm"></i>
                  </button>
                </div>
                <div id="reset-all-password-error" class="text-danger mt-2" style="display: none;"></div>
              </div>
            </div>
          `,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, reset all!',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showLoaderOnConfirm: true,
          preConfirm: () => {
            if (isProcessing) return false;

            const password = document.getElementById('swal-reset-all-password').value;
            const errorDiv = document.getElementById('reset-all-password-error');

            if (!password) {
              errorDiv.textContent = 'Password is required';
              errorDiv.style.display = 'block';
              return false;
            }

            errorDiv.style.display = 'none';
            isProcessing = true;

            // Verify password and reset all settings
            return $.ajax({
              url: '{{ route("admin.settings.verify-password") }}',
              method: 'POST',
              data: {
                password: password,
                _token: '{{ csrf_token() }}'
              }
            }).then(function(response) {
              if (response.success) {
                // Password verified, now reset all settings
                return $.ajax({
                  url: '{{ route("admin.settings.reset-defaults") }}',
                  type: 'POST',
                  headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  }
                }).then(function(data) {
                  isProcessing = false;
                  if (data.success) {
                    return { success: true, message: 'All settings have been reset to their default values.' };
                  } else {
                    throw new Error(data.message || 'Failed to reset settings');
                  }
                }).catch(function(xhr) {
                  isProcessing = false;
                  const message = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to reset settings';
                  throw new Error(message);
                });
              } else {
                isProcessing = false;
                const errorDiv = document.getElementById('reset-all-password-error');
                errorDiv.textContent = 'The password you entered is incorrect.';
                errorDiv.style.display = 'block';
                return false;
              }
            }).catch(function(xhr) {
              isProcessing = false;
              if (xhr.responseJSON && xhr.responseJSON.message && xhr.responseJSON.message.includes('password')) {
                const errorDiv = document.getElementById('reset-all-password-error');
                errorDiv.textContent = 'The password you entered is incorrect.';
                errorDiv.style.display = 'block';
                return false;
              } else {
                throw new Error('Failed to verify password.');
              }
            });
          },
          didOpen: () => {
            // Re-initialize lucide icons in the modal
            if (typeof lucide !== 'undefined') {
              lucide.createIcons();
            }
          }
        }).then((result) => {
          if (result.isConfirmed && result.value && result.value.success) {
            Swal.fire({
              icon: 'success',
              title: 'Reset Complete!',
              text: result.value.message,
              timer: 2000,
              showConfirmButton: false,
              confirmButtonColor: '#245dac'
            }).then(() => {
              location.reload();
            });
          }
        }).catch((error) => {
          if (error && error.message) {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: error.message,
              confirmButtonColor: '#245dac'
            }).then(() => {
              // Show password modal again on error
              setTimeout(() => showResetAllPasswordModal(), 100);
            });
          }
        });
      };

      showResetAllPasswordModal();
    });

    // Handle logout other devices form submission
    $('#logoutOtherDevicesForm').on('submit', function(e) {
      e.preventDefault();

      const $form = $(this);
      const $submitBtn = $('#logoutOtherDevicesBtn');
      const $errorAlert = $('#logoutOtherDevicesError');
      const $errorText = $('#logoutOtherDevicesErrorText');
      const $passwordInput = $('#logout_password');

      // Clear previous errors
      $errorAlert.addClass('d-none');
      $passwordInput.removeClass('is-invalid');
      $passwordInput.next('.invalid-feedback').text('');

      // Show loading state
      $submitBtn.prop('disabled', true);
      $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

      $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: $form.serialize(),
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
          if (data.success) {
            // Close modal and show success message
            $('#logoutOtherDevicesModal').modal('hide');

            // Show success alert on main page
            const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i data-lucide="check-circle" class="icon-sm me-2"></i>
                            ${data.message || 'You have been logged out from other devices successfully.'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
            $('.card-body').first().prepend(alertHtml);

            if (typeof lucide !== 'undefined') {
              lucide.createIcons();
            }
          }
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            const data = xhr.responseJSON;
            if (data.errors && data.errors.password) {
              $passwordInput.addClass('is-invalid');
              $passwordInput.next('.invalid-feedback').text(data.errors.password);
              $errorText.text(data.errors.password);
              $errorAlert.removeClass('d-none');
            }
          } else {
            $errorText.text('An error occurred while processing your request.');
            $errorAlert.removeClass('d-none');
          }
        },
        complete: function() {
          // Reset button state
          $submitBtn.prop('disabled', false);
          $submitBtn.html('<i data-lucide="log-out" class="icon-sm me-1"></i>Logout Other Devices');
          if (typeof lucide !== 'undefined') {
            lucide.createIcons();
          }
        }
      });
    });

    // Handle logout all users form submission
    $('#logoutAllUsersForm').on('submit', function(e) {
      e.preventDefault();

      const $form = $(this);
      const $submitBtn = $('#logoutAllUsersBtn');
      const $errorAlert = $('#logoutAllUsersError');
      const $errorText = $('#logoutAllUsersErrorText');
      const $passwordInput = $('#admin_password');

      // Clear previous errors
      $errorAlert.addClass('d-none');
      $passwordInput.removeClass('is-invalid');
      $passwordInput.next('.invalid-feedback').text('');

      // Show loading state
      $submitBtn.prop('disabled', true);
      $submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

      $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: $form.serialize(),
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
          if (data.success) {
            // Show success message in modal first
            const successHtml = `
                        <i data-lucide="check-circle" class="icon-sm me-2"></i>
                        ${data.message || 'All users have been logged out successfully.'}
                        <br><small class="text-muted">You will be redirected to login page in 3 seconds...</small>
                    `;
            $errorAlert.removeClass('alert-danger').addClass('alert-success');
            $errorAlert.html(successHtml);
            $errorAlert.removeClass('d-none');

            // Redirect to login after 3 seconds
            setTimeout(function() {
              window.location.href = '/login';
            }, 3000);
          }
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            const data = xhr.responseJSON;
            if (data.errors && data.errors.password) {
              $passwordInput.addClass('is-invalid');
              $passwordInput.next('.invalid-feedback').text(data.errors.password);
              $errorText.text(data.errors.password);
              $errorAlert.removeClass('d-none');
            }
          } else {
            $errorText.text('An error occurred while processing your request.');
            $errorAlert.removeClass('d-none');
          }
        },
        complete: function() {
          // Reset button state
          $submitBtn.prop('disabled', false);
          $submitBtn.html('<i data-lucide="user-x" class="icon-sm me-1"></i>Logout All Users');
          if (typeof lucide !== 'undefined') {
            lucide.createIcons();
          }
        }
      });
    });



    // Clear modal errors when modals are hidden
    $('.modal').on('hidden.bs.modal', function() {
      // Clear validation errors
      $(this).find('.is-invalid').removeClass('is-invalid');
      $(this).find('.invalid-feedback').text('');

      // Clear error alerts
      $(this).find('.alert.alert-danger').each(function() {
        if ($(this).attr('id') && $(this).attr('id').includes('Error')) {
          $(this).addClass('d-none');
        }
      });

      // Reset forms
      const form = $(this).find('form')[0];
      if (form) {
        form.reset();
      }
    });

    // Password toggle function for SweetAlert reset all modal
    window.toggleSwalResetAllPassword = function() {
      const input = document.getElementById('swal-reset-all-password');
      const icon = input.nextElementSibling.querySelector('i');

      if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
      } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
      }

      // Re-initialize lucide icons
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    };
  });
</script>
@endpush