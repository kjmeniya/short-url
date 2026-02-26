@extends('admin.layout.master')

@section('title', $title ?? 'Permission Details')
@section('description', $description ?? 'View permission details and role assignments')
@section('keywords', $keywords ?? 'permission details, role assignments, access control')

@push('plugin-styles')
<style>
  .info-card {
    border-radius: 0.375rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .role-item {
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 0.5rem;
  }

  .method-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
  }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $permission->display_name }}</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
          <h6 class="card-title mb-0">Permission Details: {{ $permission->display_name }}</h6>
          <div class="d-flex gap-2">
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm">
              <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back to Permissions
            </a>
          </div>
        </div>

        <!-- Permission Information -->
        <div class="info-card border">
          <div class="row">
            <div class="col-md-6">
              <h6 class="mb-3">
                <i data-lucide="info" class="icon-sm me-2"></i>Permission Information
              </h6>
              <table class="table table-borderless">
                <tr>
                  <td class="fw-bold" style="width: 30%;">Permission Name:</td>
                  <td><code>{{ $permission->name }}</code></td>
                </tr>
                <tr>
                  <td class="fw-bold">Display Name:</td>
                  <td>{{ $permission->display_name }}</td>
                </tr>
                <tr>
                  <td class="fw-bold">HTTP Method:</td>
                  <td>
                    <span class="badge method-badge bg-{{ $permission->method === 'GET' ? 'success' : ($permission->method === 'POST' ? 'primary' : ($permission->method === 'PUT' ? 'warning' : ($permission->method === 'PATCH' ? 'info' : ($permission->method === 'DELETE' ? 'danger' : 'secondary')))) }}">
                      {{ $permission->method }}
                    </span>
                  </td>
                </tr>
                <tr>
                  <td class="fw-bold">Category:</td>
                  <td>
                    <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $permission->category)) }}</span>
                  </td>
                </tr>
                <tr>
                  <td class="fw-bold">Route Name:</td>
                  <td>
                    @if($permission->route_name)
                    <code>{{ $permission->route_name }}</code>
                    @else
                    <span class="text-muted">No route assigned</span>
                    @endif
                  </td>
                </tr>
                <tr>
                  <td class="fw-bold">Created:</td>
                  <td>{{ formatUserDateTime($permission->created_at) }}</td>
                </tr>
                <tr>
                  <td class="fw-bold">Updated:</td>
                  <td>{{ formatUserDateTime($permission->updated_at) }}</td>
                </tr>
              </table>
            </div>
            <div class="col-md-6">
              <h6 class="mb-3">
                <i data-lucide="file-text" class="icon-sm me-2"></i>Description
              </h6>
              <p class="text-muted">
                {{ $permission->description ?: 'No description provided.' }}
              </p>

              <div class="mt-4">
                <div class="row text-center">
                  <div class="col-12">
                    <div class="border rounded p-3">
                      <h4 class="text-primary mb-1">{{ $permission->roles->count() }}</h4>
                      <small class="text-muted">Roles with this Permission</small>
                    </div>
                  </div>
                </div>
              </div>

              @if($permission->route_name)
              <div class="mt-4">
                <h6 class="mb-2">
                  <i data-lucide="link" class="icon-sm me-2"></i>Route Information
                </h6>
                <div class="border rounded p-3">
                  <div class="d-flex align-items-center mb-2">
                    <span class="badge method-badge bg-{{ $permission->method === 'GET' ? 'success' : ($permission->method === 'POST' ? 'primary' : ($permission->method === 'PUT' ? 'warning' : ($permission->method === 'PATCH' ? 'info' : ($permission->method === 'DELETE' ? 'danger' : 'secondary')))) }} me-2">
                      {{ $permission->method }}
                    </span>
                    <code>{{ $permission->route_name }}</code>
                  </div>
                  @php
                  try {
                  $route = Route::getRoutes()->getByName($permission->route_name);
                  $uri = $route ? $route->uri() : 'Route not found';
                  } catch (Exception $e) {
                  $uri = 'Route not found';
                  }
                  @endphp
                  <small class="text-muted">URI: {{ $uri }}</small>
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>

        <!-- Assigned Roles -->
        <div class="mb-4">
          <h6 class="mb-3">
            <i data-lucide="users" class="icon-sm me-2"></i>Roles with this Permission
            <span class="badge bg-primary ms-2">{{ $permission->roles->count() }}</span>
          </h6>

          @if($permission->roles->count() > 0)
          <div class="row">
            @foreach($permission->roles as $role)
            <div class="col-md-6 col-lg-4 mb-3">
              <div class="role-item border">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <h6 class="mb-1">{{ $role->display_name }}</h6>
                    <small class="text-muted">{{ $role->name }}</small>
                  </div>
                  <div>
                    @if($role->is_active)
                    <span class="badge bg-success">Active</span>
                    @else
                    <span class="badge bg-danger">Inactive</span>
                    @endif
                  </div>
                </div>

                @if($role->description)
                <p class="text-muted small mb-2">{{ Str::limit($role->description, 100) }}</p>
                @endif

                <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex gap-2">
                    <small class="text-muted">
                      <i data-lucide="shield" class="icon-sm me-1"></i>
                      {{ $role->permissions->count() }} permissions
                    </small>
                    <small class="text-muted">
                      <i data-lucide="users" class="icon-sm me-1"></i>
                      {{ $role->users->count() }} users
                    </small>
                  </div>
                  <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-outline-primary btn-sm">
                    <i data-lucide="eye" class="icon-sm"></i>
                  </a>
                </div>
              </div>
            </div>
            @endforeach
          </div>
          @else
          <div class="alert alert-info">
            <i data-lucide="info" class="icon-sm me-2"></i>
            This permission is not assigned to any roles.
          </div>
          @endif
        </div>

        <!-- Users with this Permission (through roles) -->
        @php
        $usersWithPermission = collect();
        foreach($permission->roles as $role) {
        $usersWithPermission = $usersWithPermission->merge($role->users);
        }
        $usersWithPermission = $usersWithPermission->unique('id');
        @endphp

        <div class="mb-4">
          <h6 class="mb-3">
            <i data-lucide="user-check" class="icon-sm me-2"></i>Users with this Permission
            <span class="badge bg-info ms-2">{{ $usersWithPermission->count() }}</span>
          </h6>

          @if($usersWithPermission->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Id</th>
                  <th>User</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($usersWithPermission as $user)
                <tr>
                  <td class="align-content-center">{{ $user->id }}</td>
                  <td class="align-content-center">
                    <div class="d-flex align-items-center">
                      @if($user->avatar)
                      <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                      @else
                      <div class="d-flex justify-content-center align-items-center bg-secondary text-white rounded-circle me-2" style="width:32px; height:32px;">
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
                    @if($user->role_id && $user->role)
                    <span class="badge bg-primary">{{ $user->role->display_name }}</span>
                    @else
                    <span class="badge bg-secondary">{{ ucfirst($user->role) }}</span>
                    @endif
                  </td>
                  <td class="align-content-center">
                    @if($user->is_active && $user->email_verified_at)
                    <span class="badge bg-success">Active</span>
                    @elseif(!$user->is_active)
                    <span class="badge bg-danger">Inactive</span>
                    @else
                    <span class="badge bg-warning">Unverified</span>
                    @endif
                  </td>
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
            No users currently have this permission.
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection