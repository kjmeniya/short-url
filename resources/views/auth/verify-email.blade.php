@extends('auth.layouts.master', [
'page_title' => 'Verify Email',
'page_description' => 'Verify your email address to complete your account setup.',
'page_keywords' => 'email verification, verify email, account activation, email confirmation'
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
          <div class="auth-form-wrapper px-4 py-5">
            <a href="{{url('/')}}" class="nobleui-logo d-block mb-2">
              <img src="{{ logo_url('frontend', 'large', 'light') }}" class="logo logo-light w-100px h-auto" alt="logo">
              <img src="{{ logo_url('frontend', 'large', 'dark') }}" class="logo logo-dark w-100px h-auto" alt="logo">
            </a>
            <h5 class="text-muted mb-3 fw-normal">Verify Your Email Address</h5>

            @if (session('success'))
            <div class="alert alert-success" role="alert">
              <i data-feather="check-circle" class="icon-sm me-2"></i>
              {{ session('success') }}
            </div>
            @endif

            @if (session('info'))
            <div class="alert alert-info" role="alert">
              <i data-feather="info" class="icon-sm me-2"></i>
              {{ session('info') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger" role="alert">
              @foreach ($errors->all() as $error)
              <div><i data-feather="alert-circle" class="icon-sm me-2"></i>{{ $error }}</div>
              @endforeach
            </div>
            @endif

            <div class="mb-4">
              <p class="text-muted">
                We've sent a 6-digit verification code to your email address. Please enter the code below to verify your account.
              </p>
              <p class="text-muted">
                <small>The code will expire in 10 minutes.</small>
              </p>
            </div>

            <form method="POST" action="{{ route('auth.verification.verify.post') }}" id="verifyForm">
              @csrf
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email"
                  placeholder="Enter your email address"
                  value="{{ old('email', session('verification_email') ?? (Auth::check() ? Auth::user()->email : '')) }}"
                  required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <label for="code" class="form-label">Verification Code</label>
                <input type="text" name="code" class="form-control form-control-lg text-center @error('code') is-invalid @enderror"
                  id="code"
                  placeholder="000000"
                  maxlength="6"
                  pattern="[0-9]{6}"
                  inputmode="numeric"
                  autocomplete="one-time-code"
                  required
                  style="letter-spacing: 0.5em; font-size: 1.5rem;">
                @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Enter the 6-digit code from your email</small>
              </div>

              <button type="submit" class="btn btn-primary w-100 mb-3" id="verifyBtn">
                <i data-lucide="check-circle" class="icon-sm me-1"></i>
                <span class="btn-text">Verify Email</span>
              </button>
            </form>

            <div class="text-center mb-3">
              <p class="text-muted mb-2">Didn't receive the code?</p>
              <form method="POST" action="{{ route('auth.verification.resend.post') }}" id="resendForm">
                @csrf
                <input type="hidden" name="email" value="{{ old('email', session('verification_email') ?? (Auth::check() ? Auth::user()->email : '')) }}">
                <button type="submit" class="btn btn-sm btn-outline-primary" id="resendBtn">
                  <i data-lucide="mail" class="icon-sm me-1"></i>
                  <span class="btn-text">Resend Code</span>
                </button>
              </form>
            </div>

            <div class="text-center">
              <a href="{{ route('auth.login') }}" class="text-muted">
                <i data-lucide="arrow-left" class="icon-sm me-1"></i>
                Back to Login
              </a>
            </div>

            <div class="mt-4 text-center">
              <p class="text-muted">
                <small>
                  <i data-feather="shield" class="icon-sm me-1"></i>
                  Email verification helps keep your account secure
                </small>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Auto-format OTP input
  $('#code').on('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
  });

  // Add loading state for verify button
  $('#verifyForm').on('submit', function() {
    const btn = $('#verifyBtn');
    btn.find('.btn-text').text('Verifying...');
    btn.find('i').removeClass().addClass('spinner-border spinner-border-sm icon-sm me-1');
    btn.prop('disabled', true);
  });

  // Add loading state for resend button
  $('#resendForm').on('submit', function() {
    const btn = $('#resendBtn');
    btn.find('.btn-text').text('Sending...');
    btn.find('i').removeClass().addClass('spinner-border spinner-border-sm icon-sm me-1');
    btn.prop('disabled', true);
  });

  // Initialize Lucide icons
  if (typeof lucide !== 'undefined') {
    lucide.createIcons();
  }

  // Auto-focus on code input
  $(document).ready(function() {
    $('#code').focus();
  });
</script>
@endpush