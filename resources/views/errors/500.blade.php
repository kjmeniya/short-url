@extends('errors.layout.master')

@section('content')
<div class="row w-100 mx-0 auth-page">
  <div class="col-md-8 col-xl-6 mx-auto d-flex flex-column align-items-center">
    <img src="{{ url('build/images/others/500.svg') }}" class="img-fluid mb-2" alt="500" style="max-width: 400px;">
    <h1 class="fw-bolder mb-22 mt-2 fs-80px text-secondary">500</h1>
    <h4 class="mb-2">Internal server error</h4>
    <h6 class="text-secondary mb-3 text-center">Oops!! There was an error. Please try again later.</h6>
    <a href="{{ url('/') }}"><i data-lucide="arrow-left" class="w-15px"></i> Back to home</a>
  </div>
</div>
@endsection