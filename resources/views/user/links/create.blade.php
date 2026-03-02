@extends('user.layout.master')

@section('title', 'Create New Link')

@section('content')

{{-- Breadcrumb --}}
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('user.links') }}">My Links</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create</li>
  </ol>
</nav>

<div class="row justify-content-center">
  <div class="col-lg-10 col-xl-9">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="card-header border-0 bg-body-tertiary py-4 px-4 d-flex justify-content-between align-items-center">
        <div>
          <p class="text-uppercase text-muted small fw-semibold mb-1" style="letter-spacing:.08em;">Link Builder</p>
          <h5 class="mb-0">
            <i data-lucide="plus-circle" class="icon-sm me-2 text-primary"></i>Create New Link
          </h5>
        </div>
        <a href="{{ route('user.links') }}" class="btn btn-outline-secondary btn-sm">
          <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back
        </a>
      </div>
      <div class="card-body p-4 p-lg-5">

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form id="create-link-form" action="{{ route('user.links.store') }}" method="POST" novalidate>
          @csrf

          <div class="section-card mb-5">
            <div class="section-heading d-flex align-items-center mb-3">
              <span class="badge rounded-pill bg-success-subtle text-success fw-semibold me-2">Free</span>
              <div>
                <h6 class="mb-0">Destination & Basics</h6>
                <p class="text-muted mb-0 small">Everything you need to get a new short link live.</p>
              </div>
            </div>

            {{-- Destination URL --}}
            <div class="mb-3">
              <label for="original_url" class="form-label">
                Destination URL <span class="text-danger">*</span>
              </label>
              <input type="url" id="original_url" name="original_url"
                class="form-control @error('original_url') is-invalid @enderror"
                value="{{ old('original_url') }}"
                placeholder="https://example.com/very-long-url"
                autofocus>
              @error('original_url')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Title --}}
            <div class="mb-0">
              <label for="title" class="form-label">
                Title <span class="text-muted">(optional)</span>
              </label>
              <input type="text" id="title" name="title"
                class="form-control @error('title') is-invalid @enderror"
                value="{{ old('title') }}"
                placeholder="Friendly name for this link"
                maxlength="255">
              @error('title')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <div class="section-card mb-5">
            <div class="section-heading d-flex align-items-center mb-3">
              <span class="badge rounded-pill bg-primary-subtle text-primary fw-semibold me-2">Basic</span>
              <div>
                <h6 class="mb-0">Link Controls</h6>
                <p class="text-muted small mb-0">Personalize URLs, schedule expirations, and manage limits.</p>
              </div>
            </div>

          {{-- Custom Slug --}}
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label for="custom_alias" class="form-label">
                Custom Slug <span class="text-muted">(optional)</span>
              </label>
              @if(!$features->get('custom_slug'))
              <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em;">
                <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
              </a>
              @endif
            </div>
            @if($features->get('custom_slug'))
            <div class="input-group">
              <span class="input-group-text text-muted" id="alias-addon">{{ url('/') }}/</span>
              <input type="text" id="custom_alias" name="custom_alias"
                class="form-control @error('custom_alias') is-invalid @enderror"
                value="{{ old('custom_alias') }}"
                placeholder="my-link"
                aria-describedby="alias-addon"
                autocomplete="off">
              <span class="input-group-text" id="slug-status-icon" style="min-width:38px;justify-content:center;"></span>
            </div>
            <div id="slug-feedback" class="form-text mt-1">
              Lowercase letters, numbers, hyphens, underscores A? 3??"64 chars A? Leave blank to auto-generate.
            </div>
            @error('custom_alias')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @else
            <div class="alert alert-light border text-muted small mb-0">
              Custom slugs are available on paid plans. <a href="{{ route('front.pricing') }}">Upgrade to unlock.</a>
            </div>
            @endif
          </div>

          {{-- Expiry Date --}}
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label for="expires_at" class="form-label">
                Expiry Date <span class="text-muted">(optional)</span>
              </label>
              @if(!$features->get('expiry_date'))
              <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em;">
                <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
              </a>
              @endif
            </div>
            @if($features->get('expiry_date'))
            <input type="datetime-local" id="expires_at" name="expires_at"
              class="form-control @error('expires_at') is-invalid @enderror"
              value="{{ old('expires_at') }}">
            <div class="form-text">Leave blank for no expiry.</div>
            @error('expires_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @else
            <div class="alert alert-light border text-muted small mb-0">
              Set expirations after upgrading to a paid plan.
            </div>
            @endif
          </div>

          {{-- Max Clicks --}}
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label for="max_clicks" class="form-label">
                Click Limit <span class="text-muted">(optional)</span>
              </label>
              @if(!$features->get('click_limit'))
              <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em;">
                <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
              </a>
              @endif
            </div>
            @if($features->get('click_limit'))
            <input type="number" id="max_clicks" name="max_clicks"
              class="form-control @error('max_clicks') is-invalid @enderror"
              value="{{ old('max_clicks') }}"
              placeholder="e.g. 100"
              min="1" step="1">
            <div class="form-text">Link will expire after this many clicks. Leave blank for unlimited.</div>
            @error('max_clicks')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @else
            <div class="alert alert-light border text-muted small mb-0">
              Click limits unlock with a paid subscription.
            </div>
            @endif
          </div>

          </div>

          <div class="section-card mb-5">
            <div class="section-heading d-flex align-items-center mb-3">
              <span class="badge rounded-pill bg-info-subtle text-info fw-semibold me-2">Basic</span>
              <div>
                <h6 class="mb-0">Access & Privacy</h6>
                <p class="text-muted small mb-0">Keep sensitive campaigns private or time-limited.</p>
              </div>
            </div>

          {{-- Password --}}
          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label for="password" class="form-label">
                Password Protection <span class="text-muted">(optional)</span>
              </label>
              @if(!$features->get('password_protection'))
              <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em;">
                <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
              </a>
              @endif
            </div>
            @if($features->get('password_protection'))
            <div class="row g-2">
              <div class="col-sm-6">
                <input type="password" id="password" name="password"
                  class="form-control @error('password') is-invalid @enderror"
                  placeholder="Set a password" autocomplete="new-password">
              </div>
              <div class="col-sm-6">
                <input type="password" id="password_confirmation" name="password_confirmation"
                  class="form-control"
                  placeholder="Confirm password" autocomplete="new-password">
              </div>
            </div>
            <div class="form-text mt-1">Visitors will need this password to access the link.</div>
            @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @else
            <div class="alert alert-light border text-muted small mb-0">
              Password-protected links require a paid plan.
            </div>
            @endif
          </div>

          {{-- Private Link --}}
          <div class="mb-4">
            @if($features->get('private_link'))
            <div class="form-check form-switch border rounded p-3 bg-light position-relative d-flex align-items-start">
              <input class="form-check-input ms-0 mt-1 me-2" type="checkbox" role="switch" id="is_private" name="is_private" value="1" {{ old('is_private') ? 'checked' : '' }}>
              <label class="form-check-label ms-2 flex-grow-1" style="cursor: pointer;" for="is_private">
                <strong>Private Link (Login Required)</strong>
                <span class="d-block text-muted small mt-1">If enabled, only authenticated users can open this short URL. Visitors will be redirected to the login page first.</span>
              </label>
            </div>
            @error('is_private')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @else
            <div class="d-flex justify-content-between align-items-center mb-1">
              <strong>Private Link (Login Required)</strong>
              <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em;">
                <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
              </a>
            </div>
            <div class="alert alert-light border text-muted small mb-0">
              Upgrade to enable privacy controls for your short links.
            </div>
            @endif
          </div>

          {{-- 24h Story Link --}}
          <div class="mb-4">
            @if($features->get('24h_story_link'))
            <div class="form-check form-switch border rounded p-3 bg-light position-relative d-flex align-items-start">
              <input class="form-check-input ms-0 mt-1 me-2" type="checkbox" role="switch" id="is_24h_story" name="is_24h_story" value="1" {{ old('is_24h_story') ? 'checked' : '' }}>
              <label class="form-check-label ms-2 flex-grow-1" style="cursor: pointer;" for="is_24h_story">
                <strong>24-Hour Story Link</strong>
                <span class="d-block text-muted small mt-1">If enabled, the link will automatically expire exactly 24 hours from creation.</span>
              </label>
            </div>
            @error('is_24h_story')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @else
            <div class="d-flex justify-content-between align-items-center mb-1">
              <strong>24-Hour Story Link</strong>
              <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em; position:relative; z-index:2;">
                <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
              </a>
            </div>
            <div class="alert alert-light border text-muted small mb-0">
              This time-limited mode unlocks on higher plans.
            </div>
            @endif
          </div>

          {{-- One-Time Link --}}
          <div class="mb-0">
            @if($features->get('one_time_link'))
            <div class="form-check form-switch border rounded p-3 bg-light position-relative d-flex align-items-start">
              <input class="form-check-input ms-0 mt-1 me-2" type="checkbox" role="switch" id="is_one_time" name="is_one_time" value="1" {{ old('is_one_time') ? 'checked' : '' }}>
              <label class="form-check-label ms-2 flex-grow-1" style="cursor: pointer;" for="is_one_time">
                <strong>One-Time Open / Self Destruct</strong>
                <span class="d-block text-muted small mt-1">If enabled, the link will permanently disable itself after the first successful visit.</span>
              </label>
            </div>
            @error('is_one_time')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @else
            <div class="d-flex justify-content-between align-items-center mb-1">
              <strong>One-Time Open / Self Destruct</strong>
              <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em; position:relative; z-index:2;">
                <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
              </a>
            </div>
            <div class="alert alert-light border text-muted small mb-0">
              Self-destructing links are part of advanced plans.
            </div>
            @endif
          </div>

          </div>

          <div class="section-card mb-5">
            <div class="section-heading d-flex align-items-center mb-3">
              <span class="badge rounded-pill bg-warning-subtle text-warning fw-semibold me-2">Pro</span>
              <div>
                <h6 class="mb-0">Smart Routing & Automation</h6>
                <p class="text-muted small mb-0">Advanced targeting tools for growth teams.</p>
              </div>
            </div>

          {{-- Device URLs --}}
          <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              @if($features->get('device_redirect'))
              <button class="btn btn-link px-0 text-secondary text-decoration-none fw-medium d-flex align-items-center outline-none shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#deviceUrls">
                <i data-lucide="smartphone" class="icon-sm me-1"></i> Device Based Smart Redirect <span class="text-muted fw-normal ms-1">(Optional)</span>
              </button>
              @else
              <div class="text-secondary fw-medium d-flex align-items-center">
                <i data-lucide="smartphone" class="icon-sm me-1"></i> Device Based Smart Redirect <span class="text-muted fw-normal ms-1">(Optional)</span>
              </div>
              @endif
              <div>
                @if(!$features->get('device_redirect'))
                    <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em;" title="Upgrade to Pro manually to unlock">
                        <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
                    </a>
                @elseif(!Auth::user()->isAdmin())
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle ms-1" style="font-size: 0.65rem;">Pro</span>
                @endif
              </div>
            </div>
            @if($features->get('device_redirect'))
            <div class="collapse {{ old('mobile_url') || old('tablet_url') || old('desktop_url') ? 'show' : '' }}" id="deviceUrls">
              <div class="card card-body bg-light border-0 mt-2 p-3">
                <div class="mb-2">
                  <label class="form-label mb-1"><i data-lucide="smartphone" class="icon-xs me-1"></i>Mobile URL</label>
                  <input type="url" name="mobile_url" class="form-control form-control-sm @error('mobile_url') is-invalid @enderror" value="{{ old('mobile_url') }}" placeholder="e.g. https://m.example.com">
                  @error('mobile_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-2">
                  <label class="form-label mb-1"><i data-lucide="tablet" class="icon-xs me-1"></i>Tablet URL</label>
                  <input type="url" name="tablet_url" class="form-control form-control-sm @error('tablet_url') is-invalid @enderror" value="{{ old('tablet_url') }}" placeholder="e.g. https://t.example.com">
                  @error('tablet_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-0">
                  <label class="form-label mb-1"><i data-lucide="monitor" class="icon-xs me-1"></i>Desktop URL</label>
                  <input type="url" name="desktop_url" class="form-control form-control-sm @error('desktop_url') is-invalid @enderror" value="{{ old('desktop_url') }}" placeholder="e.g. https://desktop.example.com">
                  @error('desktop_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <small class="text-muted mt-3 d-block">If specified, visitors will be redirected to these URLs based on their device instead of the default Original URL.</small>
              </div>
            </div>
            @else
            <div class="alert alert-light border text-muted small mb-0">
              Smart redirects by device unlock with the Pro plan.
            </div>
            @endif

          </div>

          {{-- Office Hours URLs --}}
          <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              @if($features->get('office_hours_redirect'))
              <button class="btn btn-link px-0 text-secondary text-decoration-none fw-medium d-flex align-items-center outline-none shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#officeHoursUrls">
                <i data-lucide="clock" class="icon-sm me-1"></i> Office Hours Time-Based Redirect <span class="text-muted fw-normal ms-1">(Optional)</span>
              </button>
              @else
              <div class="text-secondary fw-medium d-flex align-items-center">
                <i data-lucide="clock" class="icon-sm me-1"></i> Office Hours Time-Based Redirect <span class="text-muted fw-normal ms-1">(Optional)</span>
              </div>
              @endif
              <div>
                @if(!$features->get('office_hours_redirect'))
                    <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em;" title="Upgrade to Pro manually to unlock">
                        <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
                    </a>
                @elseif(!Auth::user()->isAdmin())
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle ms-1" style="font-size: 0.65rem;">Pro</span>
                @endif
              </div>
            </div>
            @if($features->get('office_hours_redirect'))
            <div class="collapse {{ old('office_url') || old('after_hours_url') ? 'show' : '' }}" id="officeHoursUrls">
              <div class="card card-body bg-light border-0 mt-2 p-3">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label mb-1">Timezone</label>
                    <select name="timezone" class="form-select form-select-sm @error('timezone') is-invalid @enderror">
                      <option value="">Select Timezone</option>
                      @foreach(timezone_identifiers_list() as $tz)
                      <option value="{{ $tz }}" {{ old('timezone', 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                      @endforeach
                    </select>
                    @error('timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex gap-2">
                      <div class="flex-fill">
                        <label class="form-label mb-1">Start Time</label>
                        <input type="time" name="office_start_time" class="form-control form-control-sm @error('office_start_time') is-invalid @enderror" value="{{ old('office_start_time') }}">
                        @error('office_start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                      </div>
                      <div class="flex-fill">
                        <label class="form-label mb-1">End Time</label>
                        <input type="time" name="office_end_time" class="form-control form-control-sm @error('office_end_time') is-invalid @enderror" value="{{ old('office_end_time') }}">
                        @error('office_end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                      </div>
                    </div>
                  </div>
                  <div class="col-12">
                    <label class="form-label d-block mb-1">Office Days</label>
                    <div class="d-flex flex-wrap gap-3">
                      @php
                      $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                      $oldDays = old('office_days', []);
                      @endphp
                      @foreach($days as $day)
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="office_days[]" value="{{ strtolower($day) }}" id="day_{{ $day }}" {{ is_array($oldDays) && in_array(strtolower($day), $oldDays) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="day_{{ $day }}">{{ substr($day, 0, 3) }}</label>
                      </div>
                      @endforeach
                    </div>
                    @error('office_days') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label mb-1">Office URL</label>
                    <input type="url" name="office_url" class="form-control form-control-sm @error('office_url') is-invalid @enderror" value="{{ old('office_url') }}" placeholder="e.g. https://example.com/open">
                    @error('office_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label mb-1">After Hours URL</label>
                    <input type="url" name="after_hours_url" class="form-control form-control-sm @error('after_hours_url') is-invalid @enderror" value="{{ old('after_hours_url') }}" placeholder="e.g. https://example.com/closed">
                    @error('after_hours_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-12 mt-2">
                    <div class="px-3 py-2 bg-white rounded border" id="office-hours-preview">
                      <span class="text-muted small">Fill out timezone, times, days, and URLs to see a live preview of the current destination.</span>
                    </div>
                  </div>
                </div>
                <small class="text-muted mt-3 d-block">If specified, redirects will route during these office hours. Overrides Device rules.</small>
              </div>
            </div>
            @else
            <div class="alert alert-light border text-muted small mb-0">
              Time-based routing unlocks with the Pro plan.
            </div>
            @endif

          </div>

          {{-- Social / OG Preview --}}
          <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              @if($features->get('custom_og_preview'))
              <button class="btn btn-link px-0 text-secondary text-decoration-none fw-medium d-flex align-items-center outline-none shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#socialPreviewUrls">
                <i data-lucide="image" class="icon-sm me-1"></i> Custom Social Preview (Open Graph) <span class="text-muted fw-normal ms-1">(Optional)</span>
              </button>
              @else
              <div class="text-secondary fw-medium d-flex align-items-center">
                <i data-lucide="image" class="icon-sm me-1"></i> Custom Social Preview (Open Graph) <span class="text-muted fw-normal ms-1">(Optional)</span>
              </div>
              @endif
              <div>
                @if(!$features->get('custom_og_preview'))
                    <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em;" title="Upgrade to Pro manually to unlock">
                        <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
                    </a>
                @elseif(!Auth::user()->isAdmin())
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle ms-1" style="font-size: 0.65rem;">Pro</span>
                @endif
              </div>
            </div>
            @if($features->get('custom_og_preview'))
            <div class="collapse {{ old('og_title') || old('og_description') || old('og_image') ? 'show' : '' }}" id="socialPreviewUrls">
              <div class="card card-body bg-light border-0 mt-2 p-3">
                <div class="row g-3">
                  <div class="col-md-12">
                    <label class="form-label mb-1">OG Title</label>
                    <input type="text" name="og_title" class="form-control form-control-sm @error('og_title') is-invalid @enderror" value="{{ old('og_title') }}" placeholder="Title displayed on social media">
                    @error('og_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-12">
                    <label class="form-label mb-1">OG Description</label>
                    <textarea name="og_description" class="form-control form-control-sm @error('og_description') is-invalid @enderror" placeholder="Description displayed on social media" rows="2">{{ old('og_description') }}</textarea>
                    @error('og_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-12">
                    <label class="form-label mb-1">OG Image URL</label>
                    <input type="url" name="og_image" class="form-control form-control-sm @error('og_image') is-invalid @enderror" value="{{ old('og_image') }}" placeholder="e.g. https://example.com/image.jpg">
                    @error('og_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-12 mt-2">
                    <label class="form-label mb-1 text-muted small"><i data-lucide="eye" class="icon-xs me-1"></i>Live Preview Card</label>
                    <div class="px-3 py-3 bg-white rounded border" style="max-width: 400px;">
                      <div id="og-preview-image" class="bg-light rounded mb-2 d-flex align-items-center justify-content-center text-muted" style="height: 150px; overflow:hidden;">
                        <span>No Image Provide</span>
                      </div>
                      <div id="og-preview-title" class="fw-bold mb-1 text-truncate">Domain Title</div>
                      <div id="og-preview-desc" class="text-muted small" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">Domain Description snippet...</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @else
            <div class="alert alert-light border text-muted small mb-0">
              Custom social previews are only available on Pro.
            </div>
            @endif

          </div>

          {{-- IP Blocking --}}
          <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              @if($features->get('ip_blocking'))
              <button class="btn btn-link px-0 text-secondary text-decoration-none fw-medium d-flex align-items-center outline-none shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#ipBlockingRules">
                <i data-lucide="shield-alert" class="icon-sm me-1"></i> IP Blocking <span class="text-muted fw-normal ms-1">(Optional)</span>
              </button>
              @else
              <div class="text-secondary fw-medium d-flex align-items-center">
                <i data-lucide="shield-alert" class="icon-sm me-1"></i> IP Blocking <span class="text-muted fw-normal ms-1">(Optional)</span>
              </div>
              @endif
              <div>
                @if(!$features->get('ip_blocking'))
                    <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em;" title="Upgrade to Pro manually to unlock">
                        <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
                    </a>
                @elseif(!Auth::user()->isAdmin())
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle ms-1" style="font-size: 0.65rem;">Pro</span>
                @endif
              </div>
            </div>
            @if($features->get('ip_blocking'))
            <div class="collapse {{ old('ip_blocks') ? 'show' : '' }}" id="ipBlockingRules">
              <div class="card card-body bg-light border-0 mt-2 p-3">
                <p class="text-muted small mb-2">Block specific IP addresses or CIDR ranges from accessing this link.</p>
                <div id="ip-blocks-container">
                  @php $oldIpBlocks = old('ip_blocks', []); @endphp
                  @foreach($oldIpBlocks as $index => $block)
                  <div class="row g-2 align-items-center mb-2 ip-block-row">
                    <div class="col-auto">
                      <select name="ip_blocks[{{ $index }}][type]" class="form-select form-select-sm">
                        <option value="ip" {{ ($block['type'] ?? '') == 'ip' ? 'selected' : '' }}>IP Address</option>
                        <option value="cidr" {{ ($block['type'] ?? '') == 'cidr' ? 'selected' : '' }}>CIDR Range</option>
                      </select>
                    </div>
                    <div class="col">
                      <input type="text" name="ip_blocks[{{ $index }}][value]" class="form-control form-control-sm" placeholder="e.g. 192.168.1.1 or 10.0.0.0/24" value="{{ $block['value'] ?? '' }}">
                    </div>
                    <div class="col-auto">
                      <button type="button" class="btn btn-sm btn-outline-danger remove-ip-block" aria-label="Remove"><i data-lucide="trash" class="icon-xs"></i></button>
                    </div>
                  </div>
                  @endforeach
                </div>
                <div>
                  <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="add-ip-block">
                    <i data-lucide="plus" class="icon-xs me-1"></i> Add IP/CIDR Block
                  </button>
                </div>
              </div>
            </div>
            @else
            <div class="alert alert-light border text-muted small mb-0">
              Block visitors by IP after upgrading to Pro.
            </div>
            @endif

          </div>

          {{-- Redirect Delay --}}
          <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
              @if($features->get('redirect_delay'))
              <button class="btn btn-link px-0 text-secondary text-decoration-none fw-medium d-flex align-items-center outline-none shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#redirectDelay">
                <i data-lucide="timer" class="icon-sm me-1"></i> Redirect Delay <span class="text-muted fw-normal ms-1">(Optional)</span>
              </button>
              @else
              <div class="text-secondary fw-medium d-flex align-items-center">
                <i data-lucide="timer" class="icon-sm me-1"></i> Redirect Delay <span class="text-muted fw-normal ms-1">(Optional)</span>
              </div>
              @endif
              <div>
                @if(!$features->get('redirect_delay'))
                    <a href="{{ route('front.pricing') }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none d-inline-flex align-items-center gap-1 shadow-sm transition-all" style="font-size: 0.70rem; padding: 0.35em 0.65em;" title="Upgrade to Pro manually to unlock">
                        <i data-lucide="lock" style="width: 12px; height: 12px;"></i> Unlock
                    </a>
                @elseif(!Auth::user()->isAdmin())
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle ms-1" style="font-size: 0.65rem;">Pro</span>
                @endif
              </div>
            </div>
            @if($features->get('redirect_delay'))
            <div class="collapse {{ old('redirect_delay') ? 'show' : '' }}" id="redirectDelay">
              <div class="card card-body bg-light border-0 mt-2 p-3">
                <p class="text-muted small mb-2">Show an interstitial page with a countdown before redirecting the visitor.</p>
                <div class="row g-2 align-items-center mb-2">
                  <div class="col-md-6">
                    <label class="form-label mb-1">Delay (Seconds)</label>
                    @php $maxDelay = \App\Models\Setting::get('max_redirect_delay', 30); @endphp
                    <input type="number" name="redirect_delay" class="form-control form-control-sm @error('redirect_delay') is-invalid @enderror" value="{{ old('redirect_delay', 0) }}" min="0" max="{{ $maxDelay }}">
                    <div class="form-text small">Set to 0 to skip delay. Maximum allowed is {{ $maxDelay }} seconds.</div>
                    @error('redirect_delay') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
              </div>
            </div>
            @else
            <div class="alert alert-light border text-muted small mb-0">
              Countdown redirects unlock on the Pro plan.
            </div>
            @endif

          </div>

          </div>

          <div class="d-flex gap-2">
            <button type="submit" id="submit-btn" class="btn btn-primary btn-sm px-4">
              <i data-lucide="save" class="icon-sm me-1"></i>Create Short Link
            </button>
            <a href="{{ route('user.links') }}" class="btn btn-outline-secondary btn-sm">
              <i data-lucide="x" class="icon-sm me-1"></i>Cancel
            </a>
          </div>

        </form>
      </div>
    </div>
  </div>

  {{-- Tips --}}
  <div class="col-lg-10 col-xl-9 mt-4">
    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4">
        <div class="d-flex align-items-center mb-3">
          <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center me-2" style="width:34px;height:34px;">
            <i data-lucide="info" class="icon-sm"></i>
          </span>
          <h6 class="fw-semibold mb-0">Helpful Tips</h6>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="d-flex align-items-start gap-2">
              <i data-lucide="sparkles" class="icon-sm text-primary mt-1"></i>
              <p class="small text-muted mb-0">Leave the slug empty to auto-generate a short, unique code.</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex align-items-start gap-2">
              <i data-lucide="type" class="icon-sm text-primary mt-1"></i>
              <p class="small text-muted mb-0">Custom slugs support lowercase letters, numbers, hyphens, and underscores.</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex align-items-start gap-2">
              <i data-lucide="target" class="icon-sm text-primary mt-1"></i>
              <p class="small text-muted mb-0">Use click limits for flash campaigns so links retire automatically.</p>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex align-items-start gap-2">
              <i data-lucide="bookmark" class="icon-sm text-primary mt-1"></i>
              <p class="small text-muted mb-0">Add titles to quickly find links in your dashboard later.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('style')
<style>
.section-card {
  background: var(--bs-body-bg);
  border: 1px solid rgba(15, 23, 42, .08);
  border-radius: 1.5rem;
  padding: 1.75rem;
  box-shadow: 0 10px 30px rgba(15, 23, 42, .05);
}
.section-card + .section-card {
  margin-top: 2rem;
}
.section-heading h6 {
  font-weight: 600;
}
.section-heading .badge {
  font-size: .7rem;
}
</style>
@endpush

@push('plugin-scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>
@endpush

@push('custom-scripts')
<script>
  $(function() {

    // ── jQuery Validate ────────────────────────────────────────────────────────
    $.validator.setDefaults({
      errorElement: 'div',
      errorClass: 'invalid-feedback d-block',
      highlight: function(element) {
        $(element).addClass('is-invalid').removeClass('is-valid');
      },
      unhighlight: function(element) {
        $(element).removeClass('is-invalid').addClass('is-valid');
      },
      errorPlacement: function(error, element) {
        // For input-group elements, place error after the parent .input-group
        if (element.parent().hasClass('input-group')) {
          error.insertAfter(element.closest('.mb-3').find('.input-group'));
        } else {
          error.insertAfter(element);
        }
      }
    });

    $('#create-link-form').validate({
      rules: {
        original_url: {
          required: true,
          url: true
        },
        title: {
          maxlength: 255
        },
        custom_alias: {
          minlength: 3,
          maxlength: 64,
          pattern: /^[a-z0-9\-_]+$/
        }
      },
      messages: {
        original_url: {
          required: 'Please enter a destination URL.',
          url: 'Please enter a valid URL (including https://).'
        },
        custom_alias: {
          minlength: 'Slug must be at least 3 characters.',
          maxlength: 'Slug may not exceed 64 characters.',
          pattern: 'Only lowercase letters, numbers, hyphens, and underscores are allowed.'
        }
      },
      submitHandler: function(form) {
        form.submit();
      }
    });

    // ── AJAX Slug Checker ──────────────────────────────────────────────────────
    var $input = $('#custom_alias');
    var $feedback = $('#slug-feedback');
    var $statusIcon = $('#slug-status-icon');
    var $submitBtn = $('#submit-btn');
    var checkUrl = '{{ route("user.links.check-slug") }}';
    var debounceTimer;

    var icons = {
      checking: '<span class="spinner-border spinner-border-sm text-secondary" role="status"></span>',
      available: '<i data-lucide="check-circle" class="icon-sm text-success"></i>',
      taken: '<i data-lucide="x-circle" class="icon-sm text-danger"></i>',
      warning: '<i data-lucide="alert-circle" class="icon-sm text-warning"></i>',
      idle: ''
    };

    var cssMap = {
      available: 'text-success',
      taken: 'text-danger',
      warning: 'text-warning',
      checking: 'text-muted',
      idle: 'text-muted'
    };

    function setStatus(type, message) {
      $feedback.attr('class', 'form-text mt-1').addClass(cssMap[type] || 'text-muted').text(message);
      $statusIcon.html(icons[type] || '');
      $input.removeClass('is-valid is-invalid');

      if (type === 'taken' || type === 'warning') {
        $input.addClass('is-invalid');
      } else if (type === 'available') {
        $input.addClass('is-valid');
      }

      $submitBtn.prop('disabled', type === 'taken' || type === 'warning');
      if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function checkSlug(slug) {
      if (slug === '') {
        setStatus('idle', 'Lowercase letters, numbers, hyphens, underscores · 3–64 chars · Leave blank to auto-generate.');
        $submitBtn.prop('disabled', false);
        return;
      }

      setStatus('checking', 'Checking availability…');

      $.ajax({
        url: checkUrl,
        type: 'GET',
        data: {
          slug: slug
        },
        dataType: 'json',
        success: function(data) {
          if (data.available) {
            setStatus(data.status === 'empty' ? 'idle' : 'available', data.message);
          } else {
            setStatus(data.status === 'invalid_format' ? 'warning' : 'taken', data.message);
          }
        },
        error: function() {
          setStatus('idle', 'Could not check availability. Please try again.');
          $submitBtn.prop('disabled', false);
        }
      });
    }

    $input.on('input', function() {
      // Auto-lowercase as the user types
      var pos = this.selectionStart;
      this.value = this.value.toLowerCase();
      this.setSelectionRange(pos, pos);

      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function() {
        checkSlug($.trim($input.val()));
      }, 450);
    });

    // Social OG Preview Live Update
    function updateOgPreview() {
      const title = document.querySelector('input[name="og_title"]').value || 'Preview Title';
      const desc = document.querySelector('textarea[name="og_description"]').value || 'Description snippet...';
      const image = document.querySelector('input[name="og_image"]').value;

      document.getElementById('og-preview-title').textContent = title;
      document.getElementById('og-preview-desc').textContent = desc;

      const imgContainer = document.getElementById('og-preview-image');
      if (image) {
        imgContainer.innerHTML = `<img src="${image}" class="w-100 h-100" style="object-fit:cover;" onerror="this.outerHTML='<span class=\\'text-muted\\'>Broken Image</span>'" />`;
      } else {
        imgContainer.innerHTML = '<span>No Image Provided</span>';
      }
    }

    const ogInputs = document.querySelectorAll('#socialPreviewUrls input, #socialPreviewUrls textarea');
    ogInputs.forEach(input => {
      input.addEventListener('input', updateOgPreview);
    });
    updateOgPreview();

    // IP Blocks Logic
    document.getElementById('add-ip-block').addEventListener('click', function() {
      const container = document.getElementById('ip-blocks-container');
      const idx = container.querySelectorAll('.ip-block-row').length;
      const html = `
          <div class="row g-2 align-items-center mb-2 ip-block-row">
              <div class="col-auto">
                  <select name="ip_blocks[${idx}][type]" class="form-select form-select-sm">
                      <option value="ip">IP Address</option>
                      <option value="cidr">CIDR Range</option>
                  </select>
              </div>
              <div class="col">
                  <input type="text" name="ip_blocks[${idx}][value]" class="form-control form-control-sm" placeholder="e.g. 192.168.1.1 or 10.0.0.0/24">
              </div>
              <div class="col-auto">
                  <button type="button" class="btn btn-sm btn-outline-danger remove-ip-block" aria-label="Remove"><i data-lucide="trash" class="icon-xs"></i></button>
              </div>
          </div>
      `;
      container.insertAdjacentHTML('beforeend', html);
      if (typeof lucide !== 'undefined') {
        lucide.createIcons();
      }
    });

    document.getElementById('ip-blocks-container').addEventListener('click', function(e) {
      if (e.target.closest('.remove-ip-block')) {
        e.target.closest('.ip-block-row').remove();
        // re-index
        document.querySelectorAll('.ip-block-row').forEach((row, index) => {
          row.querySelector('select').name = `ip_blocks[${index}][type]`;
          row.querySelector('input').name = `ip_blocks[${index}][value]`;
        });
      }
    });
  });
</script>
@endpush
