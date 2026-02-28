{{--
  Password Field Component with Eye Toggle and Strength Meter
  
  Usage:
  @include('admin.partials.password-field', [
      'name' => 'password',
      'label' => 'Password',
      'placeholder' => 'Enter password',
      'value' => old('password'),
      'required' => true,
      'showStrengthMeter' => true,
      'autocomplete' => 'new-password'
  ])
--}}

@php
$fieldId = $name . '_' . uniqid();
$toggleId = $fieldId . '_toggle';
$strengthId = $fieldId . '_strength';
$showStrengthMeter = $showStrengthMeter ?? true;
$required = $required ?? false;
$autocomplete = $autocomplete ?? 'current-password';
$placeholder = $placeholder ?? 'Enter ' . strtolower($label ?? 'Password');

// Password requirements configuration
$requirements = $requirements ?? [
'length' => ['enabled' => true, 'min' => 8],
'uppercase' => ['enabled' => false],
'lowercase' => ['enabled' => false],
'number' => ['enabled' => false],
'special' => ['enabled' => false]
];

// Convert requirements to JSON for JavaScript
$requirementsJson = json_encode($requirements);
@endphp

<div class="mb-3">
    @if(isset($label))
    <label for="{{ $fieldId }}" class="form-label">
        {{ $label }}
        @if($required)
        <span class="text-danger">*</span>
        @endif
    </label>
    @endif

    <div class="password-field-wrapper position-relative">
        <input
            type="password"
            class="form-control password-field @error($name) is-invalid @enderror"
            id="{{ $fieldId }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            value="{{ $value ?? '' }}"
            autocomplete="{{ $autocomplete }}"
            @if($required) required @endif
            @if($showStrengthMeter) data-strength-target="{{ $strengthId }}" @endif
            @if($showStrengthMeter) data-requirements="{{ $requirementsJson }}" @endif>

        <!-- Password Toggle Eye Icon -->
        <span
            class="password-toggle-btn"
            id="{{ $toggleId }}"
            data-target="{{ $fieldId }}"
            tabindex="0"
            role="button"
            aria-label="Toggle password visibility"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            data-bs-title="Click to show/hide password">
            <i data-lucide="eye" class="icon-sm text-muted password-toggle-icon"></i>
        </span>
    </div>

    @if($showStrengthMeter)
    <!-- Password Strength Meter -->
    <div class="password-strength-meter mt-2" id="{{ $strengthId }}" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="text-muted">Password Strength:</small>
            <small class="strength-text text-muted">Weak</small>
        </div>
        <div class="progress" style="height: 4px;">
            <div class="progress-bar strength-bar" role="progressbar" style="width: 0%"></div>
        </div>
        <div class="strength-requirements mt-2" style="font-size: 0.75rem;">
            @if($requirements['length']['enabled'])
            <div class="requirement" data-requirement="length">
                <i class="requirement-icon text-muted" data-lucide="circle"></i>
                <span class="requirement-text text-muted">At least {{ $requirements['length']['min'] }} characters</span>
            </div>
            @endif

            @if($requirements['uppercase']['enabled'])
            <div class="requirement" data-requirement="uppercase">
                <i class="requirement-icon text-muted" data-lucide="circle"></i>
                <span class="requirement-text text-muted">One uppercase letter</span>
            </div>
            @endif

            @if($requirements['lowercase']['enabled'])
            <div class="requirement" data-requirement="lowercase">
                <i class="requirement-icon text-muted" data-lucide="circle"></i>
                <span class="requirement-text text-muted">One lowercase letter</span>
            </div>
            @endif

            @if($requirements['number']['enabled'])
            <div class="requirement" data-requirement="number">
                <i class="requirement-icon text-muted" data-lucide="circle"></i>
                <span class="requirement-text text-muted">One number</span>
            </div>
            @endif

            @if($requirements['special']['enabled'])
            <div class="requirement" data-requirement="special">
                <i class="requirement-icon text-muted" data-lucide="circle"></i>
                <span class="requirement-text text-muted">One special character</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    @error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
    @enderror
</div>

<style>
    .password-field-wrapper {
        position: relative;
    }

    .password-toggle-btn {
        position: absolute;
        right: 3px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        z-index: 10;
        padding: 4px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        transition: background-color 0.2s ease;
        background: transparent;
        border: none;
    }

    .password-toggle-btn:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }

    .password-toggle-btn:focus {
        outline: none;
        background-color: rgba(13, 110, 253, 0.1);
        box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
    }

    .password-toggle-btn:active {
        background-color: rgba(0, 0, 0, 0.1);
    }

    /* Ensure eye icon stays in position even with validation errors */
    .password-field-wrapper .form-control {
        padding-right: 2.5rem !important;
    }

    .password-field-wrapper .form-control.is-invalid {
        padding-right: 2.5rem !important;
        background-image: none !important;
        background-repeat: unset !important;
        background-position: unset !important;
        background-size: unset !important;
    }

    .password-field-wrapper .form-control.is-valid {
        padding-right: 2.5rem !important;
        background-image: none !important;
        background-repeat: unset !important;
        background-position: unset !important;
        background-size: unset !important;
    }

    /* Custom validation feedback positioning */
    .password-field-wrapper .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #dc3545;
    }

    .password-field-wrapper .valid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #198754;
    }

    .strength-bar {
        transition: all 0.3s ease;
    }

    .requirement {
        display: flex;
        align-items: center;
        margin-bottom: 2px;
    }

    .requirement-icon {
        width: 12px;
        height: 12px;
        margin-right: 6px;
        flex-shrink: 0;
    }

    .requirement.met .requirement-icon {
        color: #198754 !important;
    }

    .requirement.met .requirement-text {
        color: #198754 !important;
    }

    .password-strength-meter {
        transition: opacity 0.3s ease;
    }

    /* Fix for jQuery validation plugin */
    .password-field-wrapper label.error {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        color: #dc3545;
    }

    .password-field-wrapper input.error {
        border-color: #dc3545;
        padding-right: 2.5rem !important;
        background-image: none !important;
    }

    .password-field-wrapper input.valid {
        border-color: #198754;
        padding-right: 2.5rem !important;
        background-image: none !important;
    }
</style>