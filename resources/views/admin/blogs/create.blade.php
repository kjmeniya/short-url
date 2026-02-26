@extends('admin.layout.master')

@section('title', $title ?? 'Create Blog Post')
@section('description', $description ?? 'Create new blog post or article for your website.')
@section('keywords', $keywords ?? 'create blog post, new article, blog creation')

@push('plugin-styles')
<link href="{{ asset('build/plugins/cropperjs/cropper.min.css') }}" rel="stylesheet" />
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.blogs.index') }}">Blog Management</a></li>
        <li class="breadcrumb-item active" aria-current="page">Create Post</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
                    <h6 class="card-title mb-0">Create New Blog Post</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.blogs.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i data-lucide="arrow-left" class="icon-sm me-1"></i>
                            <span class="d-none d-sm-inline">Back to List</span>
                            <span class="d-sm-none">Back</span>
                        </a>
                    </div>
                </div>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <form id="blogCreateForm" action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Basic Information -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                                            id="title" name="title" value="{{ old('title') }}"
                                            placeholder="Enter blog post title" required>
                                        @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                            id="slug" name="slug" value="{{ old('slug') }}"
                                            placeholder="Enter URL slug" required>
                                        @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">URL-friendly version of the title</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="excerpt" class="form-label">Excerpt <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('excerpt') is-invalid @enderror"
                                            id="excerpt" name="excerpt" rows="3"
                                            placeholder="Enter a brief description of the post" required>{{ old('excerpt') }}</textarea>
                                        @error('excerpt')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Maximum 500 characters</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Content</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('content') is-invalid @enderror"
                                            id="tinymceContent" name="content" rows="15">{{ old('content') }}</textarea>
                                        @error('content')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- SEO Settings -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">SEO Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label">Meta Title</label>
                                        <input type="text" class="form-control @error('meta_title') is-invalid @enderror"
                                            id="meta_title" name="meta_title" value="{{ old('meta_title') }}"
                                            placeholder="Enter meta title">
                                        @error('meta_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Maximum 255 characters</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label">Meta Description</label>
                                        <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                            id="meta_description" name="meta_description" rows="3"
                                            placeholder="Enter meta description">{{ old('meta_description') }}</textarea>
                                        @error('meta_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Maximum 500 characters</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                        <input type="text" class="form-control @error('meta_keywords') is-invalid @enderror"
                                            id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}"
                                            placeholder="Enter meta keywords (comma separated)">
                                        @error('meta_keywords')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Separate keywords with commas</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <!-- Publishing Options -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Publishing Options</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                                            <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                        </select>
                                        @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input @error('is_featured') is-invalid @enderror"
                                                type="checkbox" id="is_featured" name="is_featured" value="1"
                                                {{ old('is_featured') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">
                                                Featured Post
                                            </label>
                                        </div>
                                        @error('is_featured')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Mark this post as featured</small>
                                    </div>

                                    <div class="mb-3">
                                        <label for="published_at" class="form-label">Publish Date</label>
                                        <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror"
                                            id="published_at" name="published_at" value="{{ old('published_at') }}">
                                        @error('published_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Leave empty to publish immediately</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Featured Image -->
                            @include('admin.partials.image-cropper', [
                            'inputId' => 'featured_image',
                            'label' => 'Featured Image',
                            'currentImage' => null
                            ])

                            <!-- Actions -->
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-sm btn-primary" id="submitBtn">
                                            <i data-lucide="save" class="icon-sm me-1"></i>
                                            Create Post
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="saveDraftBtn">
                                            <i data-lucide="file-text" class="icon-sm me-1"></i>
                                            Save as Draft
                                        </button>
                                        <a href="{{ route('admin.blogs.index') }}" class="btn btn-sm btn-outline-danger">
                                            <i data-lucide="x" class="icon-sm me-1"></i>
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/cropperjs/cropper.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('build/plugins/tinymce/tinymce.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
    $(document).ready(function() {
        // Initialize TinyMCE
        function initTinyMCE() {
            // Check if TinyMCE is available and DOM is ready
            if (typeof tinymce === 'undefined' || document.readyState !== 'complete') {
                setTimeout(initTinyMCE, 200);
                return;
            }

            // Remove any existing TinyMCE instances
            if (tinymce.get('tinymceContent')) {
                tinymce.get('tinymceContent').remove();
            }

            const bodyColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-body-color').trim();

            tinymce.init({
                selector: '#tinymceContent',
                min_height: 400,
                plugins: [
                    'advlist', 'autoresize', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'pagebreak',
                    'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen', 'table', 'emoticons'
                ],
                toolbar: 'undo redo | formatselect | bold italic underline strikethrough | forecolor backcolor | ' +
                    'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ' +
                    'link image table | code preview fullscreen | removeformat help',
                content_style: `body { color: ${bodyColor}; font-family: Roboto, sans-serif; font-size: 14px; }`,
                promotion: false,
                branding: false,
                license_key: 'gpl',
                document_base_url: window.location.origin + '/',
                relative_urls: false,
                remove_script_host: false,
                convert_urls: true,
                setup: function(editor) {
                    editor.on('change keyup', function() {
                        editor.save();
                    });

                    editor.on('init', function() {
                        console.log('TinyMCE initialized successfully');
                    });

                    editor.on('LoadContent', function() {
                        editor.save();
                    });
                }
            }).catch(function(error) {
                console.error('TinyMCE initialization failed:', error);
            });
        }

        // Wait for window load event to ensure everything is ready
        if (document.readyState === 'complete') {
            setTimeout(initTinyMCE, 300);
        } else {
            $(window).on('load', function() {
                setTimeout(initTinyMCE, 300);
            });
        }

        // Auto-generate slug from title
        $('#title').on('input', function() {
            const title = $(this).val();
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            $('#slug').val(slug);
        });

        // Save as draft functionality
        $('#saveDraftBtn').on('click', function() {
            $('#status').val('draft');
            $('#blogCreateForm').submit();
        });

        // Form validation
        $('#blogCreateForm').on('submit', function(e) {
            // Update TinyMCE content to textarea before validation
            if (tinymce.get('tinymceContent')) {
                tinymce.get('tinymceContent').save();
            }

            const title = $('#title').val().trim();
            const slug = $('#slug').val().trim();
            const excerpt = $('#excerpt').val().trim();
            const content = tinymce.get('tinymceContent') ? tinymce.get('tinymceContent').getContent().trim() : '';
            const status = $('#status').val();

            if (!title || !slug || !excerpt || !content || !status) {
                e.preventDefault();
                Swal.fire({
                    title: 'Error',
                    text: 'Please fill in all required fields',
                    icon: 'error',
                    confirmButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>OK',
                    customClass: {
                        confirmButton: 'btn btn-sm btn-danger'
                    },
                    buttonsStyling: false
                });
                return false;
            }

            if (excerpt.length > 500) {
                e.preventDefault();
                Swal.fire({
                    title: 'Error',
                    text: 'Excerpt must not exceed 500 characters',
                    icon: 'error',
                    confirmButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>OK',
                    customClass: {
                        confirmButton: 'btn btn-sm btn-danger'
                    },
                    buttonsStyling: false
                });
                return false;
            }

            // Show loading state
            const submitBtn = $('#submitBtn');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>Creating...');

            // Re-enable button after 10 seconds as fallback
            setTimeout(() => {
                submitBtn.prop('disabled', false).html(originalText);
            }, 10000);
        });

        // Character count for excerpt
        $('#excerpt').on('input', function() {
            const length = $(this).val().length;
            const maxLength = 500;
            const remaining = maxLength - length;

            let countText = `${length}/${maxLength} characters`;
            if (remaining < 50) {
                countText = `<span class="text-warning">${countText}</span>`;
            }
            if (remaining < 0) {
                countText = `<span class="text-danger">${countText}</span>`;
            }

            $(this).siblings('.form-text').html(countText);
        });

        // Character count for meta fields
        $('#meta_title').on('input', function() {
            const length = $(this).val().length;
            const maxLength = 255;
            const remaining = maxLength - length;

            let countText = `${length}/${maxLength} characters`;
            if (remaining < 20) {
                countText = `<span class="text-warning">${countText}</span>`;
            }
            if (remaining < 0) {
                countText = `<span class="text-danger">${countText}</span>`;
            }

            $(this).siblings('.form-text').html(countText);
        });

        $('#meta_description').on('input', function() {
            const length = $(this).val().length;
            const maxLength = 500;
            const remaining = maxLength - length;

            let countText = `${length}/${maxLength} characters`;
            if (remaining < 50) {
                countText = `<span class="text-warning">${countText}</span>`;
            }
            if (remaining < 0) {
                countText = `<span class="text-danger">${countText}</span>`;
            }

            $(this).siblings('.form-text').html(countText);
        });

        // Ensure Lucide icons are loaded
        function ensureLucideIcons() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            } else {
                // Fallback: try to load lucide if not available
                setTimeout(ensureLucideIcons, 100);
            }
        }

        // Initial load
        ensureLucideIcons();
    });
</script>
@endpush