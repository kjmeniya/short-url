@extends('user.layout.master')

@section('title', 'Edit Link')

@section('content')

{{-- Breadcrumb --}}
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('user.links') }}">My Links</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit — <code>{{ $link->code }}</code></li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-8 col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
          <i data-lucide="pencil" class="icon-sm me-2"></i>Edit Link &mdash; <code>{{ $link->code }}</code>
        </h6>
        <a href="{{ route('user.links') }}" class="btn btn-sm btn-outline-secondary">
          <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back
        </a>
      </div>
      <div class="card-body">

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

        <form id="edit-link-form" action="{{ route('user.links.update', $link->id) }}" method="POST" novalidate>
          @csrf
          @method('PUT')

          {{-- Current Short URL (read-only) --}}
          <div class="mb-3">
            <label class="form-label">Short URL</label>
            <div class="input-group">
              <input type="text" class="form-control" value="{{ $link->short_url }}" readonly>
              <button class="btn btn-outline-secondary" type="button" id="copyShortUrl"
                data-url="{{ $link->short_url }}" title="Copy Short URL">
                <i data-lucide="copy" class="icon-sm"></i>
              </button>
            </div>
          </div>

          {{-- Destination URL --}}
          <div class="mb-3">
            <label for="original_url" class="form-label">
              Destination URL <span class="text-danger">*</span>
            </label>
            <input type="url" id="original_url" name="original_url"
              class="form-control @error('original_url') is-invalid @enderror"
              value="{{ old('original_url', $link->original_url) }}">
            @error('original_url')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Device URLs --}}
          <div class="mb-4">
            <button class="btn btn-link px-0 text-secondary text-decoration-none fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#deviceUrls">
              <i data-lucide="smartphone" class="icon-sm me-1"></i> Device Based Smart Redirect <span class="text-muted fw-normal">(Optional)</span>
            </button>
            <div class="collapse {{ old('mobile_url', $link->mobile_url) || old('tablet_url', $link->tablet_url) || old('desktop_url', $link->desktop_url) ? 'show' : '' }}" id="deviceUrls">
              <div class="card card-body bg-light border-0 mt-2 p-3">
                <div class="mb-2">
                  <label class="form-label mb-1"><i data-lucide="smartphone" class="icon-xs me-1"></i>Mobile URL</label>
                  <input type="url" name="mobile_url" class="form-control form-control-sm @error('mobile_url') is-invalid @enderror" value="{{ old('mobile_url', $link->mobile_url) }}" placeholder="e.g. https://m.example.com">
                  @error('mobile_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-2">
                  <label class="form-label mb-1"><i data-lucide="tablet" class="icon-xs me-1"></i>Tablet URL</label>
                  <input type="url" name="tablet_url" class="form-control form-control-sm @error('tablet_url') is-invalid @enderror" value="{{ old('tablet_url', $link->tablet_url) }}" placeholder="e.g. https://t.example.com">
                  @error('tablet_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-0">
                  <label class="form-label mb-1"><i data-lucide="monitor" class="icon-xs me-1"></i>Desktop URL</label>
                  <input type="url" name="desktop_url" class="form-control form-control-sm @error('desktop_url') is-invalid @enderror" value="{{ old('desktop_url', $link->desktop_url) }}" placeholder="e.g. https://desktop.example.com">
                  @error('desktop_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <small class="text-muted mt-3 d-block">If specified, visitors will be redirected to these URLs based on their device instead of the default Original URL.</small>
              </div>
            </div>
          </div>

          {{-- Office Hours URLs --}}
          <div class="mb-4">
            <button class="btn btn-link px-0 text-secondary text-decoration-none fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#officeHoursUrls">
              <i data-lucide="clock" class="icon-sm me-1"></i> Office Hours Time-Based Redirect <span class="text-muted fw-normal">(Optional)</span>
            </button>
            <div class="collapse {{ old('office_url', $link->office_url) || old('after_hours_url', $link->after_hours_url) ? 'show' : '' }}" id="officeHoursUrls">
              <div class="card card-body bg-light border-0 mt-2 p-3">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label mb-1">Timezone</label>
                    <select name="timezone" class="form-select form-select-sm @error('timezone') is-invalid @enderror">
                      <option value="">Select Timezone</option>
                      @foreach(timezone_identifiers_list() as $tz)
                      <option value="{{ $tz }}" {{ old('timezone', $link->timezone ?? 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                      @endforeach
                    </select>
                    @error('timezone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <div class="d-flex gap-2">
                      <div class="flex-fill">
                        <label class="form-label mb-1">Start Time</label>
                        <input type="time" name="office_start_time" class="form-control form-control-sm @error('office_start_time') is-invalid @enderror" value="{{ old('office_start_time', $link->office_start_time ? substr($link->office_start_time, 0, 5) : '') }}">
                        @error('office_start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                      </div>
                      <div class="flex-fill">
                        <label class="form-label mb-1">End Time</label>
                        <input type="time" name="office_end_time" class="form-control form-control-sm @error('office_end_time') is-invalid @enderror" value="{{ old('office_end_time', $link->office_end_time ? substr($link->office_end_time, 0, 5) : '') }}">
                        @error('office_end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                      </div>
                    </div>
                  </div>
                  <div class="col-12">
                    <label class="form-label d-block mb-1">Office Days</label>
                    <div class="d-flex flex-wrap gap-3">
                      @php
                      $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                      $oldDays = old('office_days', $link->office_days ?? []);
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
                    <input type="url" name="office_url" class="form-control form-control-sm @error('office_url') is-invalid @enderror" value="{{ old('office_url', $link->office_url) }}" placeholder="e.g. https://example.com/open">
                    @error('office_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-6">
                    <label class="form-label mb-1">After Hours URL</label>
                    <input type="url" name="after_hours_url" class="form-control form-control-sm @error('after_hours_url') is-invalid @enderror" value="{{ old('after_hours_url', $link->after_hours_url) }}" placeholder="e.g. https://example.com/closed">
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
          </div>

          {{-- Title --}}
          <div class="mb-3">
            <label for="title" class="form-label">
              Title <span class="text-muted">(optional)</span>
            </label>
            <input type="text" id="title" name="title"
              class="form-control @error('title') is-invalid @enderror"
              value="{{ old('title', $link->title) }}"
              placeholder="Friendly name">
            @error('title')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Social / OG Preview --}}
          <div class="mb-4">
            <button class="btn btn-link px-0 text-secondary text-decoration-none fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#socialPreviewUrls">
              <i data-lucide="image" class="icon-sm me-1"></i> Custom Social Preview (Open Graph) <span class="text-muted fw-normal">(Optional)</span>
            </button>
            <div class="collapse {{ old('og_title', $link->og_title) || old('og_description', $link->og_description) || old('og_image', $link->og_image) ? 'show' : '' }}" id="socialPreviewUrls">
              <div class="card card-body bg-light border-0 mt-2 p-3">
                <div class="row g-3">
                  <div class="col-md-12">
                    <label class="form-label mb-1">OG Title</label>
                    <input type="text" name="og_title" class="form-control form-control-sm @error('og_title') is-invalid @enderror" value="{{ old('og_title', $link->og_title) }}" placeholder="Title displayed on social media">
                    @error('og_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-12">
                    <label class="form-label mb-1">OG Description</label>
                    <textarea name="og_description" class="form-control form-control-sm @error('og_description') is-invalid @enderror" placeholder="Description displayed on social media" rows="2">{{ old('og_description', $link->og_description) }}</textarea>
                    @error('og_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                  <div class="col-md-12">
                    <label class="form-label mb-1">OG Image URL</label>
                    <input type="url" name="og_image" class="form-control form-control-sm @error('og_image') is-invalid @enderror" value="{{ old('og_image', $link->og_image) }}" placeholder="e.g. https://example.com/image.jpg">
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
          </div>

          {{-- IP Blocking --}}
          <div class="mb-4">
            <button class="btn btn-link px-0 text-secondary text-decoration-none fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#ipBlockingRules">
              <i data-lucide="shield-alert" class="icon-sm me-1"></i> IP Blocking <span class="text-muted fw-normal">(Optional)</span>
            </button>
            <div class="collapse {{ old('ip_blocks', $link->ipBlocks->count()) ? 'show' : '' }}" id="ipBlockingRules">
              <div class="card card-body bg-light border-0 mt-2 p-3">
                <p class="text-muted small mb-2">Block specific IP addresses or CIDR ranges from accessing this link.</p>
                <div id="ip-blocks-container">
                  @php $oldIpBlocks = old('ip_blocks', $link->ipBlocks->toArray() ?? []); @endphp
                  @foreach($oldIpBlocks as $index => $block)
                  <div class="row g-2 align-items-center mb-2 ip-block-row">
                    @if(isset($block['id']))
                    <input type="hidden" name="ip_blocks[{{ $index }}][id]" value="{{ $block['id'] }}">
                    @endif
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
          </div>

          {{-- Redirect Delay --}}
          <div class="mb-4">
            <button class="btn btn-link px-0 text-secondary text-decoration-none fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#redirectDelay">
              <i data-lucide="timer" class="icon-sm me-1"></i> Redirect Delay <span class="text-muted fw-normal">(Optional)</span>
            </button>
            <div class="collapse {{ old('redirect_delay', $link->redirect_delay) ? 'show' : '' }}" id="redirectDelay">
              <div class="card card-body bg-light border-0 mt-2 p-3">
                <p class="text-muted small mb-2">Show an interstitial page with a countdown before redirecting the visitor.</p>
                <div class="row g-2 align-items-center mb-2">
                  <div class="col-md-6">
                    <label class="form-label mb-1">Delay (Seconds)</label>
                    @php $maxDelay = \App\Models\Setting::get('max_redirect_delay', 30); @endphp
                    <input type="number" name="redirect_delay" class="form-control form-control-sm @error('redirect_delay') is-invalid @enderror" value="{{ old('redirect_delay', $link->redirect_delay) }}" min="0" max="{{ $maxDelay }}">
                    <div class="form-text small">Set to 0 to skip delay. Maximum allowed is {{ $maxDelay }} seconds.</div>
                    @error('redirect_delay') <div class="invalid-feedback">{{ $message }}</div> @enderror
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Custom Slug --}}
          <div class="mb-3">
            <label for="custom_alias" class="form-label">
              Custom Slug <span class="text-muted">(optional)</span>
            </label>
            <div class="input-group">
              <span class="input-group-text text-muted">{{ url('/') }}/</span>
              <input type="text" id="custom_alias" name="custom_alias"
                class="form-control @error('custom_alias') is-invalid @enderror"
                value="{{ old('custom_alias', $link->custom_alias) }}"
                placeholder="{{ $link->code }}"
                autocomplete="off">
              <span class="input-group-text" id="slug-status-icon" style="min-width:38px;justify-content:center;"></span>
            </div>
            <div id="slug-feedback" class="form-text mt-1">
              Lowercase letters, numbers, hyphens, underscores · 3–64 chars · Auto-code: <strong>{{ $link->code }}</strong>
            </div>
            @error('custom_alias')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          {{-- Status --}}
          <div class="mb-3">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
              <option value="active" {{ old('status', $link->status) === 'active'   ? 'selected' : '' }}>Active</option>
              <option value="inactive" {{ old('status', $link->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Expiry --}}
          <div class="mb-3">
            <label for="expires_at" class="form-label">
              Expiry Date <span class="text-muted">(optional)</span>
            </label>
            <input type="datetime-local" id="expires_at" name="expires_at"
              class="form-control @error('expires_at') is-invalid @enderror"
              value="{{ old('expires_at', $link->expires_at ? $link->expires_at->format('Y-m-d\TH:i') : '') }}">
            <div class="form-text">Leave blank to remove expiry.</div>
            @error('expires_at')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Max Clicks --}}
          <div class="mb-4">
            <label for="max_clicks" class="form-label">
              Click Limit <span class="text-muted">(optional)</span>
            </label>
            <input type="number" id="max_clicks" name="max_clicks"
              class="form-control @error('max_clicks') is-invalid @enderror"
              value="{{ old('max_clicks', $link->max_clicks) }}"
              placeholder="e.g. 100"
              min="1" step="1">
            <div class="form-text">Link expires after this many total clicks. Leave blank for unlimited.</div>
            @error('max_clicks')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          {{-- Password --}}
          <div class="mb-4">
            <label for="password" class="form-label">
              Change Password
              @if($link->password)
              <span class="badge bg-warning bg-opacity-15 text-warning border border-warning ms-1" style="font-size:0.65rem;">
                <i data-lucide="lock" style="width:10px;height:10px;"></i> Set
              </span>
              @endif
            </label>
            <div class="row g-2">
              <div class="col-sm-6">
                <input type="password" id="password" name="password"
                  class="form-control @error('password') is-invalid @enderror"
                  placeholder="Leave blank to keep existing" autocomplete="new-password">
              </div>
              <div class="col-sm-6">
                <input type="password" id="password_confirmation" name="password_confirmation"
                  class="form-control"
                  placeholder="Confirm new password" autocomplete="new-password">
              </div>
            </div>
            @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @if($link->password)
            <div class="form-check form-switch mt-2">
              <input type="checkbox" class="form-check-input cursor-pointer" id="clear_password" name="clear_password" value="1">
              <label class="form-check-label text-danger small cursor-pointer" for="clear_password">Clear existing password and make link public</label>
            </div>
            @endif
          </div>

          {{-- Private Link --}}
          <div class="mb-4">
            <div class="form-check form-switch border rounded p-3 bg-light">
              <input class="form-check-input ms-0 mt-1 me-2" type="checkbox" role="switch" id="is_private" name="is_private" value="1" {{ old('is_private', $link->is_private) ? 'checked' : '' }}>
              <label class="form-check-label d-block ms-4" for="is_private">
                <strong>Private Link (Login Required)</strong>
                <span class="d-block text-muted small mt-1">If enabled, only authenticated users can open this short URL.</span>
              </label>
            </div>
            @error('is_private')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          {{-- 24h Story Link --}}
          <div class="mb-4">
            <div class="form-check form-switch border rounded p-3 bg-light">
              <input class="form-check-input ms-0 mt-1 me-2" type="checkbox" role="switch" id="is_24h_story" name="is_24h_story" value="1" {{ old('is_24h_story', $link->is_24h_story) ? 'checked' : '' }}>
              <label class="form-check-label d-block ms-4" for="is_24h_story">
                <strong>24-Hour Story Link</strong>
                <span class="d-block text-muted small mt-1">If enabled, the link expires in 24 hours. Toggle from off to on to reset the expiration timer.</span>
              </label>
            </div>
            @error('is_24h_story')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          {{-- One-Time Link --}}
          <div class="mb-4">
            <div class="form-check form-switch border rounded p-3 bg-light">
              <input class="form-check-input ms-0 mt-1 me-2" type="checkbox" role="switch" id="is_one_time" name="is_one_time" value="1" {{ old('is_one_time', $link->is_one_time) ? 'checked' : '' }}>
              <label class="form-check-label d-block ms-4" for="is_one_time">
                <strong>One-Time Open / Self Destruct</strong>
                <span class="d-block text-muted small mt-1">If enabled, the link will permanently disable itself after the first successful visit.</span>
              </label>
            </div>
            @error('is_one_time')
            <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm px-4">
              <i data-lucide="save" class="icon-sm me-1"></i>Save Changes
            </button>
            <a href="{{ route('user.links') }}" class="btn btn-outline-secondary btn-sm">
              <i data-lucide="x" class="icon-sm me-1"></i>Cancel
            </a>
          </div>

        </form>
      </div>
    </div>
  </div>

  {{-- Link Info Sidebar --}}
  <div class="col-md-4 col-lg-5">
    <div class="card">
      <div class="card-body">
        <h6 class="fw-semibold mb-3">
          <i data-lucide="info" class="icon-sm me-2"></i>Link Info
        </h6>
        <table class="table table-sm table-borderless mb-0">
          <tr>
            <th class="text-muted" style="width:40%">Code</th>
            <td><code>{{ $link->code }}</code></td>
          </tr>
          <tr>
            <th class="text-muted">Status</th>
            <td>
              @if($link->status == 'active' && !$link->isExpired())
              <span class="badge bg-success">Active</span>
              @elseif($link->isExpired())
              <span class="badge bg-danger">Expired</span>
              @else
              <span class="badge bg-secondary">Inactive</span>
              @endif
            </td>
          </tr>
          <tr>
            <th class="text-muted">Clicks</th>
            <td>{{ number_format($link->clicks) }}</td>
          </tr>
          <tr>
            <th class="text-muted">Created</th>
            <td>{{ $link->created_at->format('M d, Y') }}</td>
          </tr>
          <tr>
            <th class="text-muted">Updated</th>
            <td>{{ $link->updated_at->format('M d, Y') }}</td>
          </tr>
          @if($link->max_clicks)
          <tr>
            <th class="text-muted">Click Limit</th>
            <td>
              {{ number_format($link->clicks) }} / {{ number_format($link->max_clicks) }}
              @php $pct = $link->clickUsagePercent(); @endphp
              @if($pct !== null)
              <div class="progress mt-1" style="height:4px;">
                <div class="progress-bar {{ $pct >= 100 ? 'bg-danger' : ($pct >= 75 ? 'bg-warning' : 'bg-success') }}"
                  role="progressbar" style="width:{{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
              </div>
              <div class="opacity-50" style="font-size:.65rem;">{{ $pct }}% used</div>
              @endif
            </td>
          </tr>
          @endif
          @if($link->expires_at)
          <tr>
            <th class="text-muted">Expires</th>
            <td class="{{ $link->isExpired() ? 'text-danger' : '' }}">
              {{ $link->expires_at->format('M d, Y') }}
            </td>
          </tr>
          @endif
          @if($link->password)
          <tr>
            <th class="text-muted">Password</th>
            <td><span class="badge bg-warning text-dark">Set</span></td>
          </tr>
          @endif
        </table>
      </div>
    </div>

    {{-- Warning note --}}
    <div class="card border-warning mt-3">
      <div class="card-body p-3">
        <h6 class="fw-semibold mb-2 text-warning d-flex align-items-center gap-2">
          <i data-lucide="alert-triangle" class="icon-sm"></i>Important
        </h6>
        <p class="small text-muted mb-0">
          Changing the <strong>custom alias</strong> will break any existing short links you've already shared with others.
        </p>
      </div>
    </div>
  </div>
</div>

@endsection

@push('plugin-scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/additional-methods.min.js"></script>
@endpush

@push('custom-scripts')
<script>
  $(function() {
    // ── Copy Short URL ─────────────────────────────────────────────────────────
    $('#copyShortUrl').on('click', function() {
      var url = $(this).data('url');
      navigator.clipboard.writeText(url).then(function() {
        var $btn = $('#copyShortUrl');
        var origHtml = $btn.html();
        $btn.html('<i data-lucide="check" class="icon-sm text-success"></i>');
        if (typeof lucide !== 'undefined') lucide.createIcons();
        setTimeout(function() {
          $btn.html(origHtml);
          if (typeof lucide !== 'undefined') lucide.createIcons();
        }, 2000);
      });
    });

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
        if (element.parent().hasClass('input-group')) {
          error.insertAfter(element.closest('.mb-3').find('.input-group'));
        } else {
          error.insertAfter(element);
        }
      }
    });

    $('#edit-link-form').validate({
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
    var $submitBtn = $('button[type="submit"]');
    var checkUrl = '{{ route("user.links.check-slug") }}';
    var excludeId = {
      {
        $link - > id
      }
    };
    var autoCode = '{{ $link->code }}';
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
        setStatus('idle', 'Lowercase letters, numbers, hyphens, underscores · 3–64 chars · Auto-code: ' + autoCode);
        $submitBtn.prop('disabled', false);
        return;
      }

      setStatus('checking', 'Checking availability…');

      $.ajax({
        url: checkUrl,
        type: 'GET',
        data: {
          slug: slug,
          exclude_id: excludeId
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

    // Run once on page load if pre-filled
    var initialSlug = $.trim($input.val());
    if (initialSlug !== '') {
      checkSlug(initialSlug);
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
          const idInput = row.querySelector('input[type="hidden"]');
          if (idInput) idInput.name = `ip_blocks[${index}][id]`;

          row.querySelector('select').name = `ip_blocks[${index}][type]`;
          row.querySelector('input[type="text"]').name = `ip_blocks[${index}][value]`;
        });
      }
    });
  });
</script>
@endpush