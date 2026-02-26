<footer class="front-footer">

  {{-- ── Main Footer Body ── --}}
  <div class="container">
    <div class="front-footer-body">

      {{-- Brand Column --}}
      <div class="front-footer-brand-col">
        <a href="{{ url('/') }}" class="front-footer-logo">
          <img src="{{ logo_url('frontend', 'large', 'light') }}" class="logo logo-light object-fit-contain" alt="{{ site_name() }}" height="32">
          <img src="{{ logo_url('frontend', 'large', 'dark') }}" class="logo logo-dark  object-fit-contain" alt="{{ site_name() }}" height="32">
        </a>
        <p class="front-footer-tagline">{{ site_description() }}</p>
        {{-- Social Links --}}
        <div class="front-footer-socials">
          <a href="#" class="front-social-btn" aria-label="Twitter / X">
            <i data-lucide="twitter"></i>
          </a>
          <a href="#" class="front-social-btn" aria-label="GitHub">
            <i data-lucide="github"></i>
          </a>
          <a href="#" class="front-social-btn" aria-label="LinkedIn">
            <i data-lucide="linkedin"></i>
          </a>
          <a href="#" class="front-social-btn" aria-label="RSS Feed">
            <i data-lucide="rss"></i>
          </a>
        </div>
      </div>

      {{-- Product Links --}}
      <div class="front-footer-col">
        <h6 class="front-footer-heading">Product</h6>
        <ul class="front-footer-links">
          <li><a href="/#features">Features</a></li>
          <li><a href="/#about">About Us</a></li>
          <li><a href="{{ route('front.blogs.index') }}">Blog</a></li>
          <li><a href="/#contact">Contact</a></li>
        </ul>
      </div>

      {{-- Account Links --}}
      <div class="front-footer-col">
        <h6 class="front-footer-heading">Account</h6>
        <ul class="front-footer-links">
          @guest
          <li><a href="{{ route('user.login') }}">Login</a></li>
          <li><a href="{{ route('user.register') }}">Create Account</a></li>
          @endguest
          @auth
          <li><a href="{{ (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin()) ? route('admin.dashboard') : route('user.dashboard') }}">Dashboard</a></li>
          <li><a href="{{ (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin()) ? route('admin.profile') : route('user.profile') }}">Profile</a></li>
          @endauth
        </ul>
      </div>

      {{-- Legal Links --}}
      <div class="front-footer-col">
        <h6 class="front-footer-heading">Legal</h6>
        <ul class="front-footer-links">
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
          <li><a href="#">Cookie Policy</a></li>
        </ul>
      </div>

    </div>{{-- end body --}}
  </div>

  {{-- ── Bottom Bar ── --}}
  <div class="front-footer-bottom">
    <div class="container">
      <div class="front-footer-bottom-inner">
        <p class="mb-0">
          &copy; {{ date('Y') }} <strong>{{ site_name() }}</strong>. All rights reserved.
        </p>
        <p class="mb-0 front-footer-made">
          Made with <i data-lucide="heart" class="front-heart-icon"></i> passion
        </p>
      </div>
    </div>
  </div>

</footer>