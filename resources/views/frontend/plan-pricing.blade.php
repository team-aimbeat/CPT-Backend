@extends('frontend.layouts.master')
@section('title','Plan Pricing')
@section('main-content')

  <!-- main -->
    <div class="pricing-container ">
        <h1 class="text-center">WORKOUT PLANS</h1>
        <div class="pricing-cards">

            @foreach ($workoutPackageList as $data)
                <div class="plan-card featured">
                    <h2>{{$data->name}}</h2>
                    <div class="plan-card-detail row">
                        <div class="col-md-4">
                            {!! $data->description !!}
                        </div>
                        <div class="col-md-4">
                            <p class="price">Starting At</p>
                            <p class="price">{{getPriceFormat($data->price)}} / {{($data->duration > 1) ?  $data->duration." Months" : $data->duration_unit}}</p>
                        </div>
                        <div class="col-md-4 text-center">
                            @if(Auth()->user())
                                <a href="{{route('dashboard')}}" class="btn-login">Get Started</a>
                            @else
                                <button onclick="openLoginModal()" class="btn-login">Get Started</button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>



<!-- Membership Plans Section -->

    <!-- Membership cards -->
    <section class="membership-cards-section">
        
        <h2 class="membership-heading">Flexible Pricing Plans</h2>
        <p class="membership-desc">
          Perfect for beginners. Includes access to basic workout plans and community support.
        </p>
        <div class="membership-cards">

          @foreach ($basicPackageList as $data)
            <div class="membership-card">
              <img src="{{ getSingleMedia($data,'package_image', false) }}" alt="Basic Plan" class="card-img"/>
              <div class="membership-card-body">
                  <p class="start">Start at</p>
                  <p class="price">
                    <span class="price-amount">{{getPriceFormat($data->price)}}</span>
                    <span class="price-duration">/ {{($data->duration > 1) ?  $data->duration." Months" : $data->duration_unit}}</span>
                  </p>
                  {!! $data->description !!}
                  @if(Auth()->user())
                    <a href="{{route('dashboard')}}" class="btn-login">Get Started for Free</a>
                  @else
                    <button onclick="openLoginModal()" class="btn-login">Get Started for Free</button>
                  @endif
              </div>
            </div>
          @endforeach

        </div>
      </section> 

@endsection


@push("styles")
<link rel="stylesheet" href="{{asset("asset/css/plan-pricing.css")}}" />
@endpush


@push("scripts")

@endpush

