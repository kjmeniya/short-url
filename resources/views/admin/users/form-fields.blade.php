{{-- Reusable form fields for user create/edit --}}
<div class="row">
    <div class="col-sm-6">
        <div class="mb-3">
            <label for="name" class="form-label">Full Name *</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                   id="name" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Enter full name" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-sm-6">
        <div class="mb-3">
            <label for="email" class="form-label">Email Address *</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                   id="email" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="Enter email address" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        @include('admin.partials.password-field', [
            'name' => 'password',
            'label' => 'Password' . (isset($user) ? '' : ' *'),
            'placeholder' => isset($user) ? 'Leave blank to keep current password' : 'Enter password',
            'required' => !isset($user),
            'showStrengthMeter' => true,
            'autocomplete' => 'new-password',
            'requirements' => [
                'length' => ['enabled' => true, 'min' => 8],
                'uppercase' => ['enabled' => false],
                'lowercase' => ['enabled' => false],
                'number' => ['enabled' => false],
                'special' => ['enabled' => false]
            ]
        ])
        @if(isset($user))
            <small class="form-text text-muted">Leave blank to keep current password</small>
        @endif
    </div>
    <div class="col-sm-6">
        @include('admin.partials.password-field', [
            'name' => 'password_confirmation',
            'label' => 'Confirm Password' . (isset($user) ? '' : ' *'),
            'placeholder' => isset($user) ? 'Confirm new password' : 'Confirm password',
            'required' => !isset($user),
            'showStrengthMeter' => false,
            'autocomplete' => 'new-password'
        ])
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <div class="mb-3">
            <label for="phone" class="form-label">Phone Number</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                   id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="Enter phone number">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-sm-6">
        <div class="mb-3">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', isset($user) && $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : '') }}">
            @error('date_of_birth')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="mb-3">
    <label for="address" class="form-label">Address</label>
    <textarea class="form-control @error('address') is-invalid @enderror" 
              id="address" name="address" rows="3" placeholder="Enter address">{{ old('address', $user->address ?? '') }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Avatar Upload with Cropper --}}
@include('admin.partials.image-cropper', [
    'inputId' => 'avatar',
    'label' => 'Profile Picture',
    'currentImage' => isset($user) && $user->avatar ? $user->avatar_url : null
])
