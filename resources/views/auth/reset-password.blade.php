@extends('auth.layouts.master', [
'page_title' => 'Reset Password',
'page_description' => 'Set a new password for your account.',
'page_keywords' => 'reset password, new password, change password, account security'
])

@section('content')
<div class="page-wrapper full-page">
  <div class="page-content d-flex align-items-center justify-content-center">
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
                  <img src="{{ logo_url('frontend', 'large', 'light') }}" class="logo logo-light w-100px h-auto" alt="logo">
                  <img src="{{ logo_url('frontend', 'large', 'dark') }}" class="logo logo-dark w-100px h-auto" alt="logo">
                </a>
                <h5 class="text-muted mb-3 fw-normal">Reset your password</h5>

                @if ($errors->any())
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
                @endif

                <form class="forms-sample" method="POST" action="{{ route('auth.reset-password.post') }}" id="resetPasswordForm">
                  @csrf
                  <input type="hidden" name="token" value="{{ $token }}">
                  <input type="hidden" name="email" value="{{ $email }}">

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
                  'autocomplete' => 'new-password'
                  ])

                  <div class="mb-3">
                    <button type="submit" class="btn btn-sm btn-primary me-2 mb-2 mb-md-0 w-100" id="resetPasswordBtn">
                      <i data-lucide="key" class="icon-sm me-1"></i>
                      <span class="btn-text">Reset Password</span>
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
    // Initialize form validation with custom rules (no email validation needed)
    AuthValidation.init('#resetPasswordForm', {
      // Remove email from validation since it's hidden
      password_confirmation: {
        required: true,
        minlength: 8,
        equalTo: "input[name='password']"
      }
    }, {
      password: {
        required: "Please enter a password",
        minlength: "Password must be at least 8 characters"
      },
      password_confirmation: {
        required: "Please confirm your password",
        minlength: "Password confirmation must be at least 8 characters",
        equalTo: "Passwords do not match"
      }
    }, 'Resetting password...');

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