@extends('admin.layout.master')

@section('title', $title ?? 'Sync Permissions')
@section('description', $description ?? 'Synchronize system permissions with application routes')
@section('keywords', $keywords ?? 'sync permissions, routes, controllers, system permissions')

@push('plugin-styles')
<link href="{{ asset('build/plugins/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" />
<style>
  .sync-card {
    border-radius: 0.375rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
  }
  
  .sync-status {
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
  }
  
  .sync-status.success {
    background-color: #d1e7dd;
    border: 1px solid #badbcc;
    color: #0f5132;
  }
  
  .sync-status.error {
    background-color: #f8d7da;
    border: 1px solid #f5c2c7;
    color: #842029;
  }
  
  .sync-status.info {
    background-color: #d1ecf1;
    border: 1px solid #b8daff;
    color: #055160;
  }
  
  .permission-preview {
    max-height: 400px;
    overflow-y: auto;
    border-radius: 0.375rem;
    padding: 1rem;
  }
  
  .permission-item {
    padding: 0.5rem;
    border-bottom: 1px solid #f8f9fa;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
  }
  
  .permission-item:last-child {
    border-bottom: none;
  }
  
  .permission-item.new {
    background-color: #d1e7dd;
    border-left: 4px solid #198754;
  }
  
  .loading-spinner {
    display: none;
  }
</style>
@endpush

@section('content')
<nav class="page-breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">Permissions</a></li>
    <li class="breadcrumb-item active" aria-current="page">Sync Permissions</li>
  </ol>
</nav>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
          <h6 class="card-title mb-0">Sync Permissions with Routes</h6>
          <div class="d-flex gap-2">
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm">
              <i data-lucide="arrow-left" class="icon-sm me-1"></i>Back to Permissions
            </a>
          </div>
        </div>

        <!-- Sync Information -->
        <div class="sync-card border bg-light">
          <h6 class="mb-3">
            <i data-lucide="info" class="icon-sm me-2"></i>About Permission Synchronization
          </h6>
          <p class="text-muted mb-3">
            This tool automatically scans your application's routes and creates corresponding permissions in the database. 
            It helps ensure that all your application's functionality is properly protected by the permission system.
          </p>
          
          <div class="row">
            <div class="col-md-6">
              <h6 class="mb-2">What gets synced:</h6>
              <ul class="text-muted small">
                <li>All named routes in your application</li>
                <li>HTTP methods (GET, POST, PUT, PATCH, DELETE)</li>
                <li>Route categories based on route names</li>
                <li>Automatic display names and descriptions</li>
              </ul>
            </div>
            <div class="col-md-6">
              <h6 class="mb-2">What gets skipped:</h6>
              <ul class="text-muted small">
                <li>Authentication routes (login, register, etc.)</li>
                <li>Password reset routes</li>
                <li>System routes (storage, debugbar, etc.)</li>
                <li>Routes with parameters (except admin routes)</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Sync Status -->
        <div id="syncStatus" style="display: none;"></div>

        <!-- Sync Controls -->
        <div class="d-flex justify-content-center mb-4">
          <button type="button" id="syncPermissions" class="btn btn-warning btn-lg">
            <i data-lucide="refresh-cw" class="icon-sm me-2"></i>
            <span class="sync-text">Sync Permissions Now</span>
            <div class="loading-spinner spinner-border spinner-border-sm ms-2" role="status" style="display: none;">
              <span class="visually-hidden">Loading...</span>
            </div>
          </button>
        </div>

        <!-- Sync Results -->
        <div id="syncResults" style="display: none;">
          <h6 class="mb-3">
            <i data-lucide="list" class="icon-sm me-2"></i>Sync Results
          </h6>
          <div class="row">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">
                    <i data-lucide="plus-circle" class="icon-sm me-2 text-success"></i>
                    New Permissions Created
                    <span id="newPermissionsCount" class="badge bg-success ms-2">0</span>
                  </h6>
                </div>
                <div class="card-body p-0">
                  <div id="newPermissionsList" class="permission-preview border">
                    <div class="text-center text-muted p-3">
                      No new permissions to display
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card">
                <div class="card-header">
                  <h6 class="mb-0">
                    <i data-lucide="check-circle" class="icon-sm me-2 text-info"></i>
                    Sync Summary
                  </h6>
                </div>
                <div class="card-body">
                  <div id="syncSummary">
                    <div class="text-center text-muted">
                      Run sync to see summary
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Warning Notice -->
        <div class="alert alert-warning mt-4">
          <div class="d-flex">
            <i data-lucide="alert-triangle" class="icon-sm me-2 mt-1"></i>
            <div>
              <strong>Important Notes:</strong>
              <ul class="mb-0 mt-2">
                <li>This operation is safe and will not delete existing permissions</li>
                <li>Only new permissions will be created based on your current routes</li>
                <li>Existing permissions will be updated if their route information has changed</li>
                <li>You may need to assign new permissions to appropriate roles after syncing</li>
              </ul>
            </div>
          </div>
        </div>
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
    $('#syncPermissions').on('click', function() {
      const button = $(this);
      const syncText = button.find('.sync-text');
      const spinner = button.find('.loading-spinner');
      const statusDiv = $('#syncStatus');
      const resultsDiv = $('#syncResults');
      
      // Show loading state
      button.prop('disabled', true);
      syncText.text('Syncing...');
      spinner.show();
      statusDiv.hide();
      resultsDiv.hide();
      
      // Make AJAX request
      $.ajax({
        url: "{{ route('admin.permissions.sync.process') }}",
        type: 'POST',
        data: {
          _token: "{{ csrf_token() }}"
        },
        success: function(response) {
          if (response.success) {
            // Show success status
            statusDiv.html(`
              <div class="sync-status success">
                <div class="d-flex align-items-center">
                  <i data-lucide="check-circle" class="icon-sm me-2"></i>
                  <div>
                    <strong>Sync Completed Successfully!</strong>
                    <br><small>${response.message}</small>
                  </div>
                </div>
              </div>
            `).show();
            
            // Update results
            updateSyncResults(response);
            resultsDiv.show();
            
            // Show success notification
            Swal.fire({
              icon: 'success',
              title: 'Sync Completed!',
              text: response.message,
              timer: 3000,
              showConfirmButton: false,
              didOpen: () => {
                if (typeof lucide !== 'undefined') {
                  lucide.createIcons();
                }
              }
            });
            
          } else {
            // Show error status
            statusDiv.html(`
              <div class="sync-status error">
                <div class="d-flex align-items-center">
                  <i data-lucide="x-circle" class="icon-sm me-2"></i>
                  <div>
                    <strong>Sync Failed!</strong>
                    <br><small>${response.message}</small>
                  </div>
                </div>
              </div>
            `).show();
            
            Swal.fire({
              icon: 'error',
              title: 'Sync Failed!',
              text: response.message
            });
          }
        },
        error: function(xhr) {
          const response = xhr.responseJSON;
          const message = response?.message || 'An unexpected error occurred during sync.';
          
          // Show error status
          statusDiv.html(`
            <div class="sync-status error">
              <div class="d-flex align-items-center">
                <i data-lucide="x-circle" class="icon-sm me-2"></i>
                <div>
                  <strong>Sync Error!</strong>
                  <br><small>${message}</small>
                </div>
              </div>
            </div>
          `).show();
          
          Swal.fire({
            icon: 'error',
            title: 'Sync Error!',
            text: message,
            didOpen: () => {
              if (typeof lucide !== 'undefined') {
                lucide.createIcons();
              }
            }
          });
        },
        complete: function() {
          // Reset button state
          button.prop('disabled', false);
          syncText.text('Sync Permissions Now');
          spinner.hide();
        }
      });
    });
    
    function updateSyncResults(response) {
      // Update new permissions count
      $('#newPermissionsCount').text(response.synced_count || 0);
      
      // Update new permissions list
      const newPermissionsList = $('#newPermissionsList');
      if (response.new_permissions && response.new_permissions.length > 0) {
        let html = '';
        response.new_permissions.forEach(function(permission) {
          html += `
            <div class="permission-item new">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <strong>${permission.display_name}</strong>
                  <br><small class="text-muted">${permission.name}</small>
                </div>
                <span class="badge bg-${getMethodColor(permission.method)}">${permission.method}</span>
              </div>
              ${permission.route_name ? `<small class="text-info">Route: ${permission.route_name}</small>` : ''}
            </div>
          `;
        });
        newPermissionsList.html(html);
      } else {
        newPermissionsList.html('<div class="text-center text-muted p-3">No new permissions created</div>');
      }
      
      // Update sync summary
      const syncSummary = $('#syncSummary');
      syncSummary.html(`
        <div class="row text-center">
          <div class="col-6">
            <div class="border rounded p-2">
              <h5 class="text-success mb-1">${response.synced_count || 0}</h5>
              <small class="text-muted">New Permissions</small>
            </div>
          </div>
          <div class="col-6">
            <div class="border rounded p-2">
              <h5 class="text-info mb-1">${new Date().toLocaleString()}</h5>
              <small class="text-muted">Last Sync</small>
            </div>
          </div>
        </div>
        <div class="mt-3">
          <small class="text-muted">
            <i data-lucide="clock" class="icon-sm me-1"></i>
            Sync completed at ${new Date().toLocaleTimeString()}
          </small>
        </div>
      `);
    }
    
    function getMethodColor(method) {
      const colors = {
        'GET': 'success',
        'POST': 'primary',
        'PUT': 'warning',
        'PATCH': 'info',
        'DELETE': 'danger'
      };
      return colors[method] || 'secondary';
    }
  });
</script>
@endpush
