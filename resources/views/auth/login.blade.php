@extends('auth.layouts.master', [
'page_title' => 'Login',
'page_description' => 'Secure login to access your dashboard and manage your account.',
'page_keywords' => 'login, sign in, user authentication, secure access, dashboard login'
])

@section('content')
<div class="row w-100 mx-0 auth-page">
  <div class="col-md-8 col-xl-6 mx-auto">
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
              <!-- Soft<span>Dev</span> -->
              <img src="{{ logo_url('frontend', 'large', 'light') }}" class="logo logo-light w-100px h-auto" alt="logo">
              <img src="{{ logo_url('frontend', 'large', 'dark') }}" class="logo logo-dark w-100px h-auto" alt="logo">
            </a>
            <h5 class="text-muted mb-3 fw-normal">Welcome back! Log in to your account.</h5>

            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <div class="d-flex align-items-start">
                <i data-lucide="alert-circle" class="icon-sm me-2 mt-1 text-danger"></i>
                <div class="flex-grow-1">
                  @foreach ($errors->all() as $error)
                  <div class="mb-1">{{ $error }}</div>
                  @endforeach
                  @if ($errors->has('email') && (str_contains($errors->first('email'), 'password') || str_contains($errors->first('email'), 'incorrect')))
                  <div class="mt-2">
                    <small class="text-muted">
                      <i data-lucide="help-circle" class="icon-xs me-1"></i>
                      <a href="{{ route('auth.forgot-password') }}" class="text-decoration-none">Forgot your password?</a>
                    </small>
                  </div>
                  @endif
                  @if ($errors->has('email') && str_contains($errors->first('email'), "couldn't find"))
                  <div class="mt-2">
                    <a href="{{ route('auth.register') }}" class="btn btn-outline-success btn-sm"><i data-lucide="user-plus" class="icon-sm me-2"></i>Create a new account</a>
                  </div>
                  @endif
                </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form class="forms-sample" method="POST" action="{{ route('auth.login.post') }}" id="loginForm">
              @csrf
              <div class="mb-3">
                <label for="userEmail" class="form-label">
                  Email address <span class="text-danger">*</span>
                  <i data-lucide="help-circle" class="icon-xs ms-1 text-muted"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-title="Enter the email address associated with your account"
                    style="cursor: help;"></i>
                </label>
                <input name="email" type="email" class="form-control" id="userEmail"
                  placeholder="Email"
                  value="{{ old('email') }}"
                  required>
              </div>
              @include('admin.partials.password-field', [
              'name' => 'password',
              'label' => 'Password',
              'placeholder' => 'Enter your password',
              'required' => true,
              'showStrengthMeter' => false,
              'autocomplete' => 'current-password'
              ])
              <div class="mb-3 d-flex justify-content-between">
                <div class="form-check">
                  <input name="remember" type="checkbox" class="form-check-input" id="authCheck">
                  <label class="form-check-label" for="authCheck">
                    Remember me
                    <i data-lucide="help-circle" class="icon-xs ms-1 text-muted"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      data-bs-title="Keep you signed in for 30 days on this device"
                      style="cursor: help;"></i>
                  </label>
                </div>
                <div class="text-end">
                  <a href="{{ route('auth.forgot-password') }}">Forgot password?</a>
                </div>
              </div>
              <div class="text-center">
                <button type="submit" class="btn btn-sm btn-primary me-2 mb-2 mb-md-0" id="loginBtn">
                  <i data-lucide="log-in" class="icon-sm me-1"></i>
                  <span class="btn-text">Login</span>
                </button>
                @if(app(\App\Services\SettingsService::class)->get('google_auth_enabled'))
                <a href="{{ route('auth.google') }}" class="btn btn-sm btn-outline-light mb-2 mb-md-0" id="googleBtn">
                  <svg class='btn-icon-prepend icon-sm' fill='currentColor' viewBox="-3 0 262 262" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid">
                    <g id="SVGRepo_bgCarrier" strokeWidth="0"></g>
                    <g id="SVGRepo_tracerCarrier" strokeLinecap="round" strokeLinejoin="round"></g>
                    <g id="SVGRepo_iconCarrier">
                      <path d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027" fill="#4285F4"></path>
                      <path d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1" fill="#34A853"></path>
                      <path d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782" fill="#FBBC05"></path>
                      <path d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251" fill="#EB4335"></path>
                    </g>
                  </svg>
                  <span class="btn-text">Continue with Google</span>
                </a>
                @endif
              </div>

            </form>

            <!-- Security Information Tooltip -->
            <div class="mt-2 text-center">
              <span class="text-muted"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-html="true"
                data-bs-title="<p class='p-2'><strong class='text-success'>Security Notice:</strong><br>Your account will be temporarily locked after <b class='text-danger'>5 failed login attempts</b> for security purposes.<br>If you're having trouble accessing your account, please use the '<b>Forgot Password</b>' option.</p>"
                style="cursor: help;">
                <i data-lucide="shield-check" class="icon-xs me-1 text-success"></i>
                <small>Security Information</small>
              </span>
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
    // Initialize form validation with custom password rule for login (min 6 chars)
    AuthValidation.init('#loginForm', {
      password: {
        required: true,
        minlength: 6
      }
    }, {
      password: {
        required: "Please enter your password",
        minlength: "Password must be at least 6 characters"
      }
    }, 'Logging in...');

    // Add loading state for Google button
    $('#googleBtn').on('click', function() {
      const btn = $(this);
      const originalText = btn.find('.btn-text').text();
      btn.find('.btn-text').text('Redirecting...');
      btn.find('.btn-icon-prepend').hide();
      btn.prepend('<span class="spinner-border spinner-border-sm icon-sm me-1" role="status"></span>');
      btn.prop('disabled', true);
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