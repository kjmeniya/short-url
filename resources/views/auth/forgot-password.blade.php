@extends('auth.layouts.master', [
'page_title' => 'Forgot Password',
'page_description' => 'Reset your password to regain access to your account.',
'page_keywords' => 'forgot password, password reset, account recovery, reset link'
])

@section('content')
<div class="page-wrapper full-page">
  <div class="page-content d-flex align-items-center justify-content-center">
    <div class="row w-100 mx-0 auth-page">
      <div class="col-md-12 col-lg-8 col-xl-6 mx-auto p-0">
        <div class="card">
          <div class="row">
            <div class="col-md-4 pe-md-0">
              <div class="auth-side-wrapper">
              </div>
            </div>
            <div class="col-md-8 ps-md-0">
              @include('auth.layouts.partials.theme-switcher')
              <div class="auth-form-wrapper px-4 py-5">
                <a href="{{url('/')}}" class="nobleui-logo d-block mb-2">
                  <img src="{{ logo_url('frontend', 'large', 'light') }}" class="logo logo-light w-100px h-auto" alt="logo">
                  <img src="{{ logo_url('frontend', 'large', 'dark') }}" class="logo logo-dark w-100px h-auto" alt="logo">
                </a>
                <h5 class="text-muted mb-3 fw-normal">Forgot your password? </h5>
                <div class="alert alert-secondary">
                  No problem. Just let us know your email address and we will email you a password reset link.
                </div>
                @if (session('status'))
                <div class="alert alert-success">
                  {{ session('status') }}
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

                <form class="forms-sample" method="POST" action="{{ route('auth.forgot-password.post') }}" id="forgotPasswordForm">
                  @csrf
                  <div class="mb-3">
                    <label for="userEmail" class="form-label">
                      Email address<span class="text-danger">*</span>
                      <i data-lucide="help-circle" class="icon-xs ms-1 text-muted"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        data-bs-title="Enter the email address associated with your account to receive a password reset link"
                        style="cursor: help;"></i>
                    </label>
                    <input name="email" type="email" class="form-control" id="userEmail"
                      placeholder="Enter your email address"
                      value="{{ old('email') }}"
                      required>
                  </div>

                  {{-- Google User Notice --}}
                  <div class="alert alert-info d-none" id="googleUserNotice">
                    <div class="d-flex align-items-center">
                      <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="-3 0 262 262" xmlns="http://www.w3.org/2000/svg">
                        <path d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027" fill="#4285F4"></path>
                        <path d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1" fill="#34A853"></path>
                        <path d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782" fill="#FBBC05"></path>
                        <path d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251" fill="#EB4335"></path>
                      </svg>
                      <div>
                        <strong>Google Account Detected</strong><br>
                        <small>This account is linked with Google. Please use "Continue with Google" to sign in, or contact support if you need assistance.</small>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <button type="submit" class="btn btn-sm btn-primary me-2 mb-2 mb-md-0 w-100" id="resetBtn">
                      <i data-lucide="mail" class="icon-sm me-1"></i>
                      <span class="btn-text">Send Password Reset Link</span>
                    </button>
                  </div>
                  <div class="text-center">
                    <a href="{{ route('auth.login') }}" class="d-block mt-3 text-secondary">Back to Login</a>
                  </div>
                </form>
              </div>
            </div>
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
    // Initialize form validation
    AuthValidation.init('#forgotPasswordForm', {}, {}, 'Sending reset link...');

    // Check for Google users when email is entered
    $('#userEmail').on('blur', function() {
      const email = $(this).val();
      if (email && email.includes('@')) {
        // Check if this email belongs to a Google user
        $.ajax({
          url: '{{ route("auth.check-google-user") }}',
          method: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            email: email
          },
          success: function(response) {
            if (response.is_google_user) {
              $('#googleUserNotice').removeClass('d-none');
              $('#resetBtn').prop('disabled', true).addClass('disabled');
            } else {
              $('#googleUserNotice').addClass('d-none');
              $('#resetBtn').prop('disabled', false).removeClass('disabled');
            }
          },
          error: function() {
            // On error, hide notice and enable button
            $('#googleUserNotice').addClass('d-none');
            $('#resetBtn').prop('disabled', false).removeClass('disabled');
          }
        });
      } else {
        $('#googleUserNotice').addClass('d-none');
        $('#resetBtn').prop('disabled', false).removeClass('disabled');
      }
    });

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