@extends('auth.layouts.master', [
'page_title' => 'Recovery Code Verification',
'page_description' => 'Use your recovery code to access your account when 2FA is unavailable.',
'page_keywords' => 'recovery code, backup code, account recovery, 2FA recovery'
])

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
            <h5 class="text-muted mb-3 fw-normal">Recovery Code Verification</h5>

            <div class="alert alert-info">
              <i data-lucide="key" class="icon-sm me-2"></i>
              Please enter one of your recovery codes to access your account. Each recovery code can only be used once.
            </div>

            @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            @endif

            <form class="forms-sample" method="POST" action="{{ route('auth.two-factor.recovery.post') }}" id="recoveryForm">
              @csrf

              <div class="mb-3">
                <label for="recovery_code" class="form-label">
                  Recovery Code<span class="text-danger">*</span>
                  <i data-lucide="help-circle" class="icon-xs ms-1 text-muted"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-title="Enter one of your 10-character recovery codes. Each code can only be used once"
                    style="cursor: help;"></i>
                </label>
                <input type="text" class="form-control @error('recovery_code') is-invalid @enderror"
                  id="recovery_code" name="recovery_code" placeholder="Enter recovery code (e.g., A1B2C3D4E5)"
                  maxlength="10" autocomplete="one-time-code" autofocus required>
                @error('recovery_code')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                  Recovery codes are 10-character codes (letters and numbers)
                </small>
              </div>

              <div class="text-center mb-3">
                <button type="submit" class="btn btn-sm btn-primary" id="verifyRecoveryBtn"
                  data-bs-toggle="tooltip"
                  data-bs-placement="top"
                  data-bs-title="Verify your recovery code and access your account">
                  <i data-lucide="unlock" class="icon-sm me-1"></i>
                  <span class="btn-text">Verify Recovery Code</span>
                </button>
                <small class="form-text text-muted d-block mt-2">
                  Remember your verification code?
                  <a href="{{ route('auth.two-factor.verify') }}" class="text-decoration-none">Back to Two-Factor Authentication</a>.
                </small>
              </div>

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
@endpush

@push('custom-scripts')
@vite(['resources/js/auth/validation.js'])
<script>
  $(document).ready(function() {
    // Initialize form validation with custom rules for recovery code
    AuthValidation.init('#recoveryForm', {
      recovery_code: {
        required: true,
        minlength: 10,
        maxlength: 10
      }
    }, {
      recovery_code: {
        required: "Please enter a recovery code",
        minlength: "Recovery code must be exactly 10 characters",
        maxlength: "Recovery code must be exactly 10 characters"
      }
    }, 'Verifying...');

    // Auto-format recovery codes
    $('#recovery_code').on('input', function() {
      let value = $(this).val().replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

      // Limit to 10 characters (no formatting, just uppercase)
      if (value.length > 10) {
        value = value.substring(0, 10);
      }

      $(this).val(value);
    });

    // Focus on input
    $('#recovery_code').focus();

    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }

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