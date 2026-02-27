@extends('errors.layout.master')

@section('content')
<div class="row w-100 mx-0 auth-page">
  <div class="col-md-8 col-xl-6 mx-auto d-flex flex-column align-items-center">

    {{-- Icon --}}
    <div class="mb-4 mt-3" style="width:120px;height:120px;background:rgba(220,53,69,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;">
      <i data-lucide="clock" style="width:54px;height:54px;color:#dc3545;"></i>
    </div>

    <h1 class="fw-bolder mb-2 fs-80px text-secondary">410</h1>
    <h4 class="mb-2">Link Expired</h4>
    <h6 class="text-secondary mb-4 text-center">
      This short link has expired and is no longer available.
    </h6>

    <a href="{{ url('/') }}" class="btn btn-primary btn-sm px-4">
      <i data-lucide="arrow-left" class="w-15px me-1"></i> Back to Home
    </a>
  </div>
</div>
@endsection
