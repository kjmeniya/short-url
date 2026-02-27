@extends('user.layout.master')

@section('title', 'My Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
  <div>
    <h4 class="mb-1">My Profile</h4>
    <p class="text-secondary mb-0">Manage your personal settings and password.</p>
  </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <i data-lucide="check-circle" class="icon-sm me-2"></i>{{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <ul class="mb-0 ps-3">
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
  <div class="col-md-6">
    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-header bg-transparent border-bottom-0 pt-4 px-4 pb-0">
        <h6 class="card-title mb-0 fw-bold">Profile Details</h6>
        <p class="text-muted small mt-1">Update your display name and change your password.</p>
      </div>
      <div class="card-body p-4 pt-3">
        <form action="{{ route('user.profile.update') }}" method="POST">
          @csrf
          @method('PUT')
          <div class="mb-3">
            <label class="form-label fw-semibold">Email <span class="badge bg-success bg-opacity-10 text-success ms-2">Verified</span></label>
            <input type="text" class="form-control" value="{{ $user->email }}" readonly disabled>
            <div class="form-text">Your email address cannot be changed.</div>
          </div>
          
          <div class="mb-4">
            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
          </div>

          <hr class="text-secondary opacity-25">

          <h6 class="fw-bold mt-4 mb-3"><i data-lucide="lock" class="icon-sm text-primary me-2"></i>Change Password</h6>
          <p class="text-muted small mb-3">Leave these fields blank if you do not want to change your password.</p>
          
          <div class="mb-3">
            @include('admin.partials.password-field', [
              'name' => 'password',
              'label' => 'New Password',
              'placeholder' => 'Enter new password',
              'required' => false,
              'showStrengthMeter' => true,
              'autocomplete' => 'new-password'
            ])
          </div>
          
          <div class="mb-4">
            @include('admin.partials.password-field', [
              'name' => 'password_confirmation',
              'label' => 'Confirm Password',
              'placeholder' => 'Re-enter new password',
              'required' => false,
              'showStrengthMeter' => false,
              'autocomplete' => 'new-password'
            ])
          </div>

          <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary py-2 fw-medium shadow-sm">
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
