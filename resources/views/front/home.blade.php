@extends('front.layout.master', [
'page_title' => 'Home',
'page_description' => site_description(),
])

@section('content')
<!-- Hero Section -->
<section class="py-5 bg-body">
  <div class="container">
    <div class="row align-items-center min-vh-75">
      <div class="col-lg-6 mb-5 mb-lg-0">
        <h1 class="display-4 fw-bold mb-4">
          Welcome to {{ site_name() }}
        </h1>
        <p class="lead text-muted mb-4">
          {{ site_description() }}
        </p>
        <div class="d-flex gap-3">
          <a href="{{ route('auth.register') }}" class="btn btn-primary">
            Get Started
          </a>
          <a href="{{ route('auth.login') }}" class="btn btn-outline-primary">
            Login
          </a>
        </div>
      </div>
      <div class="col-lg-6 text-center">
        <img src="{{ logo_url('frontend', 'large', 'light') }}" class="logo logo-light img-fluid" alt="{{ site_name() }}" style="max-width: 300px;">
        <img src="{{ logo_url('frontend', 'large', 'dark') }}" class="logo logo-dark img-fluid" alt="{{ site_name() }}" style="max-width: 300px;">
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section id="features" class="py-5 bg-light-subtle">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Features</h2>
      <p class="text-muted">Discover what makes us different</p>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <div class="mb-3">
              <i data-lucide="shield-check" class="text-primary" style="width: 48px; height: 48px;"></i>
            </div>
            <h5 class="card-title">Secure</h5>
            <p class="card-text text-muted">Your data is protected with industry-standard security measures.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <div class="mb-3">
              <i data-lucide="zap" class="text-primary" style="width: 48px; height: 48px;"></i>
            </div>
            <h5 class="card-title">Fast</h5>
            <p class="card-text text-muted">Optimized performance for the best user experience.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm">
          <div class="card-body text-center p-4">
            <div class="mb-3">
              <i data-lucide="users" class="text-primary" style="width: 48px; height: 48px;"></i>
            </div>
            <h5 class="card-title">User Friendly</h5>
            <p class="card-text text-muted">Intuitive interface designed with users in mind.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- About Section -->
<section id="about" class="py-5 bg-body">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-4 mb-lg-0">
        <h2 class="fw-bold mb-3">About Us</h2>
        <p class="text-muted mb-3">We are dedicated to providing the best solutions for your needs. Our team of experts works tirelessly to ensure you get the quality service you deserve.</p>
        <p class="text-muted">With years of experience and a passion for excellence, we help businesses and individuals achieve their goals efficiently.</p>
      </div>
      <div class="col-lg-6">
        <div class="row g-3">
          <div class="col-6">
            <div class="p-3 bg-light-subtle rounded text-center">
              <h3 class="fw-bold text-primary mb-1">500+</h3>
              <small class="text-muted">Happy Clients</small>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-light-subtle rounded text-center">
              <h3 class="fw-bold text-primary mb-1">50+</h3>
              <small class="text-muted">Projects</small>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-light-subtle rounded text-center">
              <h3 class="fw-bold text-primary mb-1">10+</h3>
              <small class="text-muted">Years Experience</small>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 bg-light-subtle rounded text-center">
              <h3 class="fw-bold text-primary mb-1">24/7</h3>
              <small class="text-muted">Support</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-5 bg-light-subtle">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Contact Us</h2>
      <p class="text-muted">Get in touch with us</p>
    </div>

    <div class="row g-5">
      <!-- Contact Form -->
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4 p-lg-5">
            <h3 class="fw-bold mb-4">Send us a Message</h3>

            <form action="{{ route('front.contact.send') }}" method="POST" id="contactForm" class="forms-sample" novalidate>
              @csrf
              <div class="row g-3">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                      id="name" name="name" value="{{ old('name') }}"
                      placeholder="Enter your full name"
                      maxlength="100" data-maxlength="true" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Full name (2-100 characters)</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                      id="email" name="email" value="{{ old('email') }}"
                      placeholder="your.email@example.com"
                      maxlength="255" data-maxlength="true" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">We'll never share your email with anyone</small>
                  </div>
                </div>
                <div class="col-12">
                  <div class="mb-3">
                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('subject') is-invalid @enderror"
                      id="subject" name="subject" value="{{ old('subject') }}"
                      placeholder="What is this regarding?"
                      maxlength="200" data-maxlength="true" required>
                    @error('subject')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Brief subject line (3-200 characters)</small>
                  </div>
                </div>
                <div class="col-12">
                  <div class="mb-3">
                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('message') is-invalid @enderror"
                      id="message" name="message" rows="5"
                      placeholder="Tell us more about your inquiry..."
                      maxlength="2000" data-maxlength="true" required>{{ old('message') }}</textarea>
                    @error('message')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Detailed message (10-2000 characters)</small>
                  </div>
                </div>
                <div class="col-12">
                  <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                      <i data-lucide="send" class="icon-sm me-2"></i>Send Message
                    </button>
                    <button type="reset" class="btn btn-outline-primary" id="resetBtn">
                      <i data-lucide="rotate-ccw" class="icon-sm me-2"></i>Reset
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body p-4 p-lg-5">
            <h3 class="fw-bold mb-4">Get in Touch</h3>
            <p class="text-muted mb-4">
              We're here to help and answer any question you might have. We look forward to hearing from you.
            </p>

            <div class="d-flex mb-4">
              <div class="flex-shrink-0">
                <div class="bg-primary bg-opacity-10 rounded p-3">
                  <i data-lucide="mail" class="text-primary" style="width: 24px; height: 24px;"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <div class="fw-semibold mb-1 h6">Email</div>
                <p class="text-muted mb-0">info@softdev.in</p>
              </div>
            </div>

            <div class="d-flex mb-4">
              <div class="flex-shrink-0">
                <div class="bg-success bg-opacity-10 rounded p-3">
                  <i data-lucide="clock" class="text-success" style="width: 24px; height: 24px;"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <div class="fw-semibold mb-1 h6">Response Time</div>
                <p class="text-muted mb-0">We typically respond within 24 hours</p>
              </div>
            </div>

            <div class="d-flex mb-4">
              <div class="flex-shrink-0">
                <div class="bg-info bg-opacity-10 rounded p-3">
                  <i data-lucide="message-circle" class="text-info" style="width: 24px; height: 24px;"></i>
                </div>
              </div>
              <div class="flex-grow-1 ms-3">
                <div class="fw-semibold mb-1 h6">Support</div>
                <p class="text-muted mb-0">Technical support available for API users</p>
              </div>
            </div>

            <hr class="my-4">

            <div class="fw-semibold mb-3 h6">Quick Links</div>
            <div class="d-flex flex-wrap gap-2">
              <a href="#" class="btn btn-outline-primary btn-sm">
                <i data-lucide="help-circle" class="icon-xs me-1"></i>FAQ
              </a>
              <a href="#" class="btn btn-outline-primary btn-sm">
                <i data-lucide="shield" class="icon-xs me-1"></i>Privacy
              </a>
              <a href="#" class="btn btn-outline-primary btn-sm">
                <i data-lucide="file-text" class="icon-xs me-1"></i>Terms
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section id="cta" class="py-5 bg-primary text-white">
  <div class="container text-center">
    <h2 class="fw-bold mb-3">Ready to get started?</h2>
    <p class="mb-4">Join us today and experience the difference.</p>
    <a href="{{ route('auth.register') }}" class="btn btn-light">
      Create Account
    </a>
  </div>
</section>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('build/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
@endpush

@push('style')
<style>
  .min-vh-75 {
    min-height: 75vh;
  }
</style>
@endpush

@push('custom-scripts')
@vite(['resources/js/front/validation/contact.js'])
<script>
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  });
</script>
@endpush