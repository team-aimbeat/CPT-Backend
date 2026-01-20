@extends('frontend.layouts.master')
@section('title','About')
@section('main-content')
 <!-- main content -->

 <section class="about-hero">
  <h1 class="heading">WE ARE GYM AND FITNESS</h1>
  <p class="">We are committed to helping you achieve your fitness goals with passion, power, and purpose.</p>
</section>
<section>
  <div class="about-banner" style="background-image: url('{{asset("asset/images/home.png")}}');">
    {{-- <img src="{{asset("asset/images/home.png")}}" alt="Gym" class="banner-img" /> --}}
  </div>
</section>

<section class="our-story">
  <div class="story-container">
    <h2 class="heading">OUR STORY</h2>
    <p>
      Founded in 2015, WE ARE GYM AND FITNESS began as a small garage gym with a big dream — to empower every individual to become the strongest version of themselves. What started with a few dumbbells and a vision has now grown into a full-fledged fitness community. 
    </p>
    <p>
      We're not just a gym — we're a lifestyle. Our trainers are passionate, our members are motivated, and our space is built to push your limits. Whether you're a beginner or a seasoned athlete, you'll find your place here. 
    </p>
    <p>
      Join us, and be a part of a movement that transforms bodies, minds, and lives.
    </p>
  </div>
</section>
<section class="our-vision">
  <div class="vision-heading">
    <h2 class="heading">Our Vision</h2>
  </div>
  <div class="vision-points">
    <div class="vision-item">
      <i class="fas fa-dumbbell"></i>
      <p>Empower individuals through strength and discipline.</p>
    </div>
    <div class="vision-item">
      <i class="fas fa-heartbeat"></i>
      <p>Promote a healthy and active lifestyle for all.</p>
    </div>
    <div class="vision-item">
      <i class="fas fa-users"></i>
      <p>Build a supportive fitness community.</p>
    </div>
    <div class="vision-item">
      <i class="fas fa-bullseye"></i>
      <p>Help members achieve their personal fitness goals.</p>
    </div>
    <div class="vision-item">
      <i class="fas fa-bullseye"></i>
      <p>Help members achieve their personal fitness goals.</p>
    </div>
    <div class="vision-item">
      <i class="fas fa-bullseye"></i>
      <p>Help members achieve their personal fitness goals.</p>
    </div>
  </div>
</section>
<section class="our-clients-reviews">
  <div class="clients-heading">
    <h2 class="heading">OUR CLIENTS</h2>
  </div>

  <div class="clients-review-boxes">
    <div class="client-box">
      <img src="{{asset("asset/images/plan.jpg")}}" alt="Client 1">
      <p>"This team is amazing! We loved working with them."</p>
      <h4>— Client 1</h4>
    </div>

    <div class="client-box">
      <img src="{{asset("asset/images/plan.jpg")}}" alt="Client 2" >
      <p>"Great experience and excellent results."</p>
      <h4>— Client 2</h4>
    </div>

    <div class="client-box">
      <img src="{{asset("asset/images/plan.jpg")}}" alt="Client 3">
      <p>"Very professional and always deliver on time."</p>
      <h4>— Client 3</h4>
    </div>

    <div class="client-box">
      <img src="{{asset("asset/images/plan.jpg")}}" alt="Client 4">
      <p>"Highly recommend their services. 10/10!"</p>
      <h4>— Client 4</h4>
    </div>
  </div>
</section>


@endsection


@push("styles")
<link rel="stylesheet" href="{{asset("asset/css/about.css")}}" />
@endpush


@push("scripts")
<script>
 

  </script>
@endpush

