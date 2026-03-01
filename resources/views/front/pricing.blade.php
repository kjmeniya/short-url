@extends('front.layout.master', [
    'page_title'       => 'Pricing',
    'page_description' => 'Simple, transparent pricing. Start free and upgrade when you need more power.',
])

@section('content')

{{-- ── Hero ──────────────────────────────────────────────────────────────────── --}}
<section class="pricing-hero position-relative overflow-hidden py-6 text-center">
  <div class="position-absolute top-0 start-0 w-100 h-100 pointer-events-none" aria-hidden="true">
    <div class="position-absolute rounded-circle"
         style="width:700px;height:700px;background:radial-gradient(circle,rgba(var(--bs-primary-rgb),.12) 0%,transparent 70%);top:-250px;left:50%;transform:translateX(-50%);filter:blur(60px);"></div>
  </div>
  <div class="container position-relative z-1">
    <div class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill fw-medium animate-fade-in-up">
      <i data-lucide="tag" class="icon-sm me-1"></i> Simple Pricing
    </div>
    <h1 class="display-4 fw-black mb-3 animate-fade-in-up-delay-1">
      Plans that grow <span class="text-gradient">with you</span>
    </h1>
    <p class="lead text-muted mb-4 mx-auto animate-fade-in-up-delay-2" style="max-width:520px;">
      Start for free. Upgrade only when you need more. No hidden fees, no surprises.
    </p>
    <div class="d-flex align-items-center justify-content-center gap-3 animate-fade-in-up-delay-3">
      <span class="d-flex align-items-center gap-1 text-muted small">
        <i data-lucide="shield-check" class="icon-xs text-success"></i> No credit card required
      </span>
      <span class="text-muted small opacity-25">|</span>
      <span class="d-flex align-items-center gap-1 text-muted small">
        <i data-lucide="refresh-cw" class="icon-xs text-primary"></i> Cancel anytime
      </span>
      <span class="text-muted small opacity-25">|</span>
      <span class="d-flex align-items-center gap-1 text-muted small">
        <i data-lucide="zap" class="icon-xs text-warning"></i> Instant setup
      </span>
    </div>
  </div>
</section>

{{-- ── Plan Cards ────────────────────────────────────────────────────────────── --}}
<section class="py-5 pb-6 position-relative">
  <div class="container">
    @if(isset($plans) && $plans->isNotEmpty())
    <div class="row g-4 justify-content-center align-items-stretch">

      @foreach($plans as $plan)
        @php
          $isFeatured     = $plan->sort_order === 3;
          $sortedFeatures = $plan->features->sortByDesc('is_include');
        @endphp

        <div class="col-md-6 col-lg-4 d-flex">
          <div class="pricing-card-page position-relative d-flex flex-column w-100 rounded-4 overflow-visible
                       {{ $isFeatured ? 'pricing-card-featured' : '' }}">

            {{-- Glow for featured --}}
            @if($isFeatured)
              <div class="pricing-glow" aria-hidden="true"></div>
              <div class="position-absolute top-0 start-50 translate-middle-x" style="z-index:3;margin-top:-14px;">
                <span class="badge bg-primary rounded-pill px-4 py-2 shadow fw-bold" style="font-size:.72rem;letter-spacing:.07em;">
                  ✦ MOST POPULAR
                </span>
              </div>
            @endif

            <div class="pricing-card-inner d-flex flex-column h-100 rounded-4 overflow-hidden
                         {{ $isFeatured ? 'border border-primary' : 'border' }}"
                 style="{{ $isFeatured ? 'border-width:2px!important;' : '' }}">

              {{-- Card Header --}}
              <div class="pricing-card-header p-4 pb-3 {{ $isFeatured ? 'bg-primary bg-opacity-5' : '' }}">
                <div class="d-flex align-items-center gap-2 mb-3">
                  <div class="pricing-icon d-inline-flex align-items-center justify-content-center rounded-3 flex-shrink-0
                               {{ $isFeatured ? 'bg-primary text-white' : 'bg-primary bg-opacity-10 text-primary' }}">
                    @if($plan->sort_order == 1)
                      <i data-lucide="sparkles" class="icon-sm"></i>
                    @elseif($plan->sort_order == 2)
                      <i data-lucide="zap" class="icon-sm"></i>
                    @else
                      <i data-lucide="rocket" class="icon-sm"></i>
                    @endif
                  </div>
                  <span class="text-uppercase fw-bold text-muted" style="font-size:.72rem;letter-spacing:.12em;">
                    {{ $plan->name }}
                  </span>
                </div>

                <div class="d-flex align-items-baseline gap-2 flex-wrap mb-1">
                  @if($plan->price == 0)
                    <span class="display-4 fw-black">Free</span>
                  @else
                    <span class="display-4 fw-black">Free</span>
                    <span class="text-muted fw-medium text-decoration-line-through" style="font-size:1.15rem;opacity:.5;">${{ number_format($plan->price, 0) }}/mo</span>
                  @endif
                </div>
                @if($plan->price > 0)
                  <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 mb-2 d-inline-block" style="font-size:.72rem;">Limited time offer</span>
                @endif
                <p class="text-muted small mb-0">{{ $plan->description }}</p>
              </div>

              {{-- CTA --}}
              <div class="px-4 py-3 {{ $isFeatured ? 'bg-primary bg-opacity-5' : '' }} border-top {{ $isFeatured ? 'border-primary border-opacity-25' : '' }}">
                <a href="{{ route('user.register') }}"
                   class="btn w-100 rounded-pill fw-semibold py-2
                          {{ $isFeatured ? 'btn-primary shadow' : 'btn-outline-primary' }}">
                  @if($plan->price == 0)
                    Get Started Free
                  @else
                    Get {{ $plan->name }} Plan
                  @endif
                  <i data-lucide="arrow-right" class="icon-sm ms-1"></i>
                </a>
              </div>

              {{-- Feature List --}}
              <div class="p-4 flex-grow-1 bg-body">
                <p class="text-muted small fw-semibold text-uppercase mb-3" style="letter-spacing:.07em;">
                  What's included
                </p>
                @if($sortedFeatures->isNotEmpty())
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                  @foreach($sortedFeatures as $feature)
                  <li class="d-flex align-items-start gap-3 pricing-feature-row">
                    @if($feature->is_include)
                      <span class="flex-shrink-0 d-inline-flex align-items-center justify-content-center rounded-circle bg-success bg-opacity-10 text-success pricing-badge"
                            style="width:24px;height:24px;margin-top:1px;">
                        <i data-lucide="check" style="width:13px;height:13px;stroke-width:2.5;"></i>
                      </span>
                      <span class="small fw-medium" style="line-height:1.55;">
                        {{ $feature->feature_title ?: $feature->feature_name }}
                        @if($feature->feature_name === 'create_link' && $feature->feature_value)
                          @if($feature->feature_value === '-1')
                            <span class="text-muted fw-normal">&nbsp;— Unlimited</span>
                          @else
                            <span class="text-muted fw-normal">&nbsp;— up to {{ $feature->feature_value }}</span>
                          @endif
                        @endif
                        @if($feature->feature_name === 'analytics' && $feature->feature_value)
                          <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill ms-1 px-2" style="font-size:.65rem;">
                            {{ ucfirst($feature->feature_value) }}
                          </span>
                        @endif
                      </span>
                    @else
                      <span class="flex-shrink-0 d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 text-danger pricing-badge"
                            style="width:24px;height:24px;margin-top:1px;">
                        <i data-lucide="x" style="width:13px;height:13px;stroke-width:2.5;"></i>
                      </span>
                      <span class="small text-muted" style="line-height:1.55;opacity:.5;text-decoration:line-through;">
                        {{ $feature->feature_title ?: $feature->feature_name }}
                      </span>
                    @endif
                  </li>
                  @endforeach
                </ul>
                @endif
              </div>

            </div>
          </div>
        </div>
      @endforeach

    </div>
    @endif
  </div>
</section>

{{-- ── FAQ ─────────────────────────────────────────────────────────────────── --}}
<section class="py-6 bg-body-tertiary">
  <div class="container">
    <div class="row justify-content-center mb-5">
      <div class="col-lg-6 text-center">
        <div class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2 rounded-pill fw-medium">FAQ</div>
        <h2 class="display-6 fw-bold mb-2">Frequently Asked Questions</h2>
        <p class="text-muted">Have more questions? <a href="/#contact" class="text-primary text-decoration-none fw-medium">Contact us</a>.</p>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="accordion accordion-flush d-flex flex-column gap-3" id="pricingFaq">

          @php
          $faqs = [
            ['q' => 'Can I switch plans at any time?',
             'a' => 'Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately and billing is prorated.'],
            ['q' => 'Is there a free trial for paid plans?',
             'a' => 'Our Free plan is available forever at no cost. Paid plans can be cancelled anytime, so you can try risk-free.'],
            ['q' => 'What payment methods do you accept?',
             'a' => 'We accept all major credit cards, debit cards, and PayPal via our secure payment processor.'],
            ['q' => 'Are my links permanent?',
             'a' => 'Yes, your short links never expire unless you set an expiry date or click limit. Even on the Free plan, links remain active.'],
            ['q' => 'Do you offer refunds?',
             'a' => 'We offer a 14-day money-back guarantee on all paid plans, no questions asked.'],
          ];
          @endphp

          @foreach($faqs as $i => $faq)
          <div class="accordion-item border rounded-4 overflow-hidden shadow-sm">
            <h3 class="accordion-header" id="faqHead{{ $i }}">
              <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }} fw-semibold bg-body" type="button"
                      data-bs-toggle="collapse" data-bs-target="#faqBody{{ $i }}"
                      aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="faqBody{{ $i }}">
                {{ $faq['q'] }}
              </button>
            </h3>
            <div id="faqBody{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}"
                 aria-labelledby="faqHead{{ $i }}" data-bs-parent="#pricingFaq">
              <div class="accordion-body text-muted">{{ $faq['a'] }}</div>
            </div>
          </div>
          @endforeach

        </div>
      </div>
    </div>
  </div>
</section>

{{-- ── CTA ─────────────────────────────────────────────────────────────────── --}}
<section class="py-6 bg-primary text-white text-center position-relative overflow-hidden">
  <div class="position-absolute top-0 end-0 opacity-10" style="transform:translate(20%,-30%) scale(1.4);">
    <i data-lucide="zap" style="width:200px;height:200px;"></i>
  </div>
  <div class="container position-relative z-1">
    <h2 class="display-6 fw-bold mb-3 text-white">Ready to get started?</h2>
    <p class="text-white-50 lead mb-4">Join thousands of users already growing with {{ site_name() }}.</p>
    <a href="{{ route('user.register') }}" class="btn btn-light btn-lg rounded-pill px-5 fw-bold shadow btn-hover-elevate d-inline-flex align-items-center gap-2">
      Start for Free <i data-lucide="arrow-right" class="icon-sm"></i>
    </a>
  </div>
</section>

@endsection

@push('style')
<style>
/* ── Pricing Page Cards ───────────────────────────────────────────────────── */
.pricing-card-page {
  transition: transform .3s cubic-bezier(.34,1.56,.64,1);
}
.pricing-card-page:hover {
  transform: translateY(-10px);
}

.pricing-card-featured {
  z-index: 2;
  transform: translateY(-6px);
}
.pricing-card-featured:hover {
  transform: translateY(-16px);
}

.pricing-card-inner {
  transition: box-shadow .3s ease;
}
.pricing-card-page:hover .pricing-card-inner {
  box-shadow: 0 24px 60px rgba(var(--bs-primary-rgb), .18) !important;
}
.pricing-card-featured .pricing-card-inner {
  box-shadow: 0 16px 48px rgba(var(--bs-primary-rgb), .22) !important;
}
.pricing-card-featured:hover .pricing-card-inner {
  box-shadow: 0 32px 72px rgba(var(--bs-primary-rgb), .3) !important;
}

/* Glow blob behind featured card */
.pricing-glow {
  position: absolute;
  inset: -30px;
  z-index: -1;
  border-radius: 2rem;
  background: radial-gradient(ellipse at center,
    rgba(var(--bs-primary-rgb),.18) 0%,
    transparent 70%);
  filter: blur(30px);
  transition: opacity .3s ease;
}
.pricing-card-featured:hover .pricing-glow {
  opacity: 1.4;
}

.pricing-icon {
  width: 40px;
  height: 40px;
  transition: transform .3s ease;
}
.pricing-card-page:hover .pricing-icon {
  transform: rotate(-6deg) scale(1.15);
}

/* Feature row hover */
.pricing-feature-row {
  border-radius: .5rem;
  padding: .2rem .4rem;
  margin: 0 -.4rem;
  transition: background .2s ease;
}
.pricing-feature-row:hover {
  background: rgba(var(--bs-primary-rgb), .05);
}

/* Gap utility */
.gap-25 { gap: .6rem !important; }

/* Accordion */
.accordion-button:not(.collapsed) {
  color: var(--bs-primary);
  background-color: rgba(var(--bs-primary-rgb), .04);
  box-shadow: none;
}
.accordion-button:focus { box-shadow: none; }

/* Hero gradient text */
.text-gradient {
  background: linear-gradient(135deg, var(--bs-primary), color-mix(in srgb, var(--bs-primary) 60%, #7c3aed));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>
@endpush
