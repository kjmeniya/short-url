@extends('auth.layouts.master', [
    'page_title'       => 'User Sign Up',
    'page_description' => 'Create your user account to get started with shortening links.',
    'page_keywords'    => 'register, sign up, create account, new user, registration',
])

@section('content')
<div class="row w-100 mx-0 auth-page">
  <div class="col-md-8 col-xl-6 mx-auto">
    <div class="card">
      <div class="row">
        <div class="col-md-4 pe-md-0">
          <div class="auth-side-wrapper" style="background: linear-gradient(135deg, #245dac 0%, #0d47a1 100%); display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem; color:#fff; text-align:center;">
            <i data-lucide="user-plus" style="width:60px;height:60px;opacity:.85;margin-bottom:1rem;"></i>
            <h5 class="fw-bold mb-2 text-white">Join Us</h5>
            <p class="small mb-0" style="opacity:.75;">Create a free account and start managing your links efficiently.</p>
          </div>
        </div>
        <div class="col-md-8 ps-md-0">
          @include('auth.layouts.partials.theme-switcher')
          <div class="auth-form-wrapper px-4 py-5">
            <a href="{{url('/')}}" class="nobleui-logo d-block mb-2">
              <img src="{{ logo_url('frontend', 'large', 'light') }}" class="logo logo-light w-100px h-auto" alt="logo">
              <img src="{{ logo_url('frontend', 'large', 'dark') }}" class="logo logo-dark w-100px h-auto" alt="logo">
            </a>
            <h5 class="text-muted mb-3 fw-normal">Create a free account.</h5>

            @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            @endif

            <div class="text-center mt-4">
              @if(app(\App\Services\SettingsService::class)->get('google_auth_enabled'))
              <p class="text-muted mb-4 px-2">Click the button below to instantly create an account or sign in using your Google identity.</p>
              <a href="{{ route('auth.google') }}" class="btn btn-lg btn-outline-primary d-flex align-items-center justify-content-center mx-auto mb-4" id="googleBtn" style="max-width: 320px; font-weight: 500;">
                <svg class='btn-icon-prepend icon-sm me-2' fill='currentColor' viewBox="-3 0 262 262" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid">
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
              @else
              <p class="text-danger mb-4">Google authentication is not currently enabled. Please contact the administrator.</p>
              @endif
              
              <p class="mt-4 text-secondary text-center small">
                  Already have an account? <a href="{{ route('user.login') }}" class="fw-semibold">Sign in here</a>
              </p>
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
    $('#googleBtn').on('click', function() {
      const btn = $(this);
      btn.find('.btn-text').text('Redirecting...');
      btn.find('.btn-icon-prepend').hide();
      btn.prepend('<span class="spinner-border spinner-border-sm icon-sm me-2 mb-0" role="status"></span>');
      btn.addClass('disabled').css('pointer-events', 'none');
    });

    if (typeof lucide !== 'undefined') lucide.createIcons();
  });
</script>
@endpush
