@extends('frontend.layouts.master')
@section('title','Program List')
@section('main-content')

 <!-- main -->

 <section class="programs-section" >
  <div class="container">
    <h1 class="section-title">OUR WORKOUT PROGRAMS</h1>
    <p class="section-subtitle">Choose from a variety of programs designed for every fitness level and goal.</p>

    <div class="program-cards-zigzag">

      <!-- Program 1 -->
      <div class="program-card-zigzag left">
        <img src="{{asset("asset/images/p3.png")}}" alt="Strength Training">
        <div class="program-content">
          <h2>Strength Training</h2>
          <p>Build muscle, improve posture, and boost metabolism with our expert-led strength sessions.</p>
          @if(Auth()->user())
              <a href="{{route('dashboard')}}" class="btn-login">View Program</a>
          @else
              <button onclick="openLoginModal()" class="btn-login">View Program</button>
          @endif
        </div>
      </div>
    
      <!-- Program 2 -->
      <div class="program-card-zigzag right">
        <img src="{{asset("asset/images/cardio.jpg")}}" alt="Cardio Burn" >

        <div class="program-content">
          <h2>Cardio Burn</h2>
          <p>High-energy sessions focused on fat loss, endurance, and heart health. Suitable for all levels.</p>
          @if(Auth()->user())
              <a href="{{route('dashboard')}}" class="btn-login">View Program</a>
          @else
              <button onclick="openLoginModal()" class="btn-login">View Program</button>
          @endif
        </div>
     
      </div>
    
      <!-- Program 3 -->
      <div class="program-card-zigzag left">
        <img src="{{asset("asset/images/yoga.png")}}" alt="Yoga & Flexibility" >
        <div class="program-content">
          <h2>Yoga & Flexibility</h2>
          <p>Stretch, strengthen, and relax with our yoga classes designed to improve flexibility and balance.</p>
          @if(Auth()->user())
              <a href="{{route('dashboard')}}" class="btn-login">View Program</a>
          @else
              <button onclick="openLoginModal()" class="btn-login">View Program</button>
          @endif
        </div>
      </div>
    
      <!-- Program 4 -->
      <div class="program-card-zigzag right" >
        <img src="{{asset("asset/images/p1.png")}}" alt="HIIT Blaster" >

        <div class="program-content">
          <h2>HIIT Blaster</h2>
          <p>Short and intense workouts to burn fat fast. Ideal for busy lifestyles and fast results.</p>
          @if(Auth()->user())
              <a href="{{route('dashboard')}}" class="btn-login">View Program</a>
          @else
              <button onclick="openLoginModal()" class="btn-login">View Program</button>
          @endif
        </div>
      </div>     
    </div>      
  </div>
</section>
@endsection


@push("styles")
<link rel="stylesheet" href="{{asset("asset/css/programlist.css")}}" />
@endpush


@push("scripts")

@endpush

