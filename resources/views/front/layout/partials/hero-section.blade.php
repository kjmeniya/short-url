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
  - $heroBackground: Background class (optional, default: 'bg-light')
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
$heroBackground = $heroBackground ?? 'bg-light';
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

<section class="{{ $heroBackground }}">
  <div class="container px-3 py-4 px-md-5 py-md-5">
    @if($heroLayout === 'centered')
    {{-- Centered Layout --}}
    <div class="row align-items-center text-center {{ $heroMinHeight }}">
      <div class="col-12">
        @if($heroBadge)
        <div class="badge bg-primary text-white rounded-pill px-3 py-2 mb-3">
          <i data-lucide="{{ $heroBadgeIcon }}" class="icon-xs me-1"></i>
          {{ $heroBadge }}
        </div>
        @endif

        <h1 class="fw-bold mb-3">{{ $heroTitle }}</h1>

        @if($heroSubtitle)
        <p class="lead text-muted mb-4">
          {{ $heroSubtitle }}
        </p>
        @endif

        @if(count($heroButtons) > 0)
        <div class="d-flex flex-wrap justify-content-center gap-3">
          @foreach($heroButtons as $button)
          <a href="{{ $button['url'] ?? '#' }}"
            class="btn {{ $button['type'] ?? 'btn-primary' }} {{ $button['size'] ?? 'btn-sm' }}">
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
        <div class="mt-4">
          <small class="text-muted">
            <i data-lucide="{{ $heroLastUpdatedIcon }}" class="icon-sm me-1"></i>
            {{ $heroLastUpdatedText }}
            @if(is_object($heroLastUpdated) && method_exists($heroLastUpdated, 'format'))
            {{ $heroLastUpdated->format('F j, Y') }}
            @else
            {{ $heroLastUpdated }}
            @endif
          </small>
        </div>
        @endif
      </div>
    </div>
    @else
    {{-- Split Layout (Default) --}}
    <div class="row align-items-center {{ $heroMinHeight }}">
      <div class="col-lg-6">
        @if($heroBadge)
        <div class="badge bg-primary text-white rounded-pill px-3 py-2 mb-3">
          <i data-lucide="{{ $heroBadgeIcon }}" class="icon-xs me-1"></i>
          {{ $heroBadge }}
        </div>
        @endif

        <h1 class="fw-bold mb-3">{{ $heroTitle }}</h1>

        @if($heroSubtitle)
        <p class="lead text-muted mb-4">
          {{ $heroSubtitle }}
        </p>
        @endif

        @if(count($heroButtons) > 0)
        <div class="d-flex flex-wrap gap-3">
          @foreach($heroButtons as $button)
          <a href="{{ $button['url'] ?? '#' }}"
            class="btn {{ $button['type'] ?? 'btn-primary' }} {{ $button['size'] ?? '' }}">
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
        <div class="mt-4">
          <small class="text-muted">
            <i data-lucide="{{ $heroLastUpdatedIcon }}" class="icon-sm me-1"></i>
            {{ $heroLastUpdatedText }}
            @if(is_object($heroLastUpdated) && method_exists($heroLastUpdated, 'format'))
            {{ $heroLastUpdated->format('F j, Y') }}
            @else
            {{ $heroLastUpdated }}
            @endif
          </small>
        </div>
        @endif
      </div>

      <div class="col-lg-6 text-center {{ $heroIconShowinMobile ? 'd-none d-lg-block' : '' }}">
        <div class="mt-5 mt-lg-0">
          <i data-lucide="{{ $heroIcon }}"
            style="width: <?= $heroIconSize ?>; height: <?= $heroIconSize ?>;"
            class="text-primary opacity-75 {{ $heroIconClass }}"></i>
        </div>
      </div>
    </div>
    @endif
  </div>
</section>
@if($heroShowBreadcrumbs)
<section class="border-bottom">
  <div class="container px-3 py-3 px-md-5 ">
    <nav aria-label="breadcrumb" class="">
      <ol class="breadcrumb mb-0">
        @foreach($heroBreadcrumbItems as $i => $item)
        @php $isLast = $i === count($heroBreadcrumbItems) - 1; @endphp
        @if(!$isLast && !empty($item['url']))
        <li class="breadcrumb-item"><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
        @else
        <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
        @endif
        @endforeach
      </ol>
    </nav>
  </div>
</section>
@endif