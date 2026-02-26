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

          {{-- Glow backdrop --}}
          <div class="position-absolute top-50 start-50 translate-middle rounded-circle z-0"
            style="width:340px;height:340px;background:radial-gradient(circle,rgba(var(--bs-primary-rgb),.28) 0%,transparent 70%);filter:blur(40px);pointer-events:none;"></div>

          {{-- Browser chrome card --}}
          <div class="shortener-mock-card glass-card rounded-4 shadow-lg position-relative z-2 p-0 overflow-hidden">

            {{-- Titlebar --}}
            <div class="shortener-mock-titlebar d-flex align-items-center gap-2 px-4 py-3">
              <span class="mock-dot bg-danger"></span>
              <span class="mock-dot bg-warning"></span>
              <span class="mock-dot bg-success"></span>
              <div class="mock-addressbar flex-grow-1 d-flex align-items-center gap-2 ms-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success opacity-75">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
                <span class="mock-addressbar-text text-muted">{{ rtrim(url('/'), '/') }}</span>
              </div>
            </div>

            {{-- App body --}}
            <div class="p-4 pt-3">

              <div class="text-start mb-3">
                <span class="fw-semibold" style="font-size:.8rem;letter-spacing:.06em;text-transform:uppercase;opacity:.5;">Shorten a URL</span>
              </div>

              {{-- AJAX form --}}
              <form id="heroShortenForm" novalidate>
                @csrf
                <div class="mock-shorten-input d-flex align-items-center gap-2 p-2 rounded-pill mb-1" id="shortenInputWrapper">
                  <div class="mock-input-icon d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                    style="width:34px;height:34px;background:rgba(var(--bs-primary-rgb),.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                      <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                      <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                    </svg>
                  </div>
                  <input type="url" id="heroUrlInput" name="url"
                    class="flex-grow-1 border-0 bg-transparent text-body mock-url-input"
                    placeholder="Paste your long URL here…" autocomplete="off" />
                  <button type="submit" id="heroShortenBtn"
                    class="btn btn-primary btn-sm rounded-pill px-3 flex-shrink-0 d-flex align-items-center gap-1"
                    style="font-size:.78rem;font-weight:600;min-width:90px;justify-content:center;">
                    <span class="btn-label">Shorten →</span>
                    <span class="btn-spinner d-none">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="spin-anim">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                      </svg>
                    </span>
                  </button>
                </div>
                <div id="heroUrlError" class="text-danger text-start ms-2 d-none" style="font-size:.75rem;"></div>
              </form>

              {{-- Result panel --}}
              <div id="heroResultPanel" class="d-none hero-result-panel">
                <div class="d-flex align-items-center gap-2 my-3">
                  <div class="flex-grow-1" style="height:1px;background:rgba(128,128,128,.12);"></div>
                  <span style="font-size:.7rem;opacity:.4;font-weight:600;letter-spacing:.05em;">YOUR SHORT LINK</span>
                  <div class="flex-grow-1" style="height:1px;background:rgba(128,128,128,.12);"></div>
                </div>

                <div class="mock-result-row d-flex align-items-center gap-2 p-3 rounded-3">
                  <div class="flex-grow-1 text-start overflow-hidden">
                    <div style="font-size:.7rem;opacity:.45;margin-bottom:2px;">Ready to share</div>
                    <div class="fw-bold text-truncate text-primary" style="font-size:.9rem;" id="heroShortUrlDisplay"></div>
                  </div>
                  <div class="d-flex gap-2 flex-shrink-0">
                    <button type="button" class="mock-action-btn" id="heroCopyBtn" title="Copy short URL">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                      </svg>
                    </button>
                    <a href="#" target="_blank" class="mock-action-btn" id="heroOpenBtn" title="Open link">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                        <polyline points="15 3 21 3 21 9" />
                        <line x1="10" y1="14" x2="21" y2="3" />
                      </svg>
                    </a>
                  </div>
                </div>

                <div class="d-flex gap-2 flex-wrap mt-3">
                  <div class="mock-chip mock-chip-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="20 6 9 17 4 12" />
                    </svg>
                    Active &amp; Live
                  </div>
                  <div class="mock-chip">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                    </svg>
                    <span id="heroClicksChip">0</span> clicks
                  </div>
                  <div class="mock-chip">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10" />
                      <polyline points="12 6 12 12 16 14" />
                    </svg>
                    No expiry
                  </div>
                  <button type="button" id="heroShortenAnother" class="mock-chip mock-chip-link border-0 bg-transparent">
                    ↩ Shorten another
                  </button>
                </div>
              </div>

            </div>{{-- /app body --}}
          </div>{{-- /card --}}

          {{-- Live ticker --}}
          <div class="d-flex gap-3 mt-4 justify-content-center flex-wrap">
            <div class="mock-ticker-pill">
              <span class="mock-ticker-dot bg-success"></span>
              <span class="mock-ticker-num">5.2M+</span>
              <span class="mock-ticker-label">links shortened</span>
            </div>
            <div class="mock-ticker-pill">
              <span class="mock-ticker-dot bg-primary"></span>
              <span class="mock-ticker-num">Free</span>
              <span class="mock-ticker-label">no sign-up needed</span>
            </div>
          </div>

        </div>
      </div>

</section>

{{-- ── Your Shortened Links Section ── --}}
@if(isset($guestLinks) && $guestLinks->isNotEmpty())
<section id="guestLinksSection" class="py-5">
  @else
  <section id="guestLinksSection" class="py-5 d-none">
    @endif
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
          <h5 class="fw-bold mb-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-primary" style="vertical-align:-3px">
              <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
              <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
            </svg>
            Your Shortened Links
          </h5>
          <p class="text-muted small mb-0">Links saved in this browser — <a href="{{ route('auth.register') }}" class="text-primary">create an account</a> to keep them forever.</p>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" id="guestLinksCount">
          @isset($guestLinks) {{ $guestLinks->count() }} link{{ $guestLinks->count() !== 1 ? 's' : '' }} @endisset
        </span>
      </div>

      <div id="guestLinksList" class="row g-3">
        @isset($guestLinks)
        @foreach($guestLinks as $link)
        <div class="col-12">
          <div class="card border-0 shadow-sm rounded-3 guest-link-card">
            <div class="card-body py-3 px-4">
              <div class="d-flex align-items-center gap-3 flex-wrap">

                {{-- Short URL badge --}}
                <div class="flex-shrink-0">
                  <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:.78rem;letter-spacing:.02em;">
                    {{ rtrim(url('/'), '/') }}/{{ $link->custom_alias ?: $link->code }}
                  </span>
                </div>

                {{-- Arrow + destination --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted flex-shrink-0 d-none d-sm-block">
                  <polyline points="9 18 15 12 9 6" />
                </svg>
                <p class="mb-0 text-muted small flex-grow-1 text-truncate" title="{{ $link->original_url }}" style="max-width:340px;">
                  {{ $link->original_url }}
                </p>

                {{-- Meta --}}
                <div class="d-flex align-items-center gap-3 flex-shrink-0 ms-auto flex-wrap">
                  <span class="text-muted small">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                      <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                    </svg>
                    {{ number_format($link->clicks) }} clicks
                  </span>
                  @php $sc=['active'=>'success','inactive'=>'secondary','expired'=>'danger']; @endphp
                  <span class="badge bg-{{ $sc[$link->status] ?? 'secondary' }} bg-opacity-15" style="font-size:.7rem;">
                    {{ ucfirst($link->status) }}
                  </span>
                  <span class="text-muted" style="font-size:.72rem;">{{ $link->created_at->diffForHumans() }}</span>
                  {{-- Copy --}}
                  <button class="btn btn-sm btn-outline-secondary guest-copy-btn rounded-pill px-2" data-url="{{ $link->short_url }}" title="Copy">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                    </svg>
                  </button>
                  {{-- Open --}}
                  <a href="{{ $link->short_url }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Open">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                      <polyline points="15 3 21 3 21 9" />
                      <line x1="10" y1="14" x2="21" y2="3" />
                    </svg>
                  </a>
                </div>

              </div>
            </div>
          </div>
        </div>
        @endforeach
        @endisset
      </div>

      {{-- View all + nudge --}}
      <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 mt-4 pt-2 border-top" id="guestLinksFooter" style="border-color:rgba(128,128,128,.1) !important;">
        <p class="text-muted small mb-0">
          Showing up to 5 links.
          <a href="{{ route('front.guest-links') }}" id="viewAllLinksBtn" class="text-primary fw-semibold text-decoration-none">
            View all →
          </a>
        </p>
        <a href="{{ route('auth.register') }}" class="btn btn-primary btn-sm rounded-pill px-4">
          Create a Free Account
        </a>
      </div>
    </div>
  </section>

  {{-- Features Section --}}
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

      // ── Lucide icons ──────────────────────────────────────────────────────────
      if (typeof lucide !== 'undefined') lucide.createIcons();

      // ── Hero AJAX Shortener ───────────────────────────────────────────────────
      const form = document.getElementById('heroShortenForm');
      const input = document.getElementById('heroUrlInput');
      const btn = document.getElementById('heroShortenBtn');
      const btnLabel = btn.querySelector('.btn-label');
      const btnSpinner = btn.querySelector('.btn-spinner');
      const errorEl = document.getElementById('heroUrlError');
      const resultPanel = document.getElementById('heroResultPanel');
      const shortDisplay = document.getElementById('heroShortUrlDisplay');
      const copyBtn = document.getElementById('heroCopyBtn');
      const openBtn = document.getElementById('heroOpenBtn');
      const clicksChip = document.getElementById('heroClicksChip');
      const anotherBtn = document.getElementById('heroShortenAnother');

      let currentShortUrl = '';

      function setLoading(loading) {
        btn.disabled = loading;
        btnLabel.classList.toggle('d-none', loading);
        btnSpinner.classList.toggle('d-none', !loading);
      }

      function showError(msg) {
        errorEl.textContent = msg;
        errorEl.classList.remove('d-none');
        input.style.outline = '2px solid rgba(220,53,69,.5)';
      }

      function clearError() {
        errorEl.classList.add('d-none');
        errorEl.textContent = '';
        input.style.outline = '';
      }

      function showResult(data) {
        currentShortUrl = data.short_url;
        shortDisplay.textContent = data.short_url;
        if (openBtn) openBtn.href = data.short_url;
        if (clicksChip) clicksChip.textContent = data.clicks ?? 0;

        // Hide form, reveal result with animation
        form.classList.add('d-none');
        resultPanel.classList.remove('d-none');
        resultPanel.style.opacity = '0';
        resultPanel.style.transform = 'translateY(8px)';
        requestAnimationFrame(() => {
          resultPanel.style.transition = 'opacity .35s ease, transform .35s ease';
          resultPanel.style.opacity = '1';
          resultPanel.style.transform = 'translateY(0)';
        });
      }

      function resetForm() {
        resultPanel.classList.add('d-none');
        resultPanel.style.transition = '';
        form.classList.remove('d-none');
        input.value = '';
        clearError();
        currentShortUrl = '';
        input.focus();
      }

      if (form) {
        form.addEventListener('submit', async function(e) {
          e.preventDefault();
          clearError();

          const url = input.value.trim();
          if (!url) {
            showError('Please enter a URL.');
            return;
          }
          if (!/^https?:\/\//i.test(url)) {
            showError('URL must start with https:// or http://');
            return;
          }

          setLoading(true);
          try {
            const resp = await fetch('{{ route("front.shorten") }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                  '{{ csrf_token() }}'
              },
              body: JSON.stringify({
                url
              })
            });

            const data = await resp.json();

            if (resp.ok && data.success) {
              showResult(data);
              refreshGuestLinks();
            } else {
              // Laravel validation error structure
              const msg = data.message ||
                (data.errors?.url?.[0]) ||
                'Something went wrong. Please try again.';
              showError(msg);
            }
          } catch (err) {
            showError('Network error. Please check your connection.');
          } finally {
            setLoading(false);
          }
        });
      }

      // ── Copy button ──────────────────────────────────────────────────────────
      if (copyBtn) {
        copyBtn.addEventListener('click', function() {
          if (!currentShortUrl) return;
          navigator.clipboard.writeText(currentShortUrl).then(() => {
            const orig = copyBtn.innerHTML;
            copyBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
            copyBtn.style.color = 'var(--bs-success)';
            setTimeout(() => {
              copyBtn.innerHTML = orig;
              copyBtn.style.color = '';
            }, 2000);
          });
        });
      }

      // ── Shorten another ──────────────────────────────────────────────────────
      if (anotherBtn) {
        anotherBtn.addEventListener('click', resetForm);
      }

      // ── Guest links: copy button delegation ─────────────────────────────────
      document.addEventListener('click', function(e) {
        const btn = e.target.closest('.guest-copy-btn');
        if (!btn) return;
        const url = btn.dataset.url;
        if (!url) return;
        navigator.clipboard.writeText(url).then(() => {
          const orig = btn.innerHTML;
          btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
          btn.style.color = 'var(--bs-success)';
          setTimeout(() => {
            btn.innerHTML = orig;
            btn.style.color = '';
          }, 2000);
        });
      });

      // ── Refresh guest links via AJAX ─────────────────────────────────────────
      function refreshGuestLinks() {
        fetch('{{ route("front.my-links") }}', {
            headers: {
              'Accept': 'application/json'
            }
          })
          .then(r => r.json())
          .then(data => {
            const section = document.getElementById('guestLinksSection');
            const list = document.getElementById('guestLinksList');
            const countEl = document.getElementById('guestLinksCount');
            const footerEl = document.getElementById('guestLinksFooter');
            if (!section || !list) return;

            if (!data.links || data.links.length === 0) {
              section.classList.add('d-none');
              return;
            }

            section.classList.remove('d-none');

            // Show only first 5 in the preview
            const visible = data.links.slice(0, 5);
            const total = data.total ?? data.links.length;
            const shown = visible.length;

            if (countEl) countEl.textContent = shown + ' link' + (shown !== 1 ? 's' : '');

            // Update footer text
            if (footerEl) {
              const footerText = footerEl.querySelector('p');
              if (footerText) {
                footerText.innerHTML = total > shown ?
                  `Showing ${shown} of ${total} links. <a href="{{ route('front.guest-links') }}" class="text-primary fw-semibold text-decoration-none">View all ${total} →</a>` :
                  `${total} link${total !== 1 ? 's' : ''} in this browser. <a href="{{ route('front.guest-links') }}" class="text-primary fw-semibold text-decoration-none">View all →</a>`;
              }
            }

            const statusColors = {
              active: 'success',
              inactive: 'secondary',
              expired: 'danger'
            };

            list.innerHTML = visible.map(link => {
              const sc = statusColors[link.status] || 'secondary';
              const shortDisplay = link.short_url;
              const originTrunc = link.original_url.length > 60 ? link.original_url.slice(0, 57) + '…' : link.original_url;
              return `
            <div class="col-12">
              <div class="card border-0 shadow-sm rounded-3 guest-link-card" style="animation:fadeSlideIn .3s ease;">
                <div class="card-body py-3 px-4">
                  <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="flex-shrink-0">
                      <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold" style="font-size:.78rem;">${shortDisplay}</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted flex-shrink-0 d-none d-sm-block"><polyline points="9 18 15 12 9 6"/></svg>
                    <p class="mb-0 text-muted small flex-grow-1 text-truncate" title="${link.original_url}" style="max-width:340px;">${originTrunc}</p>
                    <div class="d-flex align-items-center gap-3 flex-shrink-0 ms-auto flex-wrap">
                      <span class="text-muted small">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="me-1"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        ${link.clicks} clicks
                      </span>
                      <span class="badge bg-${sc} bg-opacity-15" style="font-size:.7rem;">${link.status.charAt(0).toUpperCase()+link.status.slice(1)}</span>
                      <span class="text-muted" style="font-size:.72rem;">${link.created_at}</span>
                      <button class="btn btn-sm btn-outline-secondary guest-copy-btn rounded-pill px-2" data-url="${link.short_url}" title="Copy">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                      </button>
                      <a href="${link.short_url}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2" title="Open">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>`;
            }).join('');
          })
          .catch(() => {}); // silently ignore
      }

      // ── Spin animation for loading spinner ──────────────────────────────────
      const style = document.createElement('style');
      style.textContent = `
    .spin-anim { animation: heroSpin .7s linear infinite; }
    @keyframes heroSpin { to { transform: rotate(360deg); } }
    .mock-url-input:focus { outline: none; }
    .mock-chip-success { color: var(--bs-success); background: rgba(var(--bs-success-rgb),.1); border-color: rgba(var(--bs-success-rgb),.2); }
    .mock-chip-link { cursor: pointer; color: var(--bs-primary); transition: color .15s; }
    .mock-chip-link:hover { color: var(--bs-primary); text-decoration: underline; }
    @keyframes fadeSlideIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
    .guest-link-card { transition: box-shadow .2s; }
    .guest-link-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08) !important; }
  `;
      document.head.appendChild(style);

    });
  </script>
  @endpush