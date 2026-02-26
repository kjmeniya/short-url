@extends('auth.layouts.master', [
'page_title' => 'Sign up',
'page_description' => 'Create your account to get started with our platform.',
'page_keywords' => 'register, sign up, create account, new user, registration'
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
            <h5 class="text-muted  mb-3 fw-normal">Create a free account.</h5>

            @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            @endif

            <form class="forms-sample" method="POST" action="{{ route('auth.register.post') }}" id="registerForm">
              @csrf
              <div class="mb-3">
                <label for="userName" class="form-label">
                  Full Name<span class="text-danger">*</span>
                  <i data-lucide="help-circle" class="icon-xs ms-1 text-muted"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-title="Enter your full name as it will appear on your account"
                    style="cursor: help;"></i>
                </label>
                <input name="name" type="text" class="form-control" id="userName"
                  placeholder="Enter your full name"
                  value="{{ old('name') }}"
                  required>
              </div>
              <div class="mb-3">
                <label for="userEmail" class="form-label">
                  Email address<span class="text-danger">*</span>
                  <i data-lucide="help-circle" class="icon-xs ms-1 text-muted"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    data-bs-title="Enter a valid email address. This will be used for login and account notifications"
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
              'placeholder' => 'Create a strong password',
              'required' => true,
              'showStrengthMeter' => true,
              'autocomplete' => 'new-password',
              'requirements' => [
              'length' => ['enabled' => true, 'min' => 8],
              'uppercase' => ['enabled' => true],
              'lowercase' => ['enabled' => true],
              'number' => ['enabled' => true],
              'special' => ['enabled' => true]
              ]
              ])
              @include('admin.partials.password-field', [
              'name' => 'password_confirmation',
              'label' => 'Confirm Password',
              'placeholder' => 'Confirm your password',
              'required' => true,
              'showStrengthMeter' => false,
              'autocomplete' => 'new-password',
              'requirements' => []
              ])
              <div class="text-center">
                <button type="submit" class="btn btn-sm btn-primary me-2 mb-2 mb-md-0" id="registerBtn">
                  <i data-lucide="user-plus" class="icon-sm me-1"></i>
                  <span class="btn-text">Sign up</span>
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
              <p class="mt-3 text-secondary text-center">Already have an account? <a href="{{ url('/auth/login') }}">Sign in</a></p>
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
    // Initialize form validation
    AuthValidation.init('#registerForm', {}, {}, 'Creating account...');

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