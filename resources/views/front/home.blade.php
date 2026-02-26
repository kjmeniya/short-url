@extends('front.layout.master', [
'page_title' => 'Home',
'page_description' => site_description(),
])

@section('content')
<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden py-6">
  <div class="hero-bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
  </div>
  <div class="container position-relative z-1">
    <div class="row align-items-center min-vh-75">
      <div class="col-lg-6 mb-5 mb-lg-0 text-center text-lg-start">
        <div class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill fw-medium animate-fade-in-up">
          <i data-lucide="zap" class="icon-sm me-1"></i> Fast & Secure Link Management
        </div>
        <h1 class="display-3 fw-black mb-4 animate-fade-in-up-delay-1 hero-title">
          Shorten Your Links, <br> <span class="text-gradient">Expand Your Reach.</span>
        </h1>
        <p class="lead text-muted mb-5 animate-fade-in-up-delay-2 hero-subtitle">
          {{ site_description() }} Transform long, ugly links into clean, trackable URLs that drive more clicks and better results.
        </p>
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center justify-content-lg-start animate-fade-in-up-delay-3">
          <a href="{{ route('auth.register') }}" class="btn btn-primary btn-lg px-4 py-3 shadow-primary rounded-pill d-flex align-items-center justify-content-center gap-2 btn-hover-elevate">
            Get Started For Free <i data-lucide="arrow-right" class="icon-sm"></i>
          </a>
          <a href="{{ route('auth.login') }}" class="btn btn-outline-secondary btn-lg px-4 py-3 rounded-pill d-flex align-items-center justify-content-center gap-2 btn-hover-elevate">
            Go to Dashboard
          </a>
        </div>
      </div>
      <div class="col-lg-6 text-center position-relative">
        <div class="hero-image-wrapper animate-float">
          <div class="glass-card p-4 rounded-4 shadow-lg position-relative z-2">
            <!-- Mock Shortener UI for visual appeal -->
            <div class="d-flex align-items-center border-bottom pb-3 mb-3">
              <div class="w-10px h-10px rounded-circle bg-danger me-2"></div>
              <div class="w-10px h-10px rounded-circle bg-warning me-2"></div>
              <div class="w-10px h-10px rounded-circle bg-success"></div>
            </div>
            <div class="mock-input-group d-flex p-2 bg-body-secondary rounded-pill mb-3">
              <div class="px-3 d-flex align-items-center text-muted"><i data-lucide="link"></i></div>
              <div class="text-start flex-grow-1 py-2 text-muted truncate">https://verylongurl.example.com/something/huge...</div>
              <div class="btn btn-primary rounded-pill px-4">Shorten</div>
            </div>
            <div class="d-flex justify-content-between align-items-center p-3 bg-body-tertiary rounded-3">
              <div class="fw-bold text-primary">{{ rtrim(url('/'), '/') }}/<span class="text-body text-opacity-50">xyz123</span></div>
              <div class="btn btn-sm btn-light rounded-circle p-2 shadow-sm"><i data-lucide="copy" class="text-primary icon-sm"></i></div>
            </div>
          </div>
          <!-- Decorative blurred elements behind the card -->
          <div class="position-absolute top-50 start-50 translate-middle w-100 h-100 bg-primary opacity-25 blur-3xl rounded-circle z-0" style="filter: blur(80px);"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section id="features" class="py-6 bg-body-tertiary position-relative">
  <div class="container">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-6 text-center">
        <div class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill fw-medium">Why Choose Us</div>
        <h2 class="display-5 fw-bold mb-3">Powerful Features</h2>
        <p class="text-muted lead">Everything you need to manage, track, and optimize your links in one place.</p>
      </div>
    </div>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="feature-card card h-100 border-0 rounded-4 shadow-sm p-2 transition-all bg-body">
          <div class="card-body p-4">
            <div class="feature-icon-wrapper bg-primary bg-opacity-10 text-primary rounded-4 d-inline-flex align-items-center justify-content-center mb-4 transition-all" style="width: 64px; height: 64px;">
              <i data-lucide="shield-check" style="width: 32px; height: 32px;"></i>
            </div>
            <h4 class="fw-bold mb-3">Bank-grade Security</h4>
            <p class="text-muted mb-0">Your data is protected with industry-standard security protocols, end-to-end encryption, and robust access controls.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card card h-100 border-0 rounded-4 shadow-sm p-2 transition-all bg-body">
          <div class="card-body p-4">
            <div class="feature-icon-wrapper bg-success bg-opacity-10 text-success rounded-4 d-inline-flex align-items-center justify-content-center mb-4 transition-all" style="width: 64px; height: 64px;">
              <i data-lucide="zap" style="width: 32px; height: 32px;"></i>
            </div>
            <h4 class="fw-bold mb-3">Lightning Fast</h4>
            <p class="text-muted mb-0">Global CDN integration ensures your links redirect instantly, providing the best experience for your users worldwide.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="feature-card card h-100 border-0 rounded-4 shadow-sm p-2 transition-all bg-body">
          <div class="card-body p-4">
            <div class="feature-icon-wrapper bg-warning bg-opacity-10 text-warning rounded-4 d-inline-flex align-items-center justify-content-center mb-4 transition-all" style="width: 64px; height: 64px;">
              <i data-lucide="bar-chart-2" style="width: 32px; height: 32px;"></i>
            </div>
            <h4 class="fw-bold mb-3">Detailed Analytics</h4>
            <p class="text-muted mb-0">Track clicks, geographic locations, referral sources, and device types to better understand your audience.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Stats / About Section -->
<section id="about" class="py-6 bg-body position-relative">
  <div class="container">
    <div class="row align-items-center bg-primary rounded-5 p-5 p-lg-6 text-white overflow-hidden position-relative shadow-lg">
      <div class="position-absolute top-0 end-0 opacity-10" style="transform: translate(20%, -20%) scale(1.5);">
        <i data-lucide="globe-2" style="width: 100px; height: 100px;"></i>
      </div>
      <div class="col-lg-5 mb-5 mb-lg-0 position-relative z-1">
        <div class="badge bg-white text-primary mb-3 px-3 py-2 rounded-pill fw-medium">About {{ site_name() }}</div>
        <h2 class="display-6 fw-bold mb-4 text-white">Connecting the world, one link at a time.</h2>
        <p class="mb-4 text-white-50 lead">We empower businesses and individuals to optimize their digital presence. Our infrastructure handles millions of redirects with zero downtime.</p>
        <a href="#contact" class="btn btn-light rounded-pill px-4 pb-2 pt-2 fw-medium btn-hover-elevate d-inline-block">Contact Us</a>
      </div>
      <div class="col-lg-7 position-relative z-1">
        <div class="row g-4">
          <div class="col-sm-6">
            <div class="glass-stats rounded-4 p-4 p-lg-5 text-center transition-all h-100">
              <h3 class="display-4 fw-bold mb-2">5M+</h3>
              <p class="mb-0 text-white-50 fw-medium">Links Shortened</p>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="glass-stats rounded-4 p-4 p-lg-5 text-center transition-all h-100">
              <h3 class="display-4 fw-bold mb-2">99.9%</h3>
              <p class="mb-0 text-white-50 fw-medium">Uptime Guarantee</p>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="glass-stats rounded-4 p-4 p-lg-5 text-center transition-all h-100">
              <h3 class="display-4 fw-bold mb-2">50k+</h3>
              <p class="mb-0 text-white-50 fw-medium">Active Users</p>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="glass-stats rounded-4 p-4 p-lg-5 text-center transition-all h-100">
              <h3 class="display-4 fw-bold mb-2">24/7</h3>
              <p class="mb-0 text-white-50 fw-medium">Expert Support</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-6 bg-body-tertiary">
  <div class="container">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-6 text-center">
        <div class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill fw-medium">Get in Touch</div>
        <h2 class="display-5 fw-bold mb-3">Let's start a conversation</h2>
        <p class="text-muted lead">Have questions? We're here to help you get the most out of {{ site_name() }}.</p>
      </div>
    </div>

    <div class="row g-5 justify-content-center">
      <!-- Contact Form -->
      <div class="col-lg-10">
        <div class="card bg-body border-0 shadow-lg rounded-4 overflow-hidden">
          <div class="row g-0">
            <div class="col-md-5 bg-primary text-white p-5 d-flex flex-column justify-content-between position-relative">
              <div class="position-absolute bottom-0 end-0 opacity-10 p-3">
                <i data-lucide="mail-plus" style="width: 150px; height: 150px;"></i>
              </div>
              <div class="position-relative z-1">
                <h3 class="fw-bold mb-4 text-white">Contact Info</h3>
                <p class="text-white-50 mb-5">Fill out the form and our team will get back to you within 24 hours.</p>

                <div class="d-flex align-items-center mb-4">
                  <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                    <i data-lucide="mail" class="text-white"></i>
                  </div>
                  <div>
                    <p class="mb-0 fs-14px text-white-50">Email Us</p>
                    <p class="mb-0 fw-medium">info@{{ request()->getHost() }}</p>
                  </div>
                </div>
                <div class="d-flex align-items-center mb-4">
                  <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                    <i data-lucide="map-pin" class="text-white"></i>
                  </div>
                  <div>
                    <p class="mb-0 fs-14px text-white-50">Location</p>
                    <p class="mb-0 fw-medium">Global Fully Remote</p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-7 p-5">
              <h4 class="fw-bold mb-4">Send Message</h4>
              <form action="{{ route('front.contact.send') }}" method="POST" id="contactForm" class="forms-sample" novalidate>
                @csrf
                <div class="row g-3">
                  <div class="col-12">
                    <div class="form-floating mb-2">
                      <input type="text" class="form-control rounded-3 border-light bg-body-tertiary @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="John Doe" required>
                      <label for="name">Your Name</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating mb-2">
                      <input type="email" class="form-control rounded-3 border-light bg-body-tertiary @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="john@example.com" required>
                      <label for="email">Email Address</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating mb-2">
                      <input type="text" class="form-control rounded-3 border-light bg-body-tertiary @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Subject" required>
                      <label for="subject">Subject</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating mb-4">
                      <textarea class="form-control rounded-3 border-light bg-body-tertiary @error('message') is-invalid @enderror" id="message" name="message" placeholder="Message" style="height: 120px" required>{{ old('message') }}</textarea>
                      <label for="message">Your Message</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm hover-lift d-flex align-items-center justify-content-center gap-2">
                      Send Message <i data-lucide="send" class="icon-sm"></i>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section id="cta" class="py-6 position-relative overflow-hidden">
  <div class="position-absolute top-0 start-0 w-100 h-100 bg-primary opacity-10"></div>
  <div class="container text-center position-relative z-1">
    <h2 class="display-5 fw-bold mb-4">Ready to unlock the power of short links?</h2>
    <p class="lead text-muted mb-5 max-w-2xl mx-auto">Join thousands of users who are already using our platform to connect with their audience.</p>
    <a href="{{ route('auth.register') }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 shadow-lg btn-hover-elevate d-inline-flex align-items-center gap-2">
      Start Shortening Now <i data-lucide="arrow-right" class="icon-sm"></i>
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