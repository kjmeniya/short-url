@extends('errors.layout.master')

@section('content')
<div class="row w-100 mx-0 auth-page">
    <div class="col-md-8 col-xl-6 mx-auto d-flex flex-column align-items-center">
        <!-- Using 404 image as fallback since 403.svg doesn't exist -->
        <img src="{{ url('build/images/others/403.avif') }}" class="img-fluid mb-2" alt="403" style="max-width: 300px;">
        <h1 class="fw-bolder mb-22 mt-2 fs-80px text-secondary">403</h1>
        <h4 class="mb-2">Access Forbidden</h4>
        <h6 class="text-secondary mb-3 text-center">Sorry, you do not have permission to access this page.</h6>
        <a href="{{ url('/') }}"><i data-lucide="arrow-left" class="w-15px"></i> Back to home</a>
    </div>
</div>
@endsection