@extends('admin.layout.master')

@section('title', $title ?? 'Create User')
@section('description', $description ?? 'Create new user accounts with role assignments')
@section('keywords', $keywords ?? 'create user, new account, user registration')

@push('plugin-styles')
<link href="{{ asset('build/plugins/flatpickr/flatpickr.min.css') }}" rel="stylesheet" />
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">Add User</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 col-xl-12 middle-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title">Add New User</h6>

            @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="forms-sample" id="userCreateForm">
              @csrf

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                      id="name" name="name" value="{{ old('name') }}" placeholder="Enter full name"
                      maxlength="255" data-maxlength="true" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                      id="email" name="email" value="{{ old('email') }}" placeholder="Enter email address"
                      maxlength="255" data-maxlength="true" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                      id="password" name="password" placeholder="Enter password (min 8 characters)"
                      minlength="8" required>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Password must be at least 8 characters long</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password *</label>
                    <input type="password" class="form-control"
                      id="password_confirmation" name="password_confirmation" placeholder="Confirm password"
                      minlength="8" required>
                    <small class="form-text text-muted">Must match the password above</small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                      id="phone" name="phone" value="{{ old('phone') }}" placeholder="Enter phone number"
                      maxlength="20" data-maxlength="true">
                    @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Format: +1234567890 or (123) 456-7890</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                    <input type="text" class="form-control @error('date_of_birth') is-invalid @enderror"
                      id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                      placeholder="Select date of birth" readonly>
                    @error('date_of_birth')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Click to select date</small>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control @error('address') is-invalid @enderror"
                  id="address" name="address" rows="3" placeholder="Enter full address"
                  maxlength="500" data-maxlength="true">{{ old('address') }}</textarea>
                @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="role_id" class="form-label">User Role *</label>
                    <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                      <option value="">Select Role</option>
                      @foreach($roles as $role)
                      <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->display_name }}
                        @if($role->description)
                        - {{ Str::limit($role->description, 50) }}
                        @endif
                      </option>
                      @endforeach
                    </select>
                    @error('role_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Assign user role and permissions</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="is_active" class="form-label">Account Status *</label>
                    <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
                      <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                      <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Active users can login to the system</small>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="timezone" class="form-label">Timezone</label>
                    <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                      <option value="">Use System Default ({{ app(\App\Services\SettingsService::class)->get('timezone', 'UTC') }})</option>
                      @foreach(getTimezoneOptions() as $value => $label)
                      <option value="{{ $value }}" {{ old('timezone') == $value ? 'selected' : '' }}>
                        {{ $label }}
                      </option>
                      @endforeach
                    </select>
                    @error('timezone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">User's preferred timezone for date/time display</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="mb-3">
                    <label for="language" class="form-label">Language</label>
                    <select class="form-select @error('language') is-invalid @enderror" id="language" name="language">
                      @foreach(getLanguageOptions() as $value => $label)
                      <option value="{{ $value }}" {{ old('language', 'en') == $value ? 'selected' : '' }}>
                        {{ $label }}
                      </option>
                      @endforeach
                    </select>
                    @error('language')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">User's preferred language</small>
                  </div>
                </div>
              </div>

              {{-- Avatar Upload with Cropper --}}
              @include('admin.partials.image-cropper', [
              'inputId' => 'avatar',
              'label' => 'Profile Picture'
              ])

              <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                  <i data-lucide="x" class="icon-sm me-1"></i>
                  <span class="d-none d-sm-inline">Cancel</span>
                  <span class="d-sm-none">Cancel</span>
                </a>
                <button type="button" class="btn btn-primary btn-sm" id="submitBtn">
                  <i data-lucide="user-plus" class="icon-sm me-1"></i>
                  <span class="d-none d-sm-inline">Create User</span>
                  <span class="d-sm-none">Create</span>
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
<script src="{{ asset('build/plugins/flatpickr/flatpickr.min.js') }}"></script>
@endpush

@push('custom-scripts')
@vite(['resources/js/admin/validation/users.js'])
@endpush