@extends('frontend.layouts.master')
@section('title','Privacy Policy')
@section('main-content')

<div class="container-fluid h-100 bg-black p-4">
  <h2 class="text-white fw-bold">Privacy Policy</h2>
  <div class="row align-items-center justify-content-center h-100 text-white">
      <div class="col-lg-12">
          {!! $data !!}
      </div>
  </div>
</div>


@endsection


@push("styles")

@endpush


@push("scripts")

@endpush

