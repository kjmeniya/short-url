@extends('admin.layout.master')

@section('title', $title ?? 'Edit Plan')
@section('description', $description ?? 'Edit subscription plan.')

@push('plugin-styles')
@endpush

@section('content')
<nav class="page-breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.plans.index') }}">Plans</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-12 stretch-card">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Edit Plan: {{ $plan->name }}</h6>

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="name">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $plan->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="slug">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $plan->slug) }}" required>
                            @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $plan->description) }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="price">Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $plan->price) }}" required>
                            @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="sort_order">Sort Order <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control @error('sort_order') is-invalid @enderror" id="sort_order" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}" required>
                            @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="is_active">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active" required>
                                <option value="1" {{ old('is_active', $plan->is_active) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', $plan->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h6 class="mt-4 mb-3">Plan Features</h6>
                    <div id="features-container">
                        @if(old('features'))
                        @foreach(old('features') as $index => $feature)
                        <div class="row feature-row mb-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="features[{{ $index }}][name]" value="{{ $feature['name'] }}" placeholder="Feature Name (e.g., max_links)">
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="features[{{ $index }}][value]" value="{{ $feature['value'] }}" placeholder="Feature Value (e.g., 100 or true)">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-icon remove-feature">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                        @elseif($plan->features->count() > 0)
                        @foreach($plan->features as $index => $feature)
                        <div class="row feature-row mb-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="features[{{ $index }}][name]" value="{{ $feature->feature_name }}" placeholder="Feature Name (e.g., max_links)">
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="features[{{ $index }}][value]" value="{{ $feature->feature_value }}" placeholder="Feature Value (e.g., 100 or true)">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-icon remove-feature">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                        @else
                        <div class="row feature-row mb-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="features[0][name]" placeholder="Feature Name (e.g., max_links)">
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="features[0][value]" placeholder="Feature Value (e.g., 100 or true)">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger btn-icon remove-feature">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-feature">
                        <i data-lucide="plus" class="icon-sm me-1"></i> Add Feature
                    </button>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary me-2">Update Plan</button>
                        <a href="{{ route('admin.plans.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('plugin-scripts')
@endpush

@push('custom-scripts')
<script>
    $(document).ready(function() {
        // Auto-generate slug (optional on edit, but let's keep it if user manually types in slug let them)
        $('#name').on('keyup', function() {
            if (!$('#slug').val()) {
                var name = $(this).val();
                var slug = name.toLowerCase().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '');
                $('#slug').val(slug);
            }
        });

        // Add feature row
        let featureIndex = <?php echo old('features') ? count(old('features')) : max($plan->features->count(), 1) ?>;
        $('#add-feature').on('click', function() {
            const row = `
                <div class="row feature-row mb-2">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="features[${featureIndex}][name]" placeholder="Feature Name (e.g., custom_alias)">
                    </div>
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="features[${featureIndex}][value]" placeholder="Feature Value (e.g., true)">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-icon remove-feature">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#features-container').append(row);
            featureIndex++;
            lucide.createIcons();
        });

        // Remove feature row
        $(document).on('click', '.remove-feature', function() {
            $(this).closest('.feature-row').remove();
        });
    });
</script>
@endpush