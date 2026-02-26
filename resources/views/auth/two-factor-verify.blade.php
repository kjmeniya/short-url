@extends('auth.layouts.master', [
'page_title' => 'Two-Factor Authentication',
'page_description' => 'Enter your two-factor authentication code to complete login.',
'page_keywords' => 'two factor authentication, 2FA, security code, verification code'
])

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="row w-100 mx-0 auth-page">
  <div class="col-md-8 col-xl-6 mx-auto">
    <div class="card">
      <div class="row">
        <div class="col-md-4 pe-md-0">
          <div class="auth-side-wrapper">
            <!-- Auth side content -->
          </div>
        </div>
        <div class="col-md-8 ps-md-0">
          @include('auth.layouts.partials.theme-switcher')
          <div class="auth-form-wrapper px-4 py-5">
            <a href="{{url('/')}}" class="nobleui-logo d-block mb-2">
              <!-- Soft<span>Dev</span> -->
              <img src="{{ logo_url('frontend', 'large', 'light') }}" class="logo logo-light w-100px h-auto" alt="logo">
              <img src="{{ logo_url('frontend', 'large', 'dark') }}" class="logo logo-dark w-100px h-auto" alt="logo">
            </a>
            <h5 class="text-muted mb-3 fw-normal">Two-Factor Authentication</h5>

            @if(isset($user) && $user->two_factor_method === 'email')
            <div class="alert alert-info">
              <i data-lucide="mail" class="icon-sm me-2"></i>
              A 6-digit verification code has been sent to your email address <strong>{{ $user->email }}</strong>.
              Please check your email and enter the code below.
            </div>
            @else
            <div class="alert alert-info">
              <i data-lucide="smartphone" class="icon-sm me-2"></i>
              Please enter the 6-digit verification code from your authenticator app.
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            @endif

            <form class="forms-sample" method="POST" action="{{ route('auth.two-factor.verify.post') }}" id="twoFactorForm">
              @csrf

              <div class="mb-3">
                <label for="code" class="form-label">
                  Verification Code<span class="text-danger">*</span>
                  <i data-lucide="help-circle" class="icon-xs ms-1 text-muted"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-title="{{ (isset($user) && $user->two_factor_method === 'email') ? 'Enter the 6-digit code sent to your email address' : 'Enter the 6-digit code from your authenticator app' }}"
                    style="cursor: help;"></i>
                </label>
                <input type="text" class="form-control @error('code') is-invalid @enderror"
                  id="code" name="code" placeholder="Enter 6-digit code"
                  maxlength="6" autocomplete="one-time-code" autofocus required>
                @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                  @if(isset($user) && $user->two_factor_method === 'email')
                  Enter the code from your email
                  @else
                  Enter the code from your authenticator app
                  @endif
                </small>
              </div>

              @if(isset($user) && $user->two_factor_method === 'email')
              <div class="text-center mb-3">
                <div class="d-flex gap-2 justify-content-center">
                  <button type="submit" class="btn btn-sm btn-primary" id="verifyBtn">
                    <i data-lucide="shield-check" class="icon-sm me-1"></i>
                    <span class="btn-text">Verify</span>
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="resendBtn">
                    <i data-lucide="mail" class="icon-sm me-1"></i>
                    <span class="btn-text">Resend Code</span>
                  </button>
                </div>
                <small class="form-text text-muted d-block mt-2">
                  Didn't receive the email? Check your spam folder or click to resend.
                  <a href="{{ route('auth.two-factor.recovery') }}" class="text-decoration-none">Use recovery code instead</a>.
                </small>
              </div>
              @else
              <div class="text-center mb-3">
                <button type="submit" class="btn btn-sm btn-primary" id="verifyBtn2">
                  <i data-lucide="shield-check" class="icon-sm me-1"></i>
                  <span class="btn-text">Verify</span>
                </button>
                <small class="form-text text-muted d-block mt-2">
                  <a href="{{ route('auth.two-factor.recovery') }}" class="text-decoration-none">Use recovery code instead</a>.
                </small>
              </div>
              @endif

              <div class="text-center">
                <p class="text-muted">
                  Having trouble?
                  <a href="{{ route('auth.logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Sign out
                  </a>
                </p>
              </div>
            </form>

            <form id="logout-form" action="{{ route('auth.logout') }}" method="POST" class="d-none">
              @csrf
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
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
@vite(['resources/js/auth/validation.js'])
<script>
  $(document).ready(function() {
    // Initialize form validation with custom rules for verification code
    AuthValidation.init('#twoFactorForm', {
      code: {
        required: true,
        minlength: 4,
        maxlength: 6
      }
    }, {
      code: {
        required: "Please enter a verification code",
        minlength: "Code must be at least 4 characters",
        maxlength: "Code cannot exceed 6 characters"
      }
    }, 'Verifying...');

    // Resend code functionality
    $('#resendBtn').on('click', function() {
      const btn = $(this);
      const originalText = btn.find('.btn-text').text();
      btn.find('.btn-text').text('Sending...');
      btn.find('i').removeClass().addClass('spinner-border spinner-border-sm icon-sm me-1');
      btn.prop('disabled', true);

      $.post('{{ route("admin.profile.two-factor.send-email-code") }}', {
          _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
          if (response.success) {
            Swal.fire({
              title: 'Code Sent!',
              text: 'A new verification code has been sent to your email.',
              icon: 'success',
              confirmButtonText: 'OK',
              customClass: {
                confirmButton: 'btn btn-primary'
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
              confirmButtonText: 'OK',
              customClass: {
                confirmButton: 'btn btn-primary'
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
            confirmButtonText: 'OK',
            customClass: {
              confirmButton: 'btn btn-primary'
            },
            buttonsStyling: false,
            didOpen: () => {
              lucide.createIcons();
            }
          });
        })
        .always(function() {
          btn.find('.btn-text').text('Resend Code');
          btn.find('.spinner-border').removeClass('spinner-border spinner-border-sm').addClass('icon-sm').attr('data-lucide', 'mail');
          btn.prop('disabled', false);
          lucide.createIcons();
        });
    });

    // Initialize Bootstrap tooltips after Lucide icons are loaded
    setTimeout(function() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }, 100);
  });
</script>
@endpush