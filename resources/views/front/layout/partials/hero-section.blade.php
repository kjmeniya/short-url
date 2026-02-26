{{--
  Common Hero Section Component for Frontend Pages

  Props:
  - $heroTitle: Main title (required)
  - $heroSubtitle: Subtitle/description (optional)
  - $heroIcon: Lucide icon name (optional, default: 'shield-check')
  - $heroIconSize: Icon size (optional, default: '280px')
  - $heroIconClass: Icon class (optional, default: '')
  - $heroIconShowinMobile: Icon show in mobile (optional, default: '')
  - $heroBadge: Badge text (optional)
  - $heroBadgeIcon: Badge icon (optional, default: 'star')
  - $heroButtons: Array of buttons with 'text', 'url', 'type', 'icon' (optional)
  - $heroLayout: Layout type - 'centered', 'split' (optional, default: 'split')
  - $heroBackground: Background class (optional, default: 'hero-section position-relative overflow-hidden')
  - $heroMinHeight: Minimum height class (optional)
  - $heroLastUpdated: Last updated date (optional, Carbon instance or string)
  - $heroLastUpdatedIcon: Last updated icon (optional, default: 'calendar')
  - $heroLastUpdatedText: Last updated text prefix (optional, default: 'Last updated:')
  - $heroShowBreadcrumbs: Show breadcrumbs bar inside hero (optional, default: false)
  - $heroBreadcrumbItems: Optional array of custom breadcrumb items: [ [ 'label' => 'Home', 'url' => url('/') ], [ 'label' => 'Current' ] ]
--}}

@php
// Set default values
$heroTitle = $heroTitle ?? 'Page Title';
$heroSubtitle = $heroSubtitle ?? '';
$heroIcon = $heroIcon ?? 'shield-check';
$heroIconSize = $heroIconSize ?? '280px';
$heroIconClass = $heroIconClass ?? '';
$heroIconShowinMobile = $heroIconShowinMobile ?? true;
$heroBadge = $heroBadge ?? '';
$heroBadgeIcon = $heroBadgeIcon ?? 'star';
$heroButtons = $heroButtons ?? [];
$heroLayout = $heroLayout ?? 'split';
$heroBackground = $heroBackground ?? 'hero-section position-relative overflow-hidden';
$heroMinHeight = $heroMinHeight ?? '';
$heroLastUpdated = $heroLastUpdated ?? null;
$heroLastUpdatedIcon = $heroLastUpdatedIcon ?? 'calendar';
$heroLastUpdatedText = $heroLastUpdatedText ?? 'Last updated:';
// Breadcrumbs defaults
$heroShowBreadcrumbs = $heroShowBreadcrumbs ?? false;
$heroBreadcrumbItems = $heroBreadcrumbItems ?? null;
if ($heroBreadcrumbItems === null) {
$heroBreadcrumbItems = [
[ 'label' => 'Home', 'url' => url('/') ],
[ 'label' => $heroTitle, 'url' => null ],
];
}
@endphp

<section class="{{ $heroBackground }} py-6 bg-body">
  <div class="hero-bg-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
  </div>
  <div class="container px-3 py-4 px-md-5 py-md-5 position-relative z-1">
    @if($heroLayout === 'centered')
    {{-- Centered Layout --}}
    <div class="row align-items-center text-center {{ $heroMinHeight }}">
      <div class="col-12">
        @if($heroBadge)
        <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 mb-3 animate-fade-in-up">
          <i data-lucide="{{ $heroBadgeIcon }}" class="icon-xs me-1"></i>
          {{ $heroBadge }}
        </div>
        @endif

        <h1 class="display-3 fw-black mb-3 animate-fade-in-up-delay-1 hero-title">{{ $heroTitle }}</h1>

        @if($heroSubtitle)
        <p class="lead text-muted mb-4 animate-fade-in-up-delay-2 hero-subtitle max-w-2xl mx-auto">
          {{ $heroSubtitle }}
        </p>
        @endif

        @if(count($heroButtons) > 0)
        <div class="d-flex flex-wrap justify-content-center gap-3 animate-fade-in-up-delay-3">
          @foreach($heroButtons as $button)
          <a href="{{ $button['url'] ?? '#' }}"
            class="btn {{ $button['type'] ?? 'btn-primary' }} {{ $button['size'] ?? 'btn-lg' }} rounded-pill px-4 py-3 d-inline-flex align-items-center btn-hover-elevate shadow-sm">
            @if(isset($button['icon']))
            <i data-lucide="{{ $button['icon'] }}" class="icon-sm me-2"></i>
            @endif
            {{ $button['text'] ?? 'Button' }}
          </a>
          @endforeach
        </div>
        @endif

        {{-- Last Updated Info for Centered Layout --}}
        @if($heroLastUpdated)
        <div class="mt-4 animate-fade-in-up-delay-3">
          <small class="text-muted bg-body-tertiary px-3 py-2 rounded-pill d-inline-block">
            <i data-lucide="{{ $heroLastUpdatedIcon }}" class="icon-sm me-1 text-primary"></i>
            {{ $heroLastUpdatedText }}
            @if(is_object($heroLastUpdated) && method_exists($heroLastUpdated, 'format'))
            <strong>{{ $heroLastUpdated->format('F j, Y') }}</strong>
            @else
            <strong>{{ $heroLastUpdated }}</strong>
            @endif
          </small>
        </div>
        @endif
      </div>
    </div>
    @else
    {{-- Split Layout (Default) --}}
    <div class="row align-items-center {{ $heroMinHeight }}">
      <div class="col-lg-6 mb-5 mb-lg-0 text-center text-lg-start">
        @if($heroBadge)
        <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 mb-3 animate-fade-in-up">
          <i data-lucide="{{ $heroBadgeIcon }}" class="icon-xs me-1"></i>
          {{ $heroBadge }}
        </div>
        @endif

        <h1 class="display-3 fw-black mb-4 animate-fade-in-up-delay-1 hero-title text-gradient">{{ $heroTitle }}</h1>

        @if($heroSubtitle)
        <p class="lead text-muted mb-5 animate-fade-in-up-delay-2 hero-subtitle">
          {{ $heroSubtitle }}
        </p>
        @endif

        @if(count($heroButtons) > 0)
        <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3 animate-fade-in-up-delay-3">
          @foreach($heroButtons as $button)
          <a href="{{ $button['url'] ?? '#' }}"
            class="btn {{ $button['type'] ?? 'btn-primary' }} {{ $button['size'] ?? 'btn-lg' }} rounded-pill px-4 py-3 d-inline-flex align-items-center btn-hover-elevate shadow-sm">
            @if(isset($button['icon']))
            <i data-lucide="{{ $button['icon'] }}" class="icon-sm me-2"></i>
            @endif
            {{ $button['text'] ?? 'Button' }}
          </a>
          @endforeach
        </div>
        @endif

        {{-- Last Updated Info for Split Layout --}}
        @if($heroLastUpdated)
        <div class="mt-4 animate-fade-in-up-delay-3">
          <small class="text-muted bg-body-tertiary px-3 py-2 rounded-pill d-inline-block">
            <i data-lucide="{{ $heroLastUpdatedIcon }}" class="icon-sm me-1 text-primary"></i>
            {{ $heroLastUpdatedText }}
            @if(is_object($heroLastUpdated) && method_exists($heroLastUpdated, 'format'))
            <strong>{{ $heroLastUpdated->format('F j, Y') }}</strong>
            @else
            <strong>{{ $heroLastUpdated }}</strong>
            @endif
          </small>
        </div>
        @endif
      </div>

      <div class="col-lg-6 text-center position-relative {{ $heroIconShowinMobile ? '' : 'd-none d-lg-block' }}">
        <div class="hero-image-wrapper animate-float position-relative z-2">
          <div class="glass-card d-inline-flex align-items-center justify-content-center rounded-4 p-5 shadow-lg">
            <i data-lucide="{{ $heroIcon }}"
              style="width: <?= $heroIconSize ?>; height: <?= $heroIconSize ?>;"
              class="text-primary opacity-75 {{ $heroIconClass }}"></i>
          </div>
        </div>
        <div class="position-absolute top-50 start-50 translate-middle w-100 h-100 bg-primary opacity-25 blur-3xl rounded-circle z-0" style="filter: blur(80px);"></div>
      </div>
    </div>
    @endif
  </div>
</section>
@if($heroShowBreadcrumbs)
<section class="border-bottom bg-body-tertiary shadow-sm">
  <div class="container px-3 py-3 px-md-5 ">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 fw-medium">
        @foreach($heroBreadcrumbItems as $i => $item)
        @php $isLast = $i === count($heroBreadcrumbItems) - 1; @endphp
        @if(!$isLast && !empty($item['url']))
        <li class="breadcrumb-item"><a href="{{ $item['url'] }}" class="text-decoration-none text-muted transition-all hover-primary">{{ $item['label'] }}</a></li>
        @else
        <li class="breadcrumb-item active text-primary" aria-current="page">{{ $item['label'] }}</li>
        @endif
        @endforeach
      </ol>
    </nav>
  </div>
</section>
@endif