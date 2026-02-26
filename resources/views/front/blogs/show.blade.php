@extends('front.layout.master')

@section('title', $blog->meta_title ?: $blog->title)
@section('description', $blog->meta_description ?: $blog->excerpt)
@section('keywords', $blog->meta_keywords ?: '')

@push('meta')
<!-- Blog Post Specific Meta Tags -->
<meta property="og:image" content="{{ $blog->featured_image ? asset('storage/' . $blog->featured_image) : asset('images/blog-default-og.jpg') }}">
<meta name="twitter:image" content="{{ $blog->featured_image ? asset('storage/' . $blog->featured_image) : asset('images/blog-default-twitter.jpg') }}">
<meta property="article:modified_time" content="{{ $blog->updated_at->toISOString() }}">
@if($blog->author)
<meta property="article:author" content="{{ $blog->author->name }}">
@endif
@endpush

@push('structured-data')
{{-- Breadcrumb Schema --}}
{!! breadcrumb_schema([
['name' => 'Home', 'url' => route('front.home')],
['name' => 'Blogs', 'url' => route('front.blogs.index')],
['name' => $blog->title, 'url' => route('front.blogs.show', $blog->slug)]
]) !!}
@endpush
@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush
@section('content')
<!-- Hero Section -->
<section class="bg-light">
  <div class="container px-3 py-4 px-md-5 py-md-5">
    <div class="row align-items-center">
      <div class="col-lg-12 mx-auto">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('front.home') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('front.blogs.index') }}" class="text-decoration-none">Blog</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($blog->title, 50) }}</li>
          </ol>
        </nav>

        <!-- Blog Meta -->
        <div class="mb-2">
          @if($blog->is_featured)
          <span class="badge bg-primary">
            <i data-lucide="star" class="icon-xs me-1"></i>
            Featured Post
          </span>
          @endif
        </div>

        <!-- Blog Title -->
        <h1 class="fw-bold mb-3">{{ $blog->title }}</h1>
        <div class="text-muted mb-2">
          <small class="d-flex flex-wrap align-items-center gap-2 gap-md-3">
            <span>
              <i data-lucide="calendar" class="icon-xs me-1"></i>
              {{ $blog->published_at->format('F d, Y') }}
            </span>
            @if($blog->author)
            <span>
              <i data-lucide="user" class="icon-xs me-1"></i>
              {{ $blog->author->name }}
            </span>
            @endif
            @if($blog->reading_time)
            <span>
              <i data-lucide="clock" class="icon-xs me-1"></i>
              {{ $blog->reading_time }} min read
            </span>
            @endif
            @if($blog->views_count > 0)
            <span>
              <i data-lucide="eye" class="icon-xs me-1"></i>
              {{ number_format($blog->views_count) }} views
            </span>
            @endif
          </small>
        </div>

        <!-- Blog Excerpt -->
        @if($blog->excerpt)
        <p class="lead text-muted mb-4">{{ $blog->excerpt }}</p>
        @endif

        <div class="d-flex flex-wrap gap-3">
          <a href="#blog-content" class="btn btn-primary btn-sm">
            <i data-lucide="book-open" class="icon-sm me-2"></i>
            Read Article
          </a>
          <a href="{{ route('front.blogs.index') }}" class="btn btn-outline-primary btn-sm">
            <i data-lucide="arrow-left" class="icon-sm me-2"></i>
            Back to Blog
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

@if($blog->hasFeaturedImage())
<!-- Featured Image Section -->
<section class="">
  <div class="container px-3 pt-3 px-md-5 pt-md-5">
    <div class="row">
      <div class="col-lg-12 mx-auto">
        <div class="position-relative overflow-hidden rounded-3 shadow-sm text-center">
          @if($blog->hasFeaturedImage())
          <img src="{{ $blog->featured_image_url }}"
            alt="{{ $blog->title }}"
            class="img-fluid w-100"
            style="max-height: 400px; object-fit: cover;"
            onerror="this.src='<?= asset('images/blog-placeholder.svg') ?>'">
          @else
          <!-- <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
            <div class="text-center text-muted">
              <i data-lucide="image" style="width: 64px; height: 64px;" class="mb-3 opacity-50"></i>
              <h5 class="mb-2 text-dark">{{ $blog->title }}</h5>
              <small class="text-muted">Blog Post</small>
            </div>
          </div> -->
          @endif
        </div>
      </div>
    </div>
  </div>
</section>
@endif

<!-- Blog Content -->
<section id="blog-content" class="">
  <div class="container px-3 pt-3 px-md-5 pt-md-5">
    <div class="cms-content">
      @if($blog->content)
      {!! $blog->content !!}
      @else
      <p class="text-muted">No content available for this blog post.</p>
      @endif
    </div>
  </div>
</section>
<!-- Author Section -->
@if($blog->author)
<!-- <section>
  <div class="container px-3 py-3 px-md-5 py-md-4">
    <div class="row">
      <div class="col-lg-12 mx-auto">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4 p-md-3">
            <div class="d-flex align-items-center">
              <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                <i data-lucide="user" style="width: 24px; height: 24px;"></i>
              </div>
              <div>
                <h6 class="fw-bold mb-1">{{ $blog->author->name }}</h6>
                <p class="text-muted small mb-0">
                  Author • Published {{ $blog->published_at->format('M d, Y') }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section> -->
@endif



<!-- Share Section -->
<section class="bg-light mt-5">
  <div class="container px-3 py-4 px-md-5 py-md-4">
    <div class="row">
      <div class="col-lg-12 mx-auto">
        <div class="text-center">
          <h5 class="fw-bold mb-3">Share this article</h5>
          <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($blog->title) }}"
              target="_blank"
              class="btn btn-outline-primary btn-sm">
              <i data-lucide="twitter" class="icon-sm me-1"></i>
              Twitter
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
              target="_blank"
              class="btn btn-outline-primary btn-sm">
              <i data-lucide="facebook" class="icon-sm me-1"></i>
              Facebook
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}"
              target="_blank"
              class="btn btn-outline-primary btn-sm">
              <i data-lucide="linkedin" class="icon-sm me-1"></i>
              LinkedIn
            </a>
            <button type="button"
              class="btn btn-outline-primary btn-sm"
              onclick="copyToClipboard('{{ request()->url() }}')">
              <i data-lucide="link" class="icon-sm me-1"></i>
              Copy Link
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Related Posts -->


@if($relatedBlogs->count() > 0)
<section>
  <div class="container px-3 pt-4 px-md-5 pt-md-5">
    <div class="row">
      <div class="col-12">
        <h3 class="text-center mb-3">Related Articles</h3>
      </div>
    </div>
    <div class="row">
      @foreach($relatedBlogs as $relatedPost)
      <div class="col-lg-4 col-md-6 mb-4">
        <div class="card feature-card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-body" style="transition: all 0.3s ease;">
          <div class="card-img-top position-relative overflow-hidden" style="height: 200px;">
            @if($relatedPost->hasFeaturedImage())
            <img src="{{ $relatedPost->featured_image_url }}"
              alt="{{ $relatedPost->title }}"
              class="img-fluid w-100 h-100 object-fit-cover"
              onerror="this.src='<?= asset('images/blog-placeholder.svg') ?>'">
            @else
            <div class="bg-light d-flex align-items-center justify-content-center h-100">
              <div class="text-center text-muted">
                <i data-lucide="image" style="width: 24px; height: 24px;" class="mb-1 opacity-50"></i>
                <!-- <small class="d-block">Related Post</small> -->
              </div>
            </div>
            @endif
          </div>

          <div class="card-body d-flex flex-column">
            <h6 class="card-title mb-2">
              <a href="{{ route('front.blogs.show', $relatedPost->slug) }}">
                {{ Str::limit($relatedPost->title, 60) }}
              </a>
            </h6>

            <small class="text-muted mb-2">
              <i data-lucide="calendar" class="icon-xs me-1"></i>
              {{ $relatedPost->published_at->format('M d, Y') }}
            </small>

            @if($relatedPost->excerpt)
            <p class="card-text text-muted small flex-grow-1">{{ Str::limit($relatedPost->excerpt, 80) }}</p>
            @endif

            <div class="mt-2">
              <a href="{{ route('front.blogs.show', $relatedPost->slug) }}" aria-label="{{$relatedPost->title}}" class="btn btn-primary btn-sm rounded-pill btn-hover-elevate">
                <i data-lucide="arrow-right" class="icon-sm me-2"></i>
                Read More
              </a>
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
  // Copy to clipboard functionality
  function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
      // Show success message
      const button = event.target.closest('button');
      const originalText = button.innerHTML;
      button.innerHTML = '<i data-lucide="check" class="icon-sm me-1"></i>Copied!';
      button.classList.remove('btn-outline-primary');
      button.classList.add('btn-success');

      setTimeout(function() {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-primary');
        // Re-initialize lucide icons
        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }
      }, 2000);
    }).catch(function(err) {
      console.error('Could not copy text: ', err);
      (window.Toast || Swal).fire({
        icon: 'error',
        title: 'Failed to copy link to clipboard'
      });
    });
  }

  // Smooth scrolling is now handled globally in master layout

  // Reading progress indicator
  window.addEventListener('scroll', function() {
    const article = document.querySelector('.blog-content');
    if (article) {
      const articleTop = article.offsetTop;
      const articleHeight = article.offsetHeight;
      const windowHeight = window.innerHeight;
      const scrollTop = window.pageYOffset;

      const progress = Math.min(100, Math.max(0,
        ((scrollTop - articleTop + windowHeight) / articleHeight) * 100
      ));

      // You can use this progress value to show a reading progress bar
      // For example: document.querySelector('.reading-progress').style.width = progress + '%';
    }
  });
</script>
@endpush