@extends('frontend.layouts.master')
@section('title','Home Page')
@section('main-content')
<!-- Main Content -->
<main class="main-hero">
    <section class="home-banner" style="background-image: url('{{asset("asset/images/gym.png")}}');">
      <div class="main-text" style="background-image: url('{{asset("asset/images/Ellipse 1.png")}}');" >
        <h1>Transform Your Fitness<br /><span class="journey-text">Journey</span></h1>
        <p class="download-text">Download the app now and start your<br /> transformation today!</p>
        <div class="mobile-app">
            <div class="get-our-app">Get our app</div>
            <div class="decive-type">
              <a href="{{ SettingData('APPVERSION', 'APPVERSION_PLAYSTORE_URL') }}" target="_blank">
                  <img src="{{asset("asset/images/playstore.png")}}" alt="Footer Logo" class="footer-img1" />     
              </a>
              <a href="{{ SettingData('APPVERSION', 'APPVERSION_APPSTORE_URL') }}" target="_blank">
                  <img src="{{asset("asset/images/apple.png")}}" alt="Footer Logo" class="footer-img2" />
              </a>
            </div>
        </div>
      </div>
    </section>
    {{-- <img src="{{asset("asset/images/home.png")}}" alt="Gym" class="banner-img" /> --}}
    <section class="card-split-section">
      <h2 class="overlay-text">One Membership Endless<br />Fitness Options</h2>
      <div class="card-split-layout">
        <div class="left-column">
            <div class="img-box">
                <img src="{{asset("asset/images/one.png")}}" alt="Card 1" />
            </div>
            <div class="img-box">
                
                <img src="{{asset("asset/images/two.png")}}" alt="Card 2"/>
            </div>
            <div class="img-box">
                <img src="{{asset("asset/images/three.png")}}" alt="Card 3" />
            </div>
            
        </div>
        <div class="right-column">
          <div class="img-box">
              <img src="{{asset("asset/images/four.png")}}" alt="Card 4" />
          </div>
          <div class="img-box">
              <img src="{{asset("asset/images/five.png")}}" alt="Card 5" />
          </div>
        </div>
      </div>
    </section>
    

    <!-- Membership cards -->
    <section class="membership-cards-section">
      <div class="explore-btn-wrapper">
        <button class="explore-btn">Explore Our Pass</button>
      </div>
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
        {{-- <div class="membership-card">
          <img src="{{asset("asset/images/p2.png")}}" alt="Basic Plan" class="card-img" />
          <div class="membership-card-body">
              <p class="start">Start at</p>
              <p class="price">
                <span class="price-amount">0₹</span>
                <span class="price-duration">/ Month</span>
              </p>
              <p class="features-heading">Features included in this plan:</p>
              <ul class="features-list">
                <li><input type="checkbox" checked disabled /> Access to basic workouts</li>
                <li><input type="checkbox" checked disabled /> Community support</li>
                <li><input type="checkbox" checked disabled /> Trainer consultation</li>
                <li><input type="checkbox" checked disabled /> App Access</li>
              </ul>
              <button onclick="openLoginModal()">Get Started for Free</button>
          </div>
        </div>
        <div class="membership-card">
          <img src="{{asset("asset/images/p3.png")}}" alt="Basic Plan" class="card-img" />
          <div class="membership-card-body">
              <p class="start">Start at</p>
              <p class="price">
                <span class="price-amount">0₹</span>
                <span class="price-duration">/ Month</span>
              </p>
              <p class="features-heading">Features included in this plan:</p>
              <ul class="features-list">
                <li><input type="checkbox" checked disabled /> Access to basic workouts</li>
                <li><input type="checkbox" checked disabled /> Community support</li>
                <li><input type="checkbox" checked disabled /> Trainer consultation</li>
                <li><input type="checkbox" checked disabled /> App Access</li>
              </ul>
              <button onclick="openLoginModal()">Get Started for Free</button>
          </div> 
        </div>--}}
      </div>
    </section>      
    <section class="single-image-section">
      <div class="image-overlay-container" style="background-image: url('{{asset("asset/images/phone.png")}}');">
        
        <div class="details-box">
          <div class="info-box">
            <div class="image-overlay-text">Your favorite fitness </br>companion just a tap away.</div>
            <div class="image-overlay-text2">Kickstart your fitness transformation. Be part of the CPT!</div>
          </div>
          <div class="app-img">
            <img src="{{asset("asset/images/playstore.png")}}" alt="Overlay1 " class="overlay-image overlay-one" />
            <img src="{{asset("asset/images/apple.png")}}" alt="Overlay2" class="overlay-image overlay-two" />
          </div>
        </div>
        {{-- <img src="{{asset("asset/images/phone.png")}}" alt="Fitness Showcase" class="single-image" /> --}}
      </div>
    </section>
  </main>
    
@endsection


@push("styles")

@endpush


@push("scripts")

@endpush

