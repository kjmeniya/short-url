<div class="row">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-start mb-3">
      <div>
        <h5 class="mb-1">{{ $setting->name }}</h5>
        <p class="text-muted mb-0">{{ $setting->description ?: 'No description provided' }}</p>
      </div>
      <div class="d-flex gap-2">
        <span class="badge {{ $setting->is_active ? 'bg-success' : 'bg-secondary' }}">
          {{ $setting->is_active ? 'Active' : 'Inactive' }}
        </span>
        <span class="badge {{ $setting->is_public ? 'bg-info' : 'bg-warning' }}">
          {{ $setting->is_public ? 'Public' : 'Private' }}
        </span>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card border">
          <div class="card-body text-center py-3">
            <i data-lucide="key" class="icon-md text-primary mb-2"></i>
            <h6 class="card-title mb-1">Setting Key</h6>
            <p class="text-muted mb-0 small">{{ $setting->key }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card border">
          <div class="card-body text-center py-3">
            <i data-lucide="type" class="icon-md text-info mb-2"></i>
            <h6 class="card-title mb-1">Type</h6>
            <p class="text-muted mb-0 small">{{ ucfirst($setting->type) }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card border">
          <div class="card-body text-center py-3">
            <i data-lucide="folder" class="icon-md text-warning mb-2"></i>
            <h6 class="card-title mb-1">Group</h6>
            <p class="text-muted mb-0 small">{{ ucfirst($setting->group) }}</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card border">
          <div class="card-body text-center py-3">
            <i data-lucide="hash" class="icon-md text-secondary mb-2"></i>
            <h6 class="card-title mb-1">Sort Order</h6>
            <p class="text-muted mb-0 small">{{ $setting->sort_order }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="mb-4">
      <h6 class="mb-3">Current Value</h6>
      @if($setting->type === 'boolean')
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" disabled
          {{ $setting->value ? 'checked' : '' }}>
        <label class="form-check-label">
          {{ $setting->value ? 'Enabled' : 'Disabled' }}
        </label>
      </div>
      @elseif($setting->type === 'select' && $setting->options)
      <div class="input-group">
        <input type="text" class="form-control" value="{{ $setting->value }}" readonly>
        <span class="input-group-text">
          {{ $setting->options[$setting->value] ?? 'Unknown' }}
        </span>
      </div>
      @elseif($setting->type === 'textarea')
      <textarea class="form-control" rows="3" readonly>{{ $setting->value }}</textarea>
      @else
      <input type="text" class="form-control" value="{{ $setting->value ?: 'Not set' }}" readonly>
      @endif
    </div>

    @if($setting->type === 'select' && $setting->options)
    <div class="mb-4">
      <h6 class="mb-3">Available Options</h6>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Value</th>
              <th>Label</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($setting->options as $optionValue => $optionLabel)
            <tr>
              <td><code>{{ $optionValue }}</code></td>
              <td>{{ $optionLabel }}</td>
              <td>
                @if($setting->value == $optionValue)
                <span class="badge bg-success">Selected</span>
                @else
                <span class="badge bg-light text-dark">Available</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

    <div class="row">
      <div class="col-md-6">
        <h6 class="mb-3">Setting Information</h6>
        <div class="list-group list-group-flush">
          <div class="list-group-item d-flex justify-content-between px-0">
            <span>ID:</span>
            <span>{{ $setting->id }}</span>
          </div>
          <div class="list-group-item d-flex justify-content-between px-0">
            <span>Key:</span>
            <code>{{ $setting->key }}</code>
          </div>
          <div class="list-group-item d-flex justify-content-between px-0">
            <span>Type:</span>
            <span class="badge bg-secondary">{{ $setting->type }}</span>
          </div>
          <div class="list-group-item d-flex justify-content-between px-0">
            <span>Group:</span>
            <span class="badge bg-primary">{{ $setting->group }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <h6 class="mb-3">Status & Dates</h6>
        <div class="list-group list-group-flush">
          <div class="list-group-item d-flex justify-content-between px-0">
            <span>Public:</span>
            <span class="badge {{ $setting->is_public ? 'bg-success' : 'bg-warning' }}">
              {{ $setting->is_public ? 'Yes' : 'No' }}
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between px-0">
            <span>Active:</span>
            <span class="badge {{ $setting->is_active ? 'bg-success' : 'bg-danger' }}">
              {{ $setting->is_active ? 'Yes' : 'No' }}
            </span>
          </div>
          <div class="list-group-item d-flex justify-content-between px-0">
            <span>Created:</span>
            <span>{{ formatUserDateTime($setting->created_at) }}</span>
          </div>
          <div class="list-group-item d-flex justify-content-between px-0">
            <span>Updated:</span>
            <span>{{ formatUserDateTime($setting->updated_at) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Store setting ID for edit button
  $('#editFromViewBtn').data('setting-id', <?= $setting->id ?>);
</script>