@extends('admin.layout.master')

@section('title', $groupName ?? 'Settings')
@section('description', $description ?? 'Manage ' . ($groupName ?? 'settings') . ' configuration options')
@section('keywords', $keywords ?? ($groupName ?? 'settings') . ', configuration, admin settings')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('build/plugins/cropperjs/cropper.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center">
  <div>
    <nav class="page-breadcrumb mb-0">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $groupName }}</li>
      </ol>
    </nav>
  </div>
</div>

<!-- Settings Groups Navigation -->
<div class="d-flex flex-wrap gap-2 mb-3">
  @foreach($settingGroups as $groupKey => $groupData)
  <a href="{{ route('admin.settings.group', $groupKey) }}"
    class="btn btn-sm {{ $selectedGroup === $groupKey ? 'btn-primary' : 'btn-outline-secondary' }}"
    data-bs-toggle="tooltip"
    title="{{ $groupData['name'] }}">
    <i data-lucide="{{ $groupData['icon'] }}" class="icon-sm"></i>
    <span class="d-none d-md-inline ms-1">{{ $groupData['name'] }}</span>
  </a>
  @endforeach
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-3 border-bottom gap-2">
          <div class="d-flex align-items-center">
            <h6 class="card-title mb-0">{{ $groupName }}</h6>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">
              <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back to All Settings
            </a>
            <button type="button" id="saveAllSettings" class="btn btn-primary btn-sm">
              <i data-lucide="save" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Save All</span>
              <span class="d-sm-none">Save</span>
            </button>
            <button type="button" class="btn btn-warning btn-sm reset-group-btn" data-group="{{ $selectedGroup }}">
              <i data-lucide="rotate-ccw" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Reset {{ $groupName }}</span>
              <span class="d-sm-none">Reset</span>
            </button>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#settingModal" id="addNewSettingBtn">
              <i data-lucide="plus" class="icon-sm me-1"></i>Add New Setting
            </button>
          </div>
        </div>

        <form id="settingsForm" action="{{ route('admin.settings.bulk-update') }}" method="POST">
          @csrf
          <input type="hidden" name="group" value="{{ $selectedGroup }}">

          <!-- Settings Content -->
          <div class="mt-3">
            @if(isset($groupedSettings[$selectedGroup]) && $groupedSettings[$selectedGroup]->count() > 0)
            <div class="row">
              @foreach($groupedSettings[$selectedGroup] as $setting)
              <div class="@if($setting->type === 'html')col-12 @else col-12 col-md-6 @endif mb-3">
                <label for="setting_{{ $setting->key }}" class="form-label">
                  {{ $setting->name }}
                  @if($setting->description)
                  <span class="ms-1 cursor-pointer" data-bs-toggle="tooltip" title="{{ $setting->description }}">
                    <i data-lucide="help-circle" class="icon-xs text-muted"></i>
                  </span>
                  @endif
                </label>

                @if($setting->type === 'text' || $setting->type === 'email' || $setting->type === 'url')
                <div class="input-group">
                  <input type="{{ $setting->type === 'email' ? 'email' : ($setting->type === 'url' ? 'url' : 'text') }}"
                    class="form-control form-control-sm"
                    id="setting_{{ $setting->key }}"
                    name="settings[{{ $setting->key }}]"
                    value="{{ $setting->value }}"
                    placeholder="{{ $setting->description }}">
                  <button class="btn btn-outline-secondary btn-sm edit-setting-btn" type="button"
                    data-setting-id="{{ $setting->id }}" title="Edit Setting">
                    <i data-lucide="edit-2" class="icon-xs"></i>
                  </button>
                </div>

                @elseif($setting->type === 'password')
                <div class="input-group">
                  <input type="password"
                    class="form-control form-control-sm"
                    id="setting_{{ $setting->key }}"
                    name="settings[{{ $setting->key }}]"
                    value="{{ $setting->value }}"
                    placeholder="{{ $setting->description }}">
                  <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePassword('setting_{{ $setting->key }}')">
                    <i data-lucide="eye" class="icon-xs"></i>
                  </button>
                  <button class="btn btn-outline-secondary btn-sm edit-setting-btn" type="button"
                    data-setting-id="{{ $setting->id }}" title="Edit Setting">
                    <i data-lucide="edit-2" class="icon-xs"></i>
                  </button>
                </div>

                @elseif($setting->type === 'color')
                <div class="input-group">
                  <input type="color"
                    class="form-control form-control-color form-control-sm"
                    id="setting_{{ $setting->key }}"
                    name="settings[{{ $setting->key }}]"
                    value="{{ $setting->value }}"
                    title="{{ $setting->description }}">
                  <input type="text"
                    class="form-control form-control-sm"
                    id="setting_{{ $setting->key }}_text"
                    value="{{ $setting->value }}"
                    placeholder="#000000"
                    readonly>
                  <button class="btn btn-outline-secondary btn-sm edit-setting-btn" type="button"
                    data-setting-id="{{ $setting->id }}" title="Edit Setting">
                    <i data-lucide="edit-2" class="icon-xs"></i>
                  </button>
                </div>

                @elseif($setting->type === 'textarea')
                <div class="input-group">
                  <textarea class="form-control form-control-sm"
                    id="setting_{{ $setting->key }}"
                    name="settings[{{ $setting->key }}]"
                    rows="3"
                    placeholder="{{ $setting->description }}">{{ $setting->value }}</textarea>
                  <button class="btn btn-outline-secondary btn-sm edit-setting-btn" type="button"
                    data-setting-id="{{ $setting->id }}" title="Edit Setting">
                    <i data-lucide="edit-2" class="icon-xs"></i>
                  </button>
                </div>

                @elseif($setting->type === 'html')
                <div>
                  <textarea class="form-control tinymce-editor"
                    id="setting_{{ $setting->key }}"
                    name="settings[{{ $setting->key }}]"
                    rows="6"
                    placeholder="{{ $setting->description }}">{{ $setting->value }}</textarea>
                  <button class="btn btn-outline-secondary btn-sm mt-2 edit-setting-btn" type="button"
                    data-setting-id="{{ $setting->id }}" title="Edit Setting">
                    <i data-lucide="edit-2" class="icon-xs"></i>
                  </button>
                </div>

                @elseif($setting->type === 'number')
                <div class="input-group">
                  <input type="number"
                    class="form-control form-control-sm"
                    id="setting_{{ $setting->key }}"
                    name="settings[{{ $setting->key }}]"
                    value="{{ $setting->value }}"
                    placeholder="{{ $setting->description }}">
                  <button class="btn btn-outline-secondary btn-sm edit-setting-btn" type="button"
                    data-setting-id="{{ $setting->id }}" title="Edit Setting">
                    <i data-lucide="edit-2" class="icon-xs"></i>
                  </button>
                </div>

                @elseif($setting->type === 'boolean')
                <div class="d-flex align-items-center">
                  <div class="form-check form-switch me-3">
                    <input class="form-check-input"
                      type="checkbox"
                      id="setting_{{ $setting->key }}"
                      name="settings[{{ $setting->key }}]"
                      value="1"
                      {{ $setting->value ? 'checked' : '' }}>
                    <label class="form-check-label" for="setting_{{ $setting->key }}">
                      {{ $setting->value ? 'Enabled' : 'Disabled' }}
                    </label>
                  </div>
                  <button class="btn btn-outline-secondary btn-sm edit-setting-btn" type="button"
                    data-setting-id="{{ $setting->id }}" title="Edit Setting">
                    <i data-lucide="edit-2" class="icon-xs"></i>
                  </button>
                </div>

                @elseif($setting->type === 'select')
                <div class="input-group">
                  <select class="form-select form-select-sm"
                    id="setting_{{ $setting->key }}"
                    name="settings[{{ $setting->key }}]">
                    @php
                    $options = null;

                    // Handle different option formats
                    if (is_array($setting->options)) {
                    $options = $setting->options;
                    } elseif (is_string($setting->options)) {
                    // Try to decode JSON
                    $decoded = json_decode($setting->options, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $options = $decoded;
                    } else {
                    // Try to decode double-encoded JSON
                    $doubleDecoded = json_decode(json_decode($setting->options, true), true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($doubleDecoded)) {
                    $options = $doubleDecoded;
                    }
                    }
                    }
                    @endphp

                    @if($options && is_array($options))
                    @foreach($options as $optionValue => $optionLabel)
                    <option value="{{ $optionValue }}" {{ $setting->value == $optionValue ? 'selected' : '' }}>
                      {{ $optionLabel }}
                    </option>
                    @endforeach
                    @else
                    <option value="">No valid options available</option>
                    @endif
                  </select>
                  <button class="btn btn-outline-secondary btn-sm edit-setting-btn" type="button"
                    data-setting-id="{{ $setting->id }}" title="Edit Setting">
                    <i data-lucide="edit-2" class="icon-xs"></i>
                  </button>
                </div>
                @if(!$options)
                <small class="text-warning">Note: This select field has invalid options configuration.</small>
                @endif

                @elseif($setting->type === 'file')
                @php
                $isLogo = strpos($setting->key, 'logo') !== false || $setting->key === 'site_favicon';
                $logoClass = strpos($setting->key, 'dark') !== false ? 'bg-dark' : 'bg-white';
                $logoClass .= ' object-fit-contain ';
                @endphp

                @if($isLogo)
                {{-- Logo/Image Upload with Cropper --}}
                @include('admin.partials.image-cropper', [
                'inputId' => $setting->key,
                'label' => $setting->name,
                'currentImage' => $setting->value ? (strpos($setting->value, 'build/') === 0 ? asset($setting->value) : asset('storage/' . $setting->value)) : null,
                'maxUploadSize' => 5,
                'logoClass' => $logoClass
                ])
                <div class="mt-2">
                  <button class="btn btn-outline-secondary btn-sm edit-setting-btn" type="button"
                    data-setting-id="{{ $setting->id }}" title="Edit Setting">
                    <i data-lucide="edit-2" class="icon-xs"></i>
                  </button>
                </div>
                @else
                {{-- Regular File Upload --}}
                <div class="input-group">
                  <input type="file"
                    class="form-control form-control-sm"
                    id="setting_{{ $setting->key }}"
                    name="settings[{{ $setting->key }}]">
                  <button class="btn btn-outline-secondary btn-sm edit-setting-btn" type="button"
                    data-setting-id="{{ $setting->id }}" title="Edit Setting">
                    <i data-lucide="edit-2" class="icon-xs"></i>
                  </button>
                </div>
                @if($setting->value)
                <small class="text-muted">Current: {{ $setting->value }}</small>
                @endif
                @endif
                @endif

              </div>
              @endforeach
            </div>

            {{-- Add test SMTP button for email group --}}
            @if($selectedGroup === 'smtp')
            <div class="row mt-3">
              <div class="col-12">
                <div class="card border-primary">
                  <div class="card-body">
                    <h6 class="card-title text-primary">
                      <i data-lucide="mail" class="icon-sm me-2"></i>Test Email & SMTP Configuration
                    </h6>
                    <p class="text-muted small mb-3">Test your email and SMTP configuration to ensure emails can be sent successfully.</p>
                    <div class="row g-2">
                      <div class="col-md-6">
                        <button type="button" class="btn btn-primary btn-sm w-100" id="testSmtpBtn">
                          <i data-lucide="send" class="icon-sm me-1"></i>Test SMTP Connection
                        </button>
                      </div>
                      <div class="col-md-6">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" id="sendTestEmailBtn">
                          <i data-lucide="mail" class="icon-sm me-1"></i>Send Test Email
                        </button>
                      </div>
                    </div>
                    <div id="smtpTestResult" class="mt-3" style="display: none;"></div>
                  </div>
                </div>
              </div>
            </div>
            @endif

            {{-- Add database download section for system group --}}
            @if($selectedGroup === 'system')
            <div class="row mt-3">
              <div class="col-12">
                <div class="card border-success">
                  <div class="card-body">
                    <h6 class="card-title text-success">
                      <i data-lucide="database" class="icon-sm me-2"></i>Database Backup & Download
                    </h6>
                    <p class="text-muted small mb-3">Download a complete backup of your database. This requires password confirmation for security.</p>

                    <div class="row g-2">
                      <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-sm w-100" id="downloadDatabaseSqlBtn">
                          <i data-lucide="download" class="icon-sm me-1"></i>Download as SQL
                        </button>
                      </div>
                      <div class="col-md-6">
                        <button type="button" class="btn btn-outline-success btn-sm w-100" id="downloadDatabaseCsvBtn">
                          <i data-lucide="file-text" class="icon-sm me-1"></i>Download as CSV (ZIP)
                        </button>
                      </div>
                    </div>

                    <div id="databaseDownloadResult" class="mt-3" style="display: none;"></div>
                  </div>
                </div>
              </div>
            </div>
            @endif

            @else
            <div class="text-center py-4">
              <i data-lucide="settings" class="icon-lg text-muted mb-2"></i>
              <p class="text-muted mb-2">No settings found in this group.</p>
              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#settingModal">
                <i data-lucide="plus" class="icon-sm me-1"></i>Add First Setting
              </button>
            </div>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>
</div>



<!-- Setting Modal (Create/Edit) -->
<div class="modal fade" id="settingModal" tabindex="-1" aria-labelledby="settingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="settingModalLabel">
          <i data-lucide="plus" class="icon-sm me-2"></i>Add New Setting
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="settingModalForm" method="POST" action="{{ route('admin.settings.store') }}">
        @csrf
        <input type="hidden" name="_method" id="modal_method" value="POST">
        <input type="hidden" name="setting_id" id="modal_setting_id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="modal_name" class="form-label">Setting Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="modal_name" name="name" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="modal_key" class="form-label">Setting Key <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="modal_key" name="key" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="modal_type" class="form-label">Type <span class="text-danger">*</span></label>
              <select class="form-select" id="modal_type" name="type" required>
                <option value="">Select Type</option>
                <option value="text">Text</option>
                <option value="textarea">Textarea</option>
                <option value="html">HTML</option>
                <option value="number">Number</option>
                <option value="boolean">Boolean</option>
                <option value="select">Select</option>
                <option value="email">Email</option>
                <option value="url">URL</option>
                <option value="color">Color</option>
                <option value="password">Password</option>
                <option value="file">File</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label for="modal_group" class="form-label">Group <span class="text-danger">*</span></label>
              <select class="form-select" id="modal_group" name="group" required>
                <option value="">Select Group</option>
                @foreach($settingGroups as $groupKey => $groupData)
                <option value="{{ $groupKey }}" {{ $selectedGroup == $groupKey ? 'selected' : '' }}>{{ $groupData['name'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-12 mb-3">
              <label for="modal_description" class="form-label">Description</label>
              <textarea class="form-control" id="modal_description" name="description" rows="2"></textarea>
            </div>
            <div class="col-md-6 mb-3">
              <label for="modal_sort_order" class="form-label">Sort Order</label>
              <input type="number" class="form-control" id="modal_sort_order" name="sort_order" value="10">
            </div>
            <div class="col-md-6 mb-3">
              <div class="form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" id="modal_is_public" name="is_public">
                <label class="form-check-label" for="modal_is_public">Public Setting</label>
              </div>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="modal_is_active" name="is_active" checked>
                <label class="form-check-label" for="modal_is_active">Active</label>
              </div>
            </div>
            <div class="col-12 mb-3" id="modal_options_container" style="display: none;">
              <label for="modal_options" class="form-label">Options (JSON format for select type)</label>
              <textarea class="form-control" id="modal_options" name="options" rows="3" placeholder='{"value1": "Label 1", "value2": "Label 2"}'></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
            <i data-lucide="x" class="icon-sm me-1"></i>Cancel
          </button>
          <button type="button" class="btn btn-danger btn-sm deleteSettingBtn" style="display: none;">
            <i data-lucide="trash-2" class="icon-sm me-1"></i>Delete
          </button>
          <button type="button" class="btn btn-warning btn-sm" id="resetToDefaultBtn" style="display: none;">
            <i data-lucide="rotate-ccw" class="icon-sm me-1"></i>Reset to Default
          </button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i data-lucide="save" class="icon-sm me-1"></i>Save Setting
          </button>
        </div>
      </form>
    </div>
  </div>
</div>



@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('build/plugins/cropperjs/cropper.min.js') }}"></script>
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/tinymce/tinymce.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
  $(document).ready(function() {

    // Initialize tooltips after Lucide icons are rendered
    function initTooltips() {
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.forEach(function(tooltipTriggerEl) {
        // Dispose existing tooltip if any to avoid duplicates
        var existingTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
        if (existingTooltip) {
          existingTooltip.dispose();
        }
        new bootstrap.Tooltip(tooltipTriggerEl);
      });
    }

    // Wait for Lucide icons to be rendered, then initialize tooltips
    setTimeout(initTooltips, 100);

    // Initialize TinyMCE for email body
    const bodyColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-body-color').trim();
    const tinymceOptions = {
      selector: '.tinymce-editor',
      min_height: 400,
      skin: 'oxide',
      plugins: [
        'advlist', 'autoresize', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'pagebreak',
        'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'code', 'fullscreen', 'table', 'emoticons'
      ],
      toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | code preview | forecolor backcolor emoticons',
      image_advtab: true,
      promotion: false,
      branding: false,
      license_key: 'gpl',
      content_style: `
            .content { padding: 30px; font-family: 'Roboto', Helvetica, sans-serif !important;}
            .button { display: inline-block; padding: 12px 24px; background: #245dac; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
        `,
      // setup: function(editor) {
      //   editor.on('change', function() {
      //     updatePreview();
      //   });
      // }
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

    // Color picker sync
    $(document).on('input', 'input[type="color"]', function() {
      const textInput = $(this).siblings('input[type="text"]');
      textInput.val($(this).val());
    });

    // Boolean switch label update
    $(document).on('change', 'input[type="checkbox"][id^="setting_"]', function() {
      const label = $(this).siblings('label');
      if ($(this).is(':checked')) {
        label.text('Enabled');
      } else {
        label.text('Disabled');
      }
    });

    // Save all settings with password confirmation
    $('#saveAllSettings').on('click', function() {
      let isProcessing = false;

      const showPasswordModal = () => {
        Swal.fire({
          title: 'Confirm Settings Update',
          html: `
            <div class="text-start">
              <p class="mb-3">Please enter your password to confirm these changes:</p>
              <div class="mb-3">
                <label for="swal-password" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" id="swal-password" class="form-control" placeholder="Enter your password">
                  <button class="btn btn-outline-secondary btn-sm" type="button" onclick="toggleSwalPassword()">
                    <i data-lucide="eye" class="icon-sm"></i>
                  </button>
                </div>
                <div id="password-error" class="text-danger mt-2" style="display: none;"></div>
              </div>
            </div>
          `,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: '<i data-lucide="save" class="icon-sm me-1"></i>Save Settings',
          cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
          confirmButtonColor: '#245dac',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showLoaderOnConfirm: true,
          customClass: {
            confirmButton: 'btn btn-primary btn-sm',
            cancelButton: 'btn btn-outline-secondary btn-sm'
          },
          didOpen: () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
          },
          preConfirm: () => {
            if (isProcessing) return false;

            const password = document.getElementById('swal-password').value;
            const errorDiv = document.getElementById('password-error');

            if (!password) {
              errorDiv.textContent = 'Password is required';
              errorDiv.style.display = 'block';
              return false;
            }

            errorDiv.style.display = 'none';
            isProcessing = true;

            // Verify password and save settings
            return $.ajax({
              url: '{{ route("admin.settings.verify-password") }}',
              method: 'POST',
              data: {
                password: password,
                _token: '{{ csrf_token() }}'
              }
            }).then(function(response) {
              if (response.success) {
                // Password verified, now save settings

                // Sync TinyMCE content to textareas before saving
                if (typeof tinymce !== 'undefined') {
                  tinymce.triggerSave();
                }

                const form = $('#settingsForm');
                const formData = new FormData(form[0]);

                // Handle unchecked boolean checkboxes - they need to be explicitly set to '0'
                form.find('input[type="checkbox"]').each(function() {
                  const checkbox = $(this);
                  const name = checkbox.attr('name');

                  if (name && name.startsWith('settings[')) {
                    if (!checkbox.is(':checked')) {
                      // Add unchecked checkbox with value '0'
                      formData.set(name, '0');
                    }
                  }
                });

                return $.ajax({
                  url: form.attr('action'),
                  method: 'POST',
                  data: formData,
                  processData: false,
                  contentType: false
                }).then(function(saveResponse) {
                  isProcessing = false;
                  if (saveResponse.success) {
                    return {
                      success: true,
                      message: saveResponse.message || 'Settings saved successfully.'
                    };
                  } else {
                    throw new Error(saveResponse.message || 'Failed to save settings');
                  }
                }).catch(function(xhr) {
                  isProcessing = false;
                  let message = 'An error occurred while saving settings.';
                  if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                  }
                  throw new Error(message);
                });
              } else {
                isProcessing = false;
                const errorDiv = document.getElementById('password-error');
                errorDiv.textContent = 'The password you entered is incorrect.';
                errorDiv.style.display = 'block';
                return false;
              }
            }).catch(function(xhr) {
              isProcessing = false;
              if (xhr.responseJSON && xhr.responseJSON.message && xhr.responseJSON.message.includes('password')) {
                const errorDiv = document.getElementById('password-error');
                errorDiv.textContent = 'The password you entered is incorrect.';
                errorDiv.style.display = 'block';
                return false;
              } else {
                throw new Error('Failed to verify password.');
              }
            });
          }
        }).then((result) => {
          if (result.isConfirmed && result.value && result.value.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success!',
              text: result.value.message,
              timer: 2000,
              showConfirmButton: false,
              confirmButtonColor: '#245dac',
              didOpen: () => {
                if (typeof lucide !== 'undefined') {
                  lucide.createIcons();
                }
              }
            }).then(() => {
              location.reload();
            });
          }
        }).catch((error) => {
          if (error && error.message) {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: error.message,
              confirmButtonColor: '#245dac',
              didOpen: () => {
                if (typeof lucide !== 'undefined') {
                  lucide.createIcons();
                }
              }
            }).then(() => {
              // Show password modal again on error
              setTimeout(() => showPasswordModal(), 100);
            });
          }
        });
      };

      showPasswordModal();
    });

    // Reset group settings with password confirmation
    $('.reset-group-btn').on('click', function(e) {
      e.preventDefault();
      const group = $(this).data('group');
      let isProcessing = false;

      const showResetPasswordModal = () => {
        Swal.fire({
          title: 'Reset {{ $groupName }}?',
          html: `
            <div class="text-start">
              <p class="mb-3">This will reset all settings in this group to their default values.</p>
              <p class="mb-3">Please enter your password to confirm:</p>
              <div class="mb-3">
                <label for="swal-reset-password" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" id="swal-reset-password" class="form-control" placeholder="Enter your password">
                  <button class="btn btn-outline-secondary btn-sm" type="button" onclick="toggleSwalResetPassword()">
                    <i data-lucide="eye" class="icon-sm"></i>
                  </button>
                </div>
                <div id="reset-password-error" class="text-danger mt-2" style="display: none;"></div>
              </div>
            </div>
          `,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: '<i data-lucide="list-restart" class="icon-sm me-1"></i>Yes, reset them!',
          cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
          allowOutsideClick: false,
          allowEscapeKey: false,
          showLoaderOnConfirm: true,
          customClass: {
            confirmButton: 'btn btn-outline-danger btn-sm',
            cancelButton: 'btn btn-outline-secondary btn-sm'
          },
          didOpen: () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();
          },
          preConfirm: () => {
            if (isProcessing) return false;

            const password = document.getElementById('swal-reset-password').value;
            const errorDiv = document.getElementById('reset-password-error');

            if (!password) {
              errorDiv.textContent = 'Password is required';
              errorDiv.style.display = 'block';
              return false;
            }

            errorDiv.style.display = 'none';
            isProcessing = true;

            // Verify password and reset settings
            return $.ajax({
              url: '{{ route("admin.settings.verify-password") }}',
              method: 'POST',
              data: {
                password: password,
                _token: '{{ csrf_token() }}'
              }
            }).then(function(response) {
              if (response.success) {
                // Password verified, now reset settings
                return $.ajax({
                  url: '{{ route("admin.settings.reset-defaults") }}',
                  method: 'POST',
                  data: {
                    _token: '{{ csrf_token() }}',
                    group: group
                  }
                }).then(function(resetResponse) {
                  isProcessing = false;
                  if (resetResponse.success) {
                    return {
                      success: true,
                      message: resetResponse.message || 'Settings reset successfully.'
                    };
                  } else {
                    throw new Error(resetResponse.message || 'Failed to reset settings');
                  }
                }).catch(function(xhr) {
                  isProcessing = false;
                  let message = 'Failed to reset settings.';
                  if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                  }
                  throw new Error(message);
                });
              } else {
                isProcessing = false;
                const errorDiv = document.getElementById('reset-password-error');
                errorDiv.textContent = 'The password you entered is incorrect.';
                errorDiv.style.display = 'block';
                return false;
              }
            }).catch(function(xhr) {
              isProcessing = false;
              if (xhr.responseJSON && xhr.responseJSON.message && xhr.responseJSON.message.includes('password')) {
                const errorDiv = document.getElementById('reset-password-error');
                errorDiv.textContent = 'The password you entered is incorrect.';
                errorDiv.style.display = 'block';
                return false;
              } else {
                throw new Error('Failed to verify password.');
              }
            });
          },
        }).then((result) => {
          if (result.isConfirmed && result.value && result.value.success) {
            Swal.fire({
              icon: 'success',
              title: 'Reset Complete!',
              text: result.value.message,
              timer: 2000,
              showConfirmButton: false,
              confirmButtonColor: '#245dac'
            }).then(() => {
              location.reload();
            });
          }
        }).catch((error) => {
          if (error && error.message) {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: error.message,
              confirmButtonColor: '#245dac'
            }).then(() => {
              // Show password modal again on error
              setTimeout(() => showResetPasswordModal(), 100);
            });
          }
        });
      };

      showResetPasswordModal();
    });

    // Test SMTP connection
    $('#testSmtpBtn').on('click', function() {
      const btn = $(this);
      const originalText = btn.html();
      const resultDiv = $('#smtpTestResult');

      btn.prop('disabled', true).html('<i data-lucide="loader" class="icon-sm me-1"></i>Testing...');
      resultDiv.hide();

      $.ajax({
        url: '{{ route("admin.settings.test-smtp") }}',
        method: 'POST',
        data: {
          _token: '{{ csrf_token() }}'
        },
        success: function(response) {
          btn.prop('disabled', false).html(originalText);

          if (response.success) {
            resultDiv.html('<div class="alert alert-success"><i data-lucide="check-circle" class="icon-sm me-2"></i>' + response.message + '</div>').show();
          } else {
            resultDiv.html('<div class="alert alert-danger"><i data-lucide="x-circle" class="icon-sm me-2"></i>' + response.message + '</div>').show();
          }

          // Re-initialize lucide icons
          if (typeof lucide !== 'undefined') {
            lucide.createIcons();
          }
        },
        error: function(xhr) {
          btn.prop('disabled', false).html(originalText);
          let message = 'SMTP test failed.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
          }
          resultDiv.html('<div class="alert alert-danger"><i data-lucide="x-circle" class="icon-sm me-2"></i>' + message + '</div>').show();

          // Re-initialize lucide icons
          if (typeof lucide !== 'undefined') {
            lucide.createIcons();
          }
        }
      });
    });

    // Send test email
    $('#sendTestEmailBtn').on('click', function() {
      const btn = $(this);
      const originalText = btn.html();
      const resultDiv = $('#smtpTestResult');

      // Show email input modal
      Swal.fire({
        title: 'Send Test Email',
        html: `
          <div class="text-start">
            <div class="mb-3">
              <label for="test-email-address" class="form-label">Email Address:</label>
              <input type="email" class="form-control" id="test-email-address" placeholder="Enter email address" required>
            </div>
            <div class="mb-3">
              <label for="test-email-subject" class="form-label">Subject:</label>
              <input type="text" class="form-control" id="test-email-subject" value="Test Email from {{ site_name() }}" required>
            </div>
            <div class="mb-3">
              <label for="test-email-message" class="form-label">Message:</label>
              <textarea class="form-control" id="test-email-message" rows="3" placeholder="Enter test message">This is a test email to verify your email configuration is working correctly.</textarea>
            </div>
          </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i data-lucide="send" class="icon-sm me-1"></i>Send Test Email',
        cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
        allowOutsideClick: false,
        customClass: {
          confirmButton: 'btn btn-outline-primary btn-sm',
          cancelButton: 'btn btn-outline-secondary btn-sm'
        },
        didOpen: () => {
          if (typeof lucide !== 'undefined') lucide.createIcons();
        },
        preConfirm: () => {
          const email = document.getElementById('test-email-address').value;
          const subject = document.getElementById('test-email-subject').value;
          const message = document.getElementById('test-email-message').value;

          if (!email) {
            Swal.showValidationMessage('Email address is required');
            return false;
          }

          if (!subject) {
            Swal.showValidationMessage('Subject is required');
            return false;
          }

          return {
            email,
            subject,
            message
          };
        }
      }).then((result) => {
        if (result.isConfirmed) {
          btn.prop('disabled', true).html('<i data-lucide="loader" class="icon-sm me-1"></i>Sending...');
          resultDiv.hide();

          $.ajax({
            url: '{{ route("admin.settings.send-test-email") }}',
            method: 'POST',
            data: {
              _token: '{{ csrf_token() }}',
              email: result.value.email,
              subject: result.value.subject,
              message: result.value.message
            },
            success: function(response) {
              btn.prop('disabled', false).html(originalText);

              if (response.success) {
                resultDiv.html('<div class="alert alert-success"><i data-lucide="check-circle" class="icon-sm me-2"></i>' + response.message + '</div>').show();
                Swal.fire({
                  icon: 'success',
                  title: 'Email Sent!',
                  text: response.message,
                  confirmButtonColor: '#17a2b8'
                });
              } else {
                resultDiv.html('<div class="alert alert-danger"><i data-lucide="x-circle" class="icon-sm me-2"></i>' + response.message + '</div>').show();
                Swal.fire({
                  icon: 'error',
                  title: 'Email Failed!',
                  text: response.message,
                  confirmButtonColor: '#17a2b8'
                });
              }

              // Re-initialize lucide icons
              if (typeof lucide !== 'undefined') {
                lucide.createIcons();
              }
            },
            error: function(xhr) {
              btn.prop('disabled', false).html(originalText);
              let message = 'Failed to send test email.';
              if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
              }
              resultDiv.html('<div class="alert alert-danger"><i data-lucide="x-circle" class="icon-sm me-2"></i>' + message + '</div>').show();

              Swal.fire({
                icon: 'error',
                title: 'Email Failed!',
                text: message,
                confirmButtonColor: '#17a2b8'
              });

              // Re-initialize lucide icons
              if (typeof lucide !== 'undefined') {
                lucide.createIcons();
              }
            }
          });
        }
      });
    });

    // Setting modal functionality
    let currentSettingId = null;
    let modalMode = 'create';

    // Add new setting button
    $('#addNewSettingBtn').on('click', function() {
      openSettingModal('create');
    });

    // Edit setting buttons
    $(document).on('click', '.edit-setting-btn', function() {
      const settingId = $(this).data('setting-id');
      currentSettingId = settingId;
      loadSettingData(settingId, 'edit');
    });

    function openSettingModal(mode, settingId = null) {
      modalMode = mode;
      currentSettingId = settingId;

      // Reset form
      const modalForm = $('#settingModalForm')[0];
      if (modalForm) {
        modalForm.reset();
      }

      // Clear validation errors
      $('.is-invalid').removeClass('is-invalid');
      $('.invalid-feedback').remove();

      // Update modal title and show appropriate sections
      if (mode === 'create') {
        $('#settingModalLabel').text('Add New Setting');
        $('.deleteSettingBtn').hide();
        $('#resetToDefaultBtn').hide();
        $('#settingModalForm').attr('action', '{{ route("admin.settings.store") }}');
        $('#modal_method').val('POST');
        $('#modal_group').val('{{ $selectedGroup }}'); // Pre-select current group
      } else if (mode === 'edit') {
        $('#settingModalLabel').text('Edit Setting');
        $('.deleteSettingBtn').show();
        $('#resetToDefaultBtn').show();
        if (settingId) {
          $('#settingModalForm').attr('action', `/admin/settings/setting/${settingId}`);
          $('#modal_method').val('PUT');
          $('#modal_setting_id').val(settingId);
        }
      }

      // Show modal
      $('#settingModal').modal('show');
    }

    function loadSettingData(settingId, mode) {
      $.ajax({
        url: `/admin/settings/setting/${settingId}`,
        method: 'GET',
        success: function(data) {
          if (mode === 'edit') {
            openSettingModal(mode, settingId);

            // Populate edit mode
            $('#modal_name').val(data.name);
            $('#modal_key').val(data.key);
            $('#modal_type').val(data.type);
            $('#modal_group').val(data.group);
            $('#modal_description').val(data.description);
            $('#modal_sort_order').val(data.sort_order || 10);
            $('#modal_is_public').prop('checked', data.is_public == 1 || data.is_public === true);
            $('#modal_is_active').prop('checked', data.is_active == 1 || data.is_active === true);

            if (data.options) {
              $('#modal_options').val(JSON.stringify(data.options, null, 2));
              $('#modal_options_container').show();
            } else {
              $('#modal_options_container').hide();
            }

            // Handle type change
            handleTypeChange(data.type);
          }
        },
        error: function(xhr) {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Failed to load setting data.'
          });
        }
      });
    }

    // Handle type change in modal
    $('#modal_type').on('change', function() {
      handleTypeChange($(this).val());
    });

    function handleTypeChange(type) {
      if (type === 'select') {
        $('#modal_options_container').show();
      } else {
        $('#modal_options_container').hide();
      }
    }

    // Setting form submission
    $('#settingModalForm').on('submit', function(e) {
      e.preventDefault();

      const form = $(this);
      const formData = new FormData(form[0]);

      // Handle options JSON
      const optionsText = $('#modal_options').val();
      if (optionsText) {
        try {
          const options = JSON.parse(optionsText);
          formData.set('options', JSON.stringify(options));
        } catch (error) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid JSON',
            text: 'Please check the options JSON format.',
            confirmButtonColor: '#245dac'
          });
          return;
        }
      }

      // Fix is_active field - convert checkbox to boolean string
      const isActiveCheckbox = $('#modal_is_active');
      if (isActiveCheckbox.length) {
        formData.set('is_active', isActiveCheckbox.is(':checked') ? 'true' : 'false');
      }

      $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          if (response.success) {
            $('#settingModal').modal('hide');
            Swal.fire({
              icon: 'success',
              title: 'Success!',
              text: response.message,
              timer: 2000,
              showConfirmButton: false,
              confirmButtonColor: '#245dac'
            }).then(() => {
              location.reload();
            });
          }
        },
        error: function(xhr) {
          if (xhr.status === 422) {
            // Validation errors
            const errors = xhr.responseJSON.errors;
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            Object.keys(errors).forEach(function(field) {
              const input = $(`#modal_${field}`);
              input.addClass('is-invalid');
              input.after(`<div class="invalid-feedback">${errors[field][0]}</div>`);
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error!',
              text: xhr.responseJSON?.message || 'Failed to save setting.',
              confirmButtonColor: '#245dac'
            });
          }
        }
      });
    });

    // Delete setting
    $('.deleteSettingBtn').on('click', function() {
      if (!currentSettingId) return;

      Swal.fire({
        title: 'Delete Setting?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i data-lucide="trash-2" class="icon-sm me-1"></i>Yes, delete it!',
        customClass: {
          confirmButton: 'btn btn-outline-danger btn-sm',
          cancelButton: 'btn btn-outline-secondary btn-sm'
        },
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: `/admin/settings/setting/${currentSettingId}`,
            method: 'DELETE',
            data: {
              _token: '{{ csrf_token() }}'
            },
            success: function(response) {
              if (response.success) {
                $('#settingModal').modal('hide');
                Swal.fire({
                  icon: 'success',
                  title: 'Deleted!',
                  text: response.message,
                  timer: 2000,
                  showConfirmButton: false
                }).then(() => {
                  location.reload();
                });
              }
            },
            error: function(xhr) {
              Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to delete setting.'
              });
            }
          });
        }
      });
    });

    // Database download functionality
    $('#downloadDatabaseSqlBtn, #downloadDatabaseCsvBtn').on('click', function(e) {
      e.preventDefault();
      const format = $(this).attr('id').includes('Sql') ? 'sql' : 'csv';
      let isProcessing = false;

      const showPasswordModal = () => {
        Swal.fire({
          title: 'Download Database Backup',
          html: `
            <div class="text-start">
              <div class="d-flex align-items-center mb-3 p-3 bg-light rounded">
                <i data-lucide="database" class="icon-md text-success me-3"></i>
                <div>
                  <h6 class="text-success">Database Export - ${format.toUpperCase()} Format</h6>
                  <small class="text-muted">Complete backup of all tables and data</small>
                </div>
              </div>

              <div class="alert alert-success border-0 mb-3">
                <div class="d-flex align-items-start">
                  <i data-lucide="shield-check" class="icon-sm text-success me-2 mt-1"></i>
                  <div>
                    <strong>Security Notice:</strong><br>
                    <small>Password confirmation is required for database access.</small>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="swal-download-password" class="form-label fw-semibold">
                  <i data-lucide="lock" class="icon-sm me-1"></i>Enter your password:
                </label>
                <div class="input-group">
                  <span class="input-group-text">
                    <i data-lucide="key" class="icon-sm text-muted"></i>
                  </span>
                  <input type="password" class="form-control" id="swal-download-password" placeholder="Your current password" autocomplete="current-password">
                  <button class="btn btn-outline-secondary btn-sm" type="button" onclick="togglePassword('swal-download-password')" title="Toggle password visibility">
                    <i data-lucide="eye" class="icon-sm"></i>
                  </button>
                </div>
                <div id="passwordError" class="text-danger small mt-2" style="display: none;"></div>
                <small class="text-muted mt-1 d-block">This must be your current account password</small>
              </div>
            </div>
          `,
          icon: null,
          showCancelButton: true,
          confirmButtonColor: '#198754',
          cancelButtonColor: '#6c757d',
          confirmButtonText: `<i data-lucide="download" class="icon-sm me-1"></i>Download ${format.toUpperCase()}`,
          cancelButtonText: '<i data-lucide="x" class="icon-sm me-1"></i>Cancel',
          allowOutsideClick: false,
          customClass: {
            popup: 'swal2-popup-large',
            title: 'fw-bold mt-2',
            htmlContainer: 'text-start',
            confirmButton: 'btn-success btn-sm',
            cancelButton: 'btn-outline-secondary btn-sm'
          },
          didOpen: () => {
            // Re-initialize lucide icons
            if (typeof lucide !== 'undefined') {
              lucide.createIcons();
            }

            // Focus on password input
            setTimeout(() => {
              const passwordInput = document.getElementById('swal-download-password');
              if (passwordInput) {
                passwordInput.focus();
              }
            }, 100);
          },
          preConfirm: () => {
            if (isProcessing) return false;

            const password = document.getElementById('swal-download-password').value;
            if (!password) {
              $("#swal-download-password").addClass('is-invalid');
              $('#passwordError').text("Password is required").show();
              // Swal.showValidationMessage('Password is required');
              return false;
            }

            isProcessing = true;

            return $.ajax({
              url: '{{ route("admin.settings.download-database") }}',
              method: 'POST',
              data: {
                _token: '{{ csrf_token() }}',
                password: password,
                format: format
              },
              xhrFields: {
                responseType: 'blob'
              },
              success: function(data, status, xhr) {
                // Check if response is actually an error (JSON) instead of a file
                if (xhr.getResponseHeader('Content-Type') && xhr.getResponseHeader('Content-Type').includes('application/json')) {
                  // Convert blob back to text to read error message
                  const reader = new FileReader();
                  reader.onload = function() {
                    try {
                      const errorResponse = JSON.parse(reader.result);
                      Swal.showValidationMessage(errorResponse.message || 'An error occurred during download');
                    } catch (e) {
                      Swal.showValidationMessage('An unexpected error occurred');
                    }
                  };
                  reader.readAsText(data);
                  return false; // Prevent modal from closing
                }

                // Get filename from Content-Disposition header
                const disposition = xhr.getResponseHeader('Content-Disposition');
                let filename = `database_backup_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.${format}`;

                if (disposition && disposition.indexOf('filename=') !== -1) {
                  const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                  const matches = filenameRegex.exec(disposition);
                  if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                  }
                }

                // Create download link
                const url = window.URL.createObjectURL(data);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                return {
                  success: true,
                  message: 'Database backup downloaded successfully!'
                };
              },
              error: function(xhr) {
                let message = 'Failed to download database backup.';
                const contentType = xhr.getResponseHeader('Content-Type') || '';

                // Handle different types of error responses
                if (contentType.includes('application/json')) {
                  try {
                    const json = JSON.parse(xhr.responseText);
                    if (json.message) {
                      message = json.message;
                    } else if (json.errors) {
                      const firstKey = Object.keys(json.errors)[0];
                      if (firstKey && json.errors[firstKey].length > 0) {
                        message = json.errors[firstKey][0];
                      }
                    }
                  } catch (e) {
                    console.warn('JSON parse failed:', e);
                    message = xhr.statusText || message;
                  }
                } else {
                  // Handle non-JSON errors
                  message = xhr.statusText || message;
                }
                $("#swal-download-password").addClass('is-invalid');
                $('#passwordError').text(message).show();

                // Re-initialize lucide icons for the error message
                setTimeout(() => {
                  if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                  }
                }, 100);
                Swal.getConfirmButton().disabled = false;
                Swal.getCancelButton().disabled = false;
                return false; // Prevent modal from closing
              }
            }).always(() => {
              isProcessing = false;
            });
          }
        }).then((result) => {
          if (result.isConfirmed && result.value && result.value.success) {
            Swal.fire({
              icon: 'success',
              title: 'Download Complete!',
              text: result.value.message,
              timer: 3000,
              showConfirmButton: false,
              confirmButtonColor: '#198754',
              customClass: {
                popup: 'swal2-success-popup',
                title: 'text-success'
              }
            });
          }
          // Note: Errors are now handled within the modal via Swal.showValidationMessage
          // so we don't need to catch and show separate error modals
        });
      };

      showPasswordModal();
    });

    $(document).on('input keypress focus', '#swal-download-password', function() {
      $(this).removeClass('is-invalid');
      $('#passwordError').hide().text('');
    });

    // Password toggle function
    window.togglePassword = function(inputId) {
      const input = document.getElementById(inputId);
      const icon = input.nextElementSibling.querySelector('svg');

      if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
      } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
      }

      // Re-initialize lucide icons
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    };

    // Password toggle function for SweetAlert save modal
    window.toggleSwalPassword = function() {
      const input = document.getElementById('swal-password');
      const icon = input.nextElementSibling.querySelector('i');

      if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
      } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
      }

      // Re-initialize lucide icons
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    };

    // Password toggle function for SweetAlert reset modal
    window.toggleSwalResetPassword = function() {
      const input = document.getElementById('swal-reset-password');
      const icon = input.nextElementSibling.querySelector('i');

      if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-lucide', 'eye-off');
      } else {
        input.type = 'password';
        icon.setAttribute('data-lucide', 'eye');
      }

      // Re-initialize lucide icons
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    };
  });
</script>
@endpush

@push('custom-styles')
<style>
  /* Custom styles for database download modal */
  .swal2-popup-large {
    width: 32rem !important;
    max-width: 90vw !important;
  }

  .swal2-success-popup .swal2-icon.swal2-success {
    border-color: #198754 !important;
  }

  .swal2-success-popup .swal2-icon.swal2-success .swal2-success-ring {
    border-color: #198754 !important;
  }

  .swal2-success-popup .swal2-icon.swal2-success .swal2-success-fix {
    background-color: #198754 !important;
  }

  .swal2-validation-message {
    background: transparent !important;
    color: inherit !important;
    font-size: inherit !important;
    font-weight: inherit !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Database backup card styling */
  .card.border-success {
    border-color: #198754 !important;
  }

  .card.border-success .card-title.text-success {
    color: #198754 !important;
  }

  /* Button hover effects */
  .btn-success:hover {
    background-color: #157347 !important;
    border-color: #146c43 !important;
  }

  .btn-outline-success:hover {
    background-color: #198754 !important;
    border-color: #198754 !important;
    color: #fff !important;
  }
</style>
@endpush