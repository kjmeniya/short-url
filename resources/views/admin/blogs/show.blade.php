@extends('admin.layout.master')

@section('title', $title ?? 'Blog Post Details')
@section('description', $description ?? "View details for {$blog->title} blog post.")
@section('keywords', $keywords ?? 'blog post details, article view, blog information')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">Blog Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($blog->title, 50) }}</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                    <h6 class="card-title mb-0">Blog Post Details</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i data-lucide="arrow-left" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Back to List</span>
                            <span class="d-sm-none">Back</span>
                        </a>
                        <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="btn btn-outline-primary btn-sm">
                            <i data-lucide="edit" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Edit Post</span>
                            <span class="d-sm-none">Edit</span>
                        </a>
                        @if($blog->status === 'published')
                        <a href="{{ route('admin.blogs.show', $blog->slug) }}" target="_blank" class="btn btn-outline-success btn-sm">
                            <i data-lucide="external-link" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">View Live</span>
                            <span class="d-sm-none">Live</span>
                        </a>
                        @endif
                        <button type="button" class="btn btn-outline-danger btn-sm delete-blog" data-id="{{ $blog->id }}">
                            <i data-lucide="trash-2" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Delete</span>
                            <span class="d-sm-none">Delete</span>
                        </button>
                    </div>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="row">
                    <div class="col-lg-8">
                        <!-- Post Content -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Post Content</h6>
                            </div>
                            <div class="card-body">
                                @if($blog->featured_image)
                                <div class="mb-4 text-center">
                                    <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}"
                                        class="img-fluid rounded" style="max-height: 300px;">
                                </div>
                                @endif

                                <h1 class="mb-3">{{ $blog->title }}</h1>

                                <div class="mb-4">
                                    <p class="lead text-muted">{{ $blog->excerpt }}</p>
                                </div>

                                <div class="blog-content">
                                    {!! $blog->content !!}
                                </div>
                            </div>
                        </div>

                        <!-- SEO Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">SEO Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Meta Title:</label>
                                            <p class="text-muted">{{ $blog->meta_title ?: 'Not set' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Meta Keywords:</label>
                                            <p class="text-muted">{{ $blog->meta_keywords ?: 'Not set' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Meta Description:</label>
                                    <p class="text-muted">{{ $blog->meta_description ?: 'Not set' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">URL Slug:</label>
                                    <p class="text-muted">{{ $blog->slug }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Post Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Post Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status:</label>
                                    <div>
                                        @if($blog->status === 'published')
                                        <span class="badge bg-success">Published</span>
                                        @elseif($blog->status === 'draft')
                                        <span class="badge bg-warning">Draft</span>
                                        @else
                                        <span class="badge bg-secondary">Archived</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Featured:</label>
                                    <div>
                                        @if($blog->is_featured)
                                        <span class="badge bg-primary">Featured</span>
                                        @else
                                        <span class="badge bg-light text-dark">Regular</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Author:</label>
                                    <p class="text-muted mb-0">{{ $blog->author->name ?? 'Unknown' }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created:</label>
                                    <p class="text-muted mb-0">{{ $blog->created_at->format('M d, Y g:i A') }}</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Last Updated:</label>
                                    <p class="text-muted mb-0">{{ $blog->updated_at->format('M d, Y g:i A') }}</p>
                                </div>

                                @if($blog->published_at)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Published:</label>
                                    <p class="text-muted mb-0">{{ $blog->published_at->format('M d, Y g:i A') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="border-end">
                                            <h4 class="mb-1 text-primary">{{ number_format($blog->views_count) }}</h4>
                                            <p class="text-muted mb-0">Views</p>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <h4 class="mb-1 text-info">{{ $blog->reading_time ?? 0 }}</h4>
                                        <p class="text-muted mb-0">Min Read</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    @if($blog->status !== 'published')
                                    <button type="button" class="btn btn-success btn-sm" onclick="changeStatus('published')">
                                        <i data-lucide="check-circle" class="icon-sm me-1"></i>
                                        Publish Post
                                    </button>
                                    @endif

                                    @if($blog->status !== 'draft')
                                    <button type="button" class="btn btn-warning btn-sm" onclick="changeStatus('draft')">
                                        <i data-lucide="edit" class="icon-sm me-1"></i>
                                        Move to Draft
                                    </button>
                                    @endif

                                    @if($blog->status !== 'archived')
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="changeStatus('archived')">
                                        <i data-lucide="archive" class="icon-sm me-1"></i>
                                        Archive Post
                                    </button>
                                    @endif

                                    @if(!$blog->is_featured)
                                    <button type="button" class="btn btn-primary btn-sm" onclick="toggleFeatured(true)">
                                        <i data-lucide="star" class="icon-sm me-1"></i>
                                        Mark as Featured
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleFeatured(false)">
                                        <i data-lucide="star-off" class="icon-sm me-1"></i>
                                        Remove Featured
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
    $(document).ready(function() {
        // Delete blog functionality
        $('.delete-blog').on('click', function() {
            const blogId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/blog/${blogId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success').then(() => {
                                    window.location.href = '{{ route("admin.blogs.index") }}';
                                });
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });
    });

    // Change post status
    function changeStatus(status) {
        const blogId = {
            {
                $blog - > id
            }
        };

        Swal.fire({
            title: 'Are you sure?',
            text: `Change post status to ${status}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, change it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.blogs.bulk-action") }}',
                    method: 'POST',
                    data: {
                        action: status === 'published' ? 'publish' : status,
                        ids: [blogId],
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            }
        });
    }

    // Toggle featured status
    function toggleFeatured(featured) {
        const blogId = {
            {
                $blog - > id
            }
        };
        const action = featured ? 'feature' : 'unfeature';

        Swal.fire({
            title: 'Are you sure?',
            text: featured ? 'Mark this post as featured?' : 'Remove featured status?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i data-lucide="star" class="icon-sm me-1"></i>Yes, do it!',
            cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
            customClass: {
                confirmButton: 'btn btn-sm btn-primary',
                cancelButton: 'btn btn-sm btn-secondary'
            },
            buttonsStyling: false,
            didOpen: () => {
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.blogs.bulk-action") }}',
                    method: 'POST',
                    data: {
                        action: action,
                        ids: [blogId],
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: '<i data-lucide="check" class="icon-sm me-1"></i>OK',
                                customClass: {
                                    confirmButton: 'btn btn-sm btn-success'
                                },
                                buttonsStyling: false,
                                didOpen: () => {
                                    if (typeof lucide !== 'undefined') {
                                        lucide.createIcons();
                                    }
                                }
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>OK',
                                customClass: {
                                    confirmButton: 'btn btn-sm btn-danger'
                                },
                                buttonsStyling: false,
                                didOpen: () => {
                                    if (typeof lucide !== 'undefined') {
                                        lucide.createIcons();
                                    }
                                }
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Something went wrong.',
                            icon: 'error',
                            confirmButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>OK',
                            customClass: {
                                confirmButton: 'btn btn-sm btn-danger'
                            },
                            buttonsStyling: false,
                            didOpen: () => {
                                if (typeof lucide !== 'undefined') {
                                    lucide.createIcons();
                                }
                            }
                        });
                    }
                });
            }
        });
    }
</script>
@endpush