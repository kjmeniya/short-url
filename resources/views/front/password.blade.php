@extends('front.layout.master')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 60vh;">
    <div class="card shadow-sm border-0" style="max-width: 400px; width: 100%;">
        <div class="card-body p-4 text-center">
            <div class="mb-4">
                <i data-lucide="lock" class="text-primary opacity-75" style="width: 56px; height: 56px;"></i>
            </div>
            <h4 class="fw-bold mb-3">Protected Link</h4>
            <p class="text-muted mb-4">This link is password protected. Please enter the password to continue.</p>
            
            <form action="{{ route('front.password.verify', ['code' => $code]) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <input type="password" name="password" class="form-control form-control-lg text-center bg-light @error('password') is-invalid @enderror" placeholder="Enter Password" autocomplete="current-password" required autofocus>
                    @error('password')
                    <div class="invalid-feedback fw-medium mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold rounded-pill">
                    Unlock <i data-lucide="arrow-right" class="icon-sm ms-2"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
