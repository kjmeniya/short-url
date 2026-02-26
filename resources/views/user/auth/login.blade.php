@extends('auth.layouts.master', [
    'page_title'       => 'User Login',
    'page_description' => 'Log in to your account to manage your short links.',
    'page_keywords'    => 'user login, sign in, short links, account access',
])

@section('content')
<div class="row w-100 mx-0 auth-page">
  <div class="col-md-8 col-xl-6 mx-auto">
    <div class="card">
      <div class="row">
        <div class="col-md-4 pe-md-0">
          <div class="auth-side-wrapper" style="background: linear-gradient(135deg, #245dac 0%, #0d47a1 100%); display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem; color:#fff; text-align:center;">
            <i data-lucide="link-2" style="width:60px;height:60px;opacity:.85;margin-bottom:1rem;"></i>
            <h5 class="fw-bold mb-2 text-white">User Portal</h5>
            <p class="small mb-0" style="opacity:.75;">Manage and track your shortened links from one dashboard.</p>
          </div>
        </div>
        <div class="col-md-8 ps-md-0">
          @include('auth.layouts.partials.theme-switcher')
          <div class="auth-form-wrapper px-4 py-5">
            <a href="{{ url('/') }}" class="nobleui-logo d-block mb-2">
              <img src="{{ logo_url('frontend', 'large', 'light') }}" class="logo logo-light w-100px h-auto" alt="logo">
              <img src="{{ logo_url('frontend', 'large', 'dark') }}" class="logo logo-dark w-100px h-auto" alt="logo">
            </a>
            <h5 class="text-muted mb-1 fw-normal">Welcome back!</h5>
            <p class="text-muted small mb-3">Sign in to your user account to manage your links.</p>

            {{-- Success Message --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i data-lucide="check-circle" class="icon-sm me-2"></i>{{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            {{-- Errors --}}
            @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <div class="d-flex align-items-start">
                <i data-lucide="alert-circle" class="icon-sm me-2 mt-1 text-danger"></i>
                <div class="flex-grow-1">
                  @foreach ($errors->all() as $error)
                  <div class="mb-1">{{ $error }}</div>
                  @endforeach
                </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form class="forms-sample" method="POST" action="{{ route('user.login.post') }}" id="userLoginForm">
              @csrf
              <div class="mb-3">
                <label for="userEmail" class="form-label">
                  Email address <span class="text-danger">*</span>
                </label>
                <input name="email" type="email" class="form-control" id="userEmail"
                  placeholder="Enter your email"
                  value="{{ old('email') }}"
                  required>
              </div>

              @include('admin.partials.password-field', [
                'name'              => 'password',
                'label'             => 'Password',
                'placeholder'       => 'Enter your password',
                'required'          => true,
                'showStrengthMeter' => false,
                'autocomplete'      => 'current-password',
              ])

              <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="form-check">
                  <input name="remember" type="checkbox" class="form-check-input" id="rememberMe">
                  <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <a href="{{ route('auth.forgot-password') }}" class="small">Forgot password?</a>
              </div>

              <div class="text-center">
                <button type="submit" class="btn btn-sm btn-primary me-2 mb-2 mb-md-0" id="userLoginBtn">
                  <i data-lucide="log-in" class="icon-sm me-1"></i>
                  <span class="btn-text">Sign In</span>
                </button>
              </div>

              <p class="mt-3 text-secondary text-center">
                Don't have an account?
                <a href="{{ route('user.register') }}">Create one for free</a>
              </p>


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
    AuthValidation.init('#userLoginForm', {
      password: { required: true, minlength: 6 }
    }, {
      password: { required: 'Please enter your password', minlength: 'Password must be at least 6 characters' }
    }, 'Signing in...');

    if (typeof lucide !== 'undefined') lucide.createIcons();
  });
</script>
@endpush
