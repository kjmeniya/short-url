@extends('admin.layout.master')

@section('title', $title ?? 'Role Details')
@section('description', $description ?? 'View role information, permissions and associated users')
@section('keywords', $keywords ?? 'role details, role information, role permissions, role view')

@push('plugin-styles')
<style>
  .info-card {
    border-radius: 0.375rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .permission-category {
    border-radius: 0.375rem;
    margin-bottom: 1rem;
  }

  .permission-category-header {
    padding: 0.75rem 1rem;
  }

  .permission-category-body {
    padding: 1rem;
  }

  .permission-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8f9fa;
  }

  .permission-item:last-child {
    border-bottom: none;
  }

  .user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
  }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $role->display_name }}</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
          <h6 class="card-title mb-0">Role Details: {{ $role->display_name }}</h6>
          <div class="d-flex gap-2">
            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-primary btn-sm">
              <i data-lucide="edit" class="icon-sm me-1"></i>Edit Role
            </a>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
              <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back to Roles
            </a>
          </div>
        </div>

        <!-- Role Information -->
        <div class="info-card bg-light border">
          <div class="row">
            <div class="col-md-6">
              <h6 class="mb-3">
                <i data-lucide="info" class="icon-sm me-2"></i>Role Information
              </h6>
              <table class="table table-borderless">
                <tr>
                  <td class="fw-bold" style="width: 30%;">Role Name:</td>
                  <td><code>{{ $role->name }}</code></td>
                </tr>
                <tr>
                  <td class="fw-bold">Display Name:</td>
                  <td>{{ $role->display_name }}</td>
                </tr>
                <tr>
                  <td class="fw-bold">Status:</td>
                  <td>
                    @if($role->is_active)
                    <span class="badge bg-success">Active</span>
                    @else
                    <span class="badge bg-danger">Inactive</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td class="fw-bold">Created:</td>
                  <td>{{ formatUserDateTime($role->created_at) }}</td>
                </tr>
                <tr>
                  <td class="fw-bold">Updated:</td>
                  <td>{{ formatUserDateTime($role->updated_at) }}</td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <h6 class="mb-3">
                <i data-lucide="file-text" class="icon-sm me-2"></i>Description
              </h6>
              <p class="text-muted">
                {{ $role->description ?: 'No description provided.' }}
              </p>

              <div class="mt-4">
                <div class="row text-center">
                  <div class="col-6">
                    <div class="border rounded p-3">
                      <h4 class="text-primary mb-1">{{ $role->permissions->count() }}</h4>
                      <small class="text-muted">Permissions</small>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="border rounded p-3">
                      <h4 class="text-info mb-1">{{ $role->users->count() }}</h4>
                      <small class="text-muted">Users</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Assigned Permissions -->
        <div class="mb-4">
          <h6 class="mb-3">
            <i data-lucide="shield" class="icon-sm me-2"></i>Assigned Permissions
            <span class="badge bg-primary ms-2">{{ $role->permissions->count() }}</span>
          </h6>

          @if($role->permissions->count() > 0)
          @php
          $groupedPermissions = $role->permissions->groupBy('category');
          @endphp

          @foreach($groupedPermissions as $category => $categoryPermissions)
          <div class="permission-category border">
            <div class="permission-category-header border bg-light">
              <div class="d-flex justify-content-between align-items-center">
                <span>
                  <i data-lucide="folder" class="icon-sm me-2"></i>
                  {{ ucfirst(str_replace('_', ' ', $category)) }}
                  <span class="badge bg-secondary ms-2">{{ $categoryPermissions->count() }}</span>
                </span>
              </div>
            </div>
            <div class="permission-category-body">
              <div class="row">
                @foreach($categoryPermissions as $permission)
                <div class="col-md-6 col-lg-4">
                  <div class="permission-item">
                    <div class="d-flex align-items-start">
                      <i data-lucide="check-circle" class="icon-sm text-success me-2 mt-1"></i>
                      <div>
                        <strong>{{ $permission->display_name }}</strong>
                        @if($permission->description)
                        <br><small class="text-muted">{{ $permission->description }}</small>
                        @endif
                        @if($permission->route_name)
                        <br><small class="text-info">
                          <span class="badge bg-{{ $permission->method === 'GET' ? 'success' : ($permission->method === 'POST' ? 'primary' : ($permission->method === 'PUT' ? 'warning' : ($permission->method === 'DELETE' ? 'danger' : 'secondary'))) }}">
                            {{ $permission->method }}
                          </span>
                          {{ $permission->route_name }}
                        </small>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
          </div>
          @endforeach
          @else
          <div class="alert alert-info">
            <i data-lucide="info" class="icon-sm me-2"></i>
            No permissions assigned to this role.
          </div>
          @endif
        </div>

        <!-- Assigned Users -->
        <div class="mb-4">
          <h6 class="mb-3">
            <i data-lucide="users" class="icon-sm me-2"></i>Users with this Role
            <span class="badge bg-info ms-2">{{ $role->users->count() }}</span>
          </h6>

          @if($role->users->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>User</th>
                  <th>Email</th>
                  <th>Status</th>
                  <th>Joined</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($role->users as $user)
                <tr>
                  <td class="align-content-center">
                    <div class="d-flex align-items-center">
                      @if($user->avatar)
                      <img src="{{ asset($user->avatar) }}" alt="Avatar" class="user-avatar me-2">
                      @else
                      <div class="d-flex justify-content-center align-items-center bg-secondary text-white rounded-circle me-2" style="width:40px; height:40px;">
                        <i data-lucide="user" class="icon-sm"></i>
                      </div>
                      @endif
                      <div>
                        <strong>{{ $user->name }}</strong>
                        @if($user->designation)
                        <br><small class="text-muted">{{ $user->designation }}</small>
                        @endif
                      </div>
                    </div>
                  </td>
                  <td class="align-content-center">{{ $user->email }}</td>
                  <td class="align-content-center">
                    @if($user->is_active && $user->email_verified_at)
                    <span class="badge bg-success">Active</span>
                    @elseif(!$user->is_active)
                    <span class="badge bg-danger">Inactive</span>
                    @else
                    <span class="badge bg-warning">Unverified</span>
                    @endif
                  </td>
                  <td class="align-content-center">{{ formatUserDate($user->created_at) }}</td>
                  <td class="align-content-center">
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-primary btn-sm">
                      <i data-lucide="eye" class="icon-sm"></i>
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @else
          <div class="alert alert-info">
            <i data-lucide="info" class="icon-sm me-2"></i>
            No users assigned to this role.
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection