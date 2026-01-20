@extends('frontend.layouts.master')
@section('title','Terms and Condition')
@section('main-content')

<div class="container-fluid h-100 bg-black p-4">
  <div class="faqs-page ">
    <h2 class="text-white fw-bold mb-4">Frequently Asked Questions​</h2>
    <div class="row align-items-center justify-content-center h-100 text-white mx-auto ">
        <div class="col-lg-12 p-0">
          <div class="accordion" id="accordionExample">
            @foreach ($faqs as $data)
              <div class="accordion-item mb-4">
                <h2 class="accordion-header" id="faq-{{$data->id}}">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{$data->id}}" aria-expanded="false"  aria-controls="collapse-{{$data->id}}">
                    {{$data->title}}
                  </button>
                </h2>
                <div id="collapse-{{$data->id}}" class="accordion-collapse collapse " aria-labelledby="faq-{{$data->id}}" data-bs-parent="#accordionExample">
                  <div class="accordion-body">
                    {!! $data->description !!}  
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
    </div>
  </div>
</div>


@endsection


@push("styles")

@endpush


@push("scripts")

@endpush

