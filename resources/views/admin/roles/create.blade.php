@extends('admin.layout.master')

@section('title', $title ?? 'Create Role')
@section('description', $description ?? 'Create new user roles with custom permissions')
@section('keywords', $keywords ?? 'create role, new role, role creation, permissions')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
  .permission-category-header {
    padding: 0.75rem 1rem;
    cursor: pointer;
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
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create Role</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
          <h6 class="card-title mb-0">Create New Role</h6>
          <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
            <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back to Roles
          </a>
        </div>

        <form action="{{ route('admin.roles.store') }}" method="POST" id="roleForm">
          @csrf

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="name" class="form-label">Role Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                  id="name" name="name" value="{{ old('name') }}"
                  placeholder="e.g., content_manager" required>
                <div class="form-text">Use lowercase letters and underscores only</div>
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label for="display_name" class="form-label">Display Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('display_name') is-invalid @enderror"
                  id="display_name" name="display_name" value="{{ old('display_name') }}"
                  placeholder="e.g., Content Manager" required>
                @error('display_name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror"
              id="description" name="description" rows="3"
              placeholder="Brief description of this role...">{{ old('description') }}</textarea>
            @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="mb-3">
            <div class="form-check form-switch">
              <input class="form-check-input @error('is_active') is-invalid @enderror"
                type="checkbox" id="is_active" name="is_active" value="1"
                {{ old('is_active', true) ? 'checked' : '' }}>
              <label class="form-check-label" for="is_active">
                Active Role
              </label>
              @error('is_active')
              <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
          <hr>
          <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
              <div>
                <h5 class="form-label mb-0">Permissions</h5>
                <span class="text-muted">Select permissions for this role</span>
              </div>
              <div>
                <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllPermissions">
                  <i data-lucide="check-square" class="icon-sm me-1"></i>Select All
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllPermissions">
                  <i data-lucide="square" class="icon-sm me-1"></i>Deselect All
                </button>
              </div>
            </div>

            @if($permissions->count() > 0)
            @foreach($permissions as $category => $categoryPermissions)
            <div class="border rounded permission-category mb-3">
              <div class="permission-category-header bg-light rounded border" aria-expanded="true">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="category-toggle" data-bs-toggle="collapse" data-bs-target="#category-{{ Str::slug($category) }}">
                    <i data-lucide="folder" class="icon-sm me-2"></i>
                    {{ ucfirst(str_replace('admin_', '', $category)) }}
                    <span class="badge bg-secondary ms-2">{{ count($categoryPermissions) }}</span>
                  </span>
                  <div class="d-flex align-items-center">
                    <label class="form-check-label select-all-category me-3 text-primary cursor-pointer"
                      for="select-all-{{ Str::slug($category) }}">
                      <input type="checkbox" class="form-check-input select-all-category-checkbox me-1 cursor-pointer"
                        id="select-all-{{ Str::slug($category) }}"
                        data-category="{{ Str::slug($category) }}">
                      Select All
                    </label>
                    <i data-lucide="chevron-down" class="icon-sm category-toggle"
                      data-bs-toggle="collapse"
                      data-bs-target="#category-{{ Str::slug($category) }}"></i>
                  </div>
                </div>
              </div>

              <div class="collapse" id="category-{{ Str::slug($category) }}">
                <div class="permission-category-body">
                  <div class="row">
                    @foreach($categoryPermissions as $permission)
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                      <div class="permission-item">
                        <div class="form-check">
                          <input class="form-check-input permission-checkbox cursor-pointer"
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permission['id'] }}"
                            id="permission-{{ $permission['id'] }}"
                            data-category="{{ Str::slug($category) }}"
                            {{ in_array($permission['id'], old('permissions', [])) ? 'checked' : '' }}>
                          <label class="form-check-label cursor-pointer" for="permission-{{ $permission['id'] }}">
                            <strong>{{ $permission['display_name'] }}</strong>
                            @if($permission['description'])
                            <br><small class="text-muted">{{ $permission['description'] }}</small>
                            @endif
                            @if($permission['route_name'])
                            <br><small class="text-secondary">{{ $permission['method'] }} {{ $permission['route_name'] }}</small>
                            @endif
                          </label>
                        </div>
                      </div>
                    </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
            @endforeach
            @else
            <div class="alert alert-info">
              <i data-lucide="info" class="icon-sm me-2"></i>
              No permissions available. Please sync permissions first.
            </div>
            @endif

            @error('permissions')
            <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
          </div>

          <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
              <i data-lucide="x" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Cancel</span>
              <span class="d-sm-none">Cancel</span>
            </a>
            <button type="submit" class="btn btn-primary btn-sm">
              <i data-lucide="save" class="icon-sm me-1"></i>
              <span class="d-none d-sm-inline">Create Role</span>
              <span class="d-sm-none">Create</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush

@push('custom-scripts')
<script>
  $(document).ready(function() {
    // Select/Deselect all permissions
    $('#selectAllPermissions').on('click', function() {
      $('.permission-checkbox').prop('checked', true);
      $('.select-all-category-checkbox').prop('checked', true);
    });

    $('#deselectAllPermissions').on('click', function() {
      $('.permission-checkbox').prop('checked', false);
      $('.select-all-category-checkbox').prop('checked', false);
    });

    // Select/Deselect all permissions in a category
    $('.select-all-category-checkbox').on('change', function() {
      const category = $(this).data('category');
      const isChecked = $(this).is(':checked');
      $(`.permission-checkbox[data-category="${category}"]`).prop('checked', isChecked);
    });

    // Update category select-all checkbox when individual permissions change
    $('.permission-checkbox').on('change', function() {
      const category = $(this).data('category');
      const totalInCategory = $(`.permission-checkbox[data-category="${category}"]`).length;
      const checkedInCategory = $(`.permission-checkbox[data-category="${category}"]:checked`).length;

      const categoryCheckbox = $(`.select-all-category-checkbox[data-category="${category}"]`);

      if (checkedInCategory === 0) {
        categoryCheckbox.prop('checked', false).prop('indeterminate', false);
      } else if (checkedInCategory === totalInCategory) {
        categoryCheckbox.prop('checked', true).prop('indeterminate', false);
      } else {
        categoryCheckbox.prop('checked', false).prop('indeterminate', true);
      }
    });

    // Form validation
    $('#roleForm').on('submit', function(e) {
      const name = $('#name').val().trim();
      const displayName = $('#display_name').val().trim();

      if (!name || !displayName) {
        e.preventDefault();
        Swal.fire('Error!', 'Please fill in all required fields.', 'error');
        return false;
      }

      // Validate role name format
      const namePattern = /^[a-z_]+$/;
      if (!namePattern.test(name)) {
        e.preventDefault();
        Swal.fire('Error!', 'Role name must contain only lowercase letters and underscores.', 'error');
        return false;
      }
    });

    // Auto-generate display name from name
    $('#name').on('input', function() {
      const name = $(this).val();
      if (name && !$('#display_name').val()) {
        const displayName = name.split('_').map(word =>
          word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
        $('#display_name').val(displayName);
      }
    });
  });
</script>
@endpush