@extends('front.layout.master')

@section('title', $title ?? 'Blog')
@section('description', $description ?? 'Read our latest articles and insights about legal documents, privacy policies, and business compliance.')
@section('keywords', $keywords ?? 'blog, articles, legal documents, privacy policy, terms and conditions, business compliance')

@push('meta')
<!-- Blog Page Specific Meta Tags -->
<meta property="og:image" content="{{ asset('images/blog-og-image.jpg') }}">
<meta name="twitter:image" content="{{ asset('images/blog-twitter-image.jpg') }}">
@endpush

@push('structured-data')
{{-- Breadcrumb Schema --}}
{!! breadcrumb_schema($breadcrumbs ?? []) !!}
@endpush

@section('content')
{{-- Hero Section --}}
@include('front.layout.partials.hero-section', [
'heroTitle' => 'Our Blog',
'heroSubtitle' => 'Stay informed with the latest insights on legal documents, privacy policies, and business compliance. Discover expert tips and industry updates.',
'heroIcon' => 'book-open',
'heroIconSize' => '200px',
'heroButtons' => [
[
'text' => 'Browse Articles',
'url' => '#blog-posts',
'type' => 'btn-primary',
'icon' => 'book-open'
],
[
'text' => 'Subscribe to Updates',
'url' => '#newsletter',
'type' => 'btn-outline-primary',
'icon' => 'mail'
]
],
'heroLayout' => 'centered',
'heroBackground' => 'bg-light'
])

<!-- Blog Posts Section -->
<section id="blog-posts" class="py-5">
  <div class="container px-3 px-md-5">
    @if($blogs->count() > 0)
    <div class="row">
      @foreach($blogs as $blog)
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card feature-card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-body" style="transition: all 0.3s ease;">
          <div class="card-img-top position-relative overflow-hidden" style="height: 200px;">
            @if($blog->hasFeaturedImage())
            <img src="{{ $blog->featured_image_url }}"
              alt="{{ $blog->title }}"
              class="img-fluid w-100 h-100 object-fit-cover"
              onerror="this.src='<?= asset('images/blog-placeholder.svg') ?>'">
            @else
            <div class="bg-light d-flex align-items-center justify-content-center h-100">
              <div class="text-center text-muted">
                <i data-lucide="image" style="width: 32px; height: 32px;" class="mb-2 opacity-50"></i>
                <!-- <small class="d-block">Blog Post</small> -->
              </div>
            </div>
            @endif
            @if($blog->is_featured)
            <span class="position-absolute top-0 start-0 badge bg-primary m-2">
              <i data-lucide="star" class="icon-xs me-1"></i>
              Featured
            </span>
            @endif
          </div>

          <div class="card-body d-flex flex-column">
            <h5 class="card-title mb-2">
              <a href="{{ route('front.blogs.show', $blog->slug) }}">
                {{ $blog->title }}
              </a>
            </h5>

            <div class="mb-2">
              <small class="text-muted d-flex flex-wrap align-items-center gap-2">
                <span><i data-lucide="calendar" class="icon-xs me-1"></i>
                  {{ $blog->published_at->format('M d, Y') }} </span>
                @if($blog->views_count > 0)
                <span><i data-lucide="eye" class="icon-xs me-1"></i>{{ number_format($blog->views_count) }} views</span>
                @endif
                @if($blog->reading_time)
                <span><i data-lucide="clock" class="icon-xs me-1"></i>{{ $blog->reading_time }} min read</span>
                @endif
              </small>
            </div>



            @if($blog->excerpt)
            <p class="card-text text-muted flex-grow-1">{{ Str::limit($blog->excerpt, 120) }}</p>
            @endif

            <div class="mt-2">
              <a href="{{ route('front.blogs.show', $blog->slug) }}" aria-label="{{$blog->title}}" class="btn btn-primary btn-sm rounded-pill btn-hover-elevate">
                <i data-lucide="arrow-right" class="icon-sm me-1"></i>
                Read More
              </a>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>

    <!-- Pagination -->
    @if($blogs->hasPages())
    <div class="row mt-5">
      <div class="col-12">
        <nav aria-label="Blog pagination">
          {{ $blogs->links() }}
        </nav>
      </div>
    </div>
    @endif

    @else
    <!-- No Posts -->
    <div class="row">
      <div class="col-12 text-center py-5">
        <div class="mb-4">
          <i data-lucide="file-text" class="icon-xxl text-muted"></i>
        </div>
        <h3 class="text-muted mb-3">No Blog Posts Yet</h3>
        <p class="text-muted">We're working on creating great content for you. Check back soon!</p>
        <a href="{{ route('front.home') }}" class="btn btn-primary">
          <i data-lucide="home" class="icon-sm me-2"></i>
          Back to Home
        </a>
      </div>
    </div>
    @endif
  </div>
</section>
@endsection