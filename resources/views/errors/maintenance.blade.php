@extends('errors.layout.master')

@section('content')
<div class="row w-100 mx-0 auth-page">
  <div class="col-md-8 col-xl-6 mx-auto d-flex flex-column align-items-center">
    <img src="{{ url('build/images/others/maintenance.svg') }}" class="img-fluid mb-2" alt="Maintenance" style="max-width: 400px;">
    <h1 class="fw-bolder mb-22 mt-2 fs-80px text-secondary">503</h1>
    <h4 class="mb-2">Maintenance Mode Enabled</h4>
    <h6 class="text-secondary mb-3 text-center">{{ $message ?? 'We are currently performing scheduled maintenance. Please check back soon.' }}</h6>
    <a href="{{ url('/') }}" class="btn btn-sm btn-primary"><i data-lucide="refresh-cw" class="w-15px"></i> Try Again</a>
  </div>
</div>
@endsection

