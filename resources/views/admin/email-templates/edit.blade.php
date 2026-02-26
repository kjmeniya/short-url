@extends('admin.layout.master')

@section('title', $title ?? 'Edit Email Template')
@section('description', $description ?? 'Edit email template content and settings')
@section('keywords', $keywords ?? 'edit email template, modify template, update template')

@push('plugin-styles')
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.email-templates.index') }}">Email Templates</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Template</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 col-xl-12 middle-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title">Edit Email Template: {{ $emailTemplate->name }}</h6>

            @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            @endif

            <form action="{{ route('admin.email-templates.update', $emailTemplate) }}" method="POST" class="forms-sample" id="emailTemplateEditForm">
              @csrf
              @method('PUT')

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $emailTemplate->name) }}">
                    <small class="form-text text-muted">A unique name to identify this template</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="type" class="form-label">Template Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="type" name="type">
                      <option value="">Select Type</option>
                      @foreach($types as $key => $value)
                      <option value="{{ $key }}" {{ old('type', $emailTemplate->type) == $key ? 'selected' : '' }}>{{ $value }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="subject" class="form-label">Email Subject <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="subject" name="subject" value="{{ old('subject', $emailTemplate->subject) }}">
                <small class="form-text text-muted">You can use variables like @{{name}}, @{{email}}, etc.</small>
              </div>

              <div class="mb-3">
                <label for="body" class="form-label">Email Body <span class="text-danger">*</span></label>
                <textarea class="form-control" id="tinymceEmailBody" name="body" rows="15">{!! old('body', htmlspecialchars($emailTemplate->body, ENT_QUOTES, 'UTF-8')) !!}</textarea>
                <small class="form-text text-muted">HTML content for the email body. Only include the content section. Header and footer are automatically added. Use variables like @{{name}}, @{{email}}, @{{reset_link}}, etc.</small>
                <div class="alert alert-info mt-2">
                  <i data-lucide="info" class="icon-sm me-2"></i>
                  <span>Use the predefined class <code>.content</code> for the main content area and <code>.button</code> for buttons. Header and footer with logo are automatically included. Click the <strong>code</strong> button to edit HTML directly.</span>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label for="variables" class="form-label">Available Variables</label>
                    <div id="variablesContainer">
                      @if(old('variables') || $emailTemplate->variables)
                      @foreach(old('variables', $emailTemplate->variables ?? []) as $index => $variable)
                      <div class="input-group mb-2">
                        <input type="text" class="form-control" name="variables[]" placeholder="Variable name" value="{{ $variable }}">
                        @if($index == 0)
                        <button type="button" class="btn btn-outline-secondary add-variable">
                          <i data-lucide="plus" class="icon-sm"></i>
                        </button>
                        @else
                        <button type="button" class="btn btn-outline-danger remove-variable">
                          <i data-lucide="minus" class="icon-sm"></i>
                        </button>
                        @endif
                      </div>
                      @endforeach
                      @else
                      <div class="input-group mb-2">
                        <input type="text" class="form-control" name="variables[]" placeholder="Variable name (e.g., name, email)">
                        <button type="button" class="btn btn-outline-secondary add-variable">
                          <i data-lucide="plus" class="icon-sm"></i>
                        </button>
                      </div>
                      @endif
                    </div>
                    <small class="form-text text-muted">Define variables that can be used in the template</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Status</label>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}>
                      <label class="form-check-label" for="is_active">
                        Active Template
                      </label>
                    </div>
                    <small class="form-text text-muted">Only active templates can be used for sending emails</small>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <div class="card">
                  <div class="card-header">
                    <h6 class="card-title mb-0">Template Preview</h6>
                  </div>
                  <div class="card-body">
                    <div id="templatePreview" class="border p-3 bg-light">
                      <p class="text-muted">Loading preview...</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.email-templates.index') }}" class="btn btn-secondary btn-sm">
                  <i data-lucide="x" class="icon-sm me-1"></i>
                  <span class="d-none d-sm-inline">Cancel</span>
                  <span class="d-sm-none">Cancel</span>
                </a>
                <button type="button" class="btn btn-primary btn-sm" id="submitBtn">
                  <i data-lucide="save" class="icon-sm me-1"></i>
                  <span class="d-none d-sm-inline">Update Template</span>
                  <span class="d-sm-none">Update</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
<script src="{{ asset('build/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>
<script src="{{ asset('build/plugins/tinymce/tinymce.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
  $(document).ready(function() {
    // Initialize Bootstrap MaxLength
    $('[data-maxlength="true"]').each(function() {
      $(this).maxlength({
        alwaysShow: true,
        threshold: 10,
        warningClass: "badge mt-1 bg-success",
        limitReachedClass: "badge mt-1 bg-danger",
        separator: ' of ',
        preText: 'You have ',
        postText: ' chars remaining.',
        validate: true
      });
    });

    // Initialize TinyMCE for email body
    const bodyColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-body-color').trim();
    const tinymceOptions = {
      selector: '#tinymceEmailBody',
      min_height: 400,
      skin: 'oxide',
      plugins: [
        'advlist', 'autoresize', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'pagebreak',
        'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen', 'table', 'emoticons'
      ],
      toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | code preview | forecolor backcolor emoticons',
      image_advtab: true,
      promotion: false,
      license_key: 'gpl',
      content_style: `
            .content { padding: 30px; font-family: 'Roboto', Helvetica, sans-serif !important;}
            .button { display: inline-block; padding: 12px 24px; background: #245dac; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        `,
      setup: function(editor) {
        editor.on('change', function() {
          updatePreview();
        });
      }
    };

    const theme = localStorage.getItem('theme');
    if (theme === 'dark') {
      tinymceOptions.content_css = 'dark';
      const bgColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-body-bg');
      tinymceOptions.content_style += ` body { background: ${bgColor}; }`;
    } else if (theme === 'light') {
      tinymceOptions.content_css = 'default';
    }

    tinymce.init(tinymceOptions);

    // Add variable functionality
    $(document).on('click', '.add-variable', function() {
      const container = $('#variablesContainer');
      const newInput = `
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="variables[]" placeholder="Variable name">
                <button type="button" class="btn btn-outline-danger remove-variable">
                    <i data-lucide="minus" class="icon-sm"></i>
                </button>
            </div>
        `;
      container.append(newInput);
      lucide.createIcons();
    });

    $(document).on('click', '.remove-variable', function() {
      $(this).closest('.input-group').remove();
    });

    // Preview functionality
    function updatePreview() {
      const subject = $('#subject').val();
      const body = tinymce.get('tinymceEmailBody') ? tinymce.get('tinymceEmailBody').getContent() : '';

      if (subject || body) {
        // Send AJAX request to render preview through custom template
        $.ajax({
          url: '{{ route("admin.email-templates.preview") }}',
          method: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            subject: subject,
            body: body
          },
          success: function(response) {
            let preview = '';
            if (subject) {
              preview += `<h5>Subject: ${subject}</h5><hr>`;
            }
            preview += response.html;
            $('#templatePreview').html(preview);
          },
          error: function() {
            $('#templatePreview').html('<p class="text-danger">Error loading preview</p>');
          }
        });
      } else {
        $('#templatePreview').html('<p class="text-muted">Enter content above to see preview</p>');
      }
    }

    // Initial preview update
    setTimeout(function() {
      if (tinymce.get('tinymceEmailBody')) {
        updatePreview();
      }
    }, 1000);

    $('#subject').on('input', updatePreview);

    // Initialize jQuery Validation
    $('#emailTemplateEditForm').validate({
      rules: {
        name: {
          required: true,
          minlength: 3,
          maxlength: 255
        },
        type: {
          required: true
        },
        subject: {
          required: true,
          minlength: 3,
          maxlength: 255
        },
        body: {
          required: function() {
            return tinymce.get('tinymceEmailBody') && tinymce.get('tinymceEmailBody').getContent().trim() === '';
          }
        }
      },
      messages: {
        name: {
          required: 'Please enter a template name',
          minlength: 'Template name must be at least 3 characters',
          maxlength: 'Template name cannot exceed 255 characters'
        },
        type: {
          required: 'Please select a template type'
        },
        subject: {
          required: 'Please enter an email subject',
          minlength: 'Subject must be at least 3 characters',
          maxlength: 'Subject cannot exceed 255 characters'
        },
        body: {
          required: 'Please enter email body content'
        }
      },
      errorElement: 'span',
      errorPlacement: function(error, element) {
        error.addClass('invalid-feedback');
        element.closest('.mb-3').append(error);
      },
      highlight: function(element, errorClass, validClass) {
        $(element).addClass('is-invalid').removeClass('is-valid');
      },
      unhighlight: function(element, errorClass, validClass) {
        $(element).removeClass('is-invalid').addClass('is-valid');
      },
      submitHandler: function(form) {
        // Sync TinyMCE content before submission
        if (tinymce.get('tinymceEmailBody')) {
          tinymce.get('tinymceEmailBody').save();
        }
        form.submit();
      }
    });

    // Form submission via button
    $('#submitBtn').on('click', function(e) {
      e.preventDefault();

      // Sync TinyMCE content before validation
      if (tinymce.get('tinymceEmailBody')) {
        const content = tinymce.get('tinymceEmailBody').getContent().trim();
        $('#tinymceEmailBody').val(content);
      }

      // Trigger form validation and submission
      $('#emailTemplateEditForm').submit();
    });
  });
</script>
@endpush