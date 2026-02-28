@extends('front.layout.blank')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 60vh;">
    <div class="card shadow-sm border-0" style="max-width: 400px; width: 100%;">
        <div class="card-body p-4 text-center">
            <div class="mb-4">
                <i data-lucide="lock" class="text-primary opacity-75" style="width: 56px; height: 56px;"></i>
            </div>
            <h4 class="fw-bold mb-3">Protected Link</h4>
            <p class="text-muted mb-4">This link is password protected. Please enter the password to continue.</p>

            <form action="{{ route('front.password.verify', ['code' => $code]) }}" method="POST" novalidate>
                @csrf
                <div class="mb-4">
                    @include('admin.partials.password-field', [
                    'name' => 'password',
                    'label' => '',
                    'placeholder' => 'Enter password',
                    'required' => true,
                    'showStrengthMeter' => false,
                    'autocomplete' => 'current-password'
                    ])
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    Unlock <i data-lucide="arrow-right" class="icon-sm"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('plugin-scripts')
<script src="{{ asset('build/plugins/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('build/plugins/jquery-validation/jquery.validate.min.js') }}"></script>
@endpush

@push('custom-scripts')
@vite(['resources/js/admin/password-utils.js'])
@endpush