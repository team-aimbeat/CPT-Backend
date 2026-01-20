@extends('frontend.layouts.master')
@section('title','Contact')
@section('main-content')



<section class="contact-container">
  <h1>Get In Touch With Us</h1>
  <div class="contact-card-wrapper">
    
   
    <!-- Google Map -->
    <div class="map-container">
      <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3783.6757250317833!2d73.85674347494142!3d18.50076967051125!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bc2c097f7a82869%3A0x3c5d6e9b6ff153ae!2sPune%2C%20Maharashtra!5e0!3m2!1sen!2sin!4v1713265564017!5m2!1sen!2sin"
        width="100%"
        height="100%"
        style="border:0;"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
      ></iframe>
    </div>
  </div>
  <div class="info-section">
    <!-- Head Office -->
    <div class="info-card">
      <i class="fas fa-map-marker-alt info-icon"></i>
      <div class="heading-info">
        <h3>Head Office</h3>
        <p>{{appSettingData('get')->address}}</p>
      </div>
    </div>
  
    <!-- Contact -->
    <a href="tel:{{appSettingData('get')->contact_number}}" class="info-card" >
      <i class="fas fa-phone-alt info-icon"></i>
      <div class="heading-info">
        <h3 class="contact">Contact</h3>
        <p class="number">{{appSettingData('get')->contact_number}}</p>
      </div>
    </a>
  
    <!-- Email -->
    <a href="mailto:{{appSettingData('get')->contact_email}}" class="info-card" >
      <i class="fas fa-envelope info-icon"></i>
      <div class="heading-info">
        <h3>Email</h3>
        <p>{{appSettingData('get')->contact_email}}</p>
      </div>
    </a>
  </div>
  <section class="contact-feedback-container">
    <!-- Contact Form -->
    <div class="form-box">
      <h2>Contact Us</h2>
      <p>We'd love to hear from you!</p>
      <form class="contact-form">
        {{csrf_field()}}
        <input type="text" name="name" placeholder="Your Name" required />
        <input type="email" name="email" placeholder="Your Email" required />
        <input type="text" name="subject" placeholder="Subject" required/>
        <textarea rows="5" name="message" placeholder="Your Message" required></textarea>
        <button type="submit" class="contact_btn">Send Message</button>
      </form>
    </div>
    </section>
</section>




@endsection


@push("styles")
<link rel="stylesheet" href="{{asset("asset/css/contact.css")}}" />
@endpush


@push("scripts")
<script>
  $( document ).ready(function() {
      $(".contact-form").validate({rules: {
      },
      errorClass: "error",
      validClass: "valid",
      errorElement: 'div',
      errorPlacement: function(error, element) {
          if(element.parent('.input-group').length) {
              error.insertAfter(element.parent());
          }else if(element.parent('.form-check').length) {
              error.insertAfter(element.parent());
          } else {
              error.insertAfter(element);
          }
      },
      onError : function(){
          $('.input-group.error-class').find('.help-block.form-error').each(function() {
              $(this).closest('.form-group').addClass('error-class').append($(this));
          });
      },
      submitHandler: function(form) {
          btnLoading($(".contact_btn"));
          $.ajax({
              type: "POST",
              url: "{{route('web.contact.submit')}}",
              data: $(form).serialize(),
              success: function(data)
              {
                  btnLoadingReset($(".contact_btn"));
                  if (data.status) {
                    $(".contact-form input, .contact-form textarea").val('');
                  }
                  else {
                    round_error_noti(data.message);
                  }
              },
              error: function (res) {
                  btnLoadingReset($(".contact_btn"));
                  let message = "Something went wrong";

                  let resData = res.responseJSON;
                  if(resData.errors){
                      message = Object.keys(resData.errors).map((key) => resData.errors[key]).join("<br>");
                  }
                  round_error_noti(message);
              }
          });
      }
      });
  });

  </script>
@endpush

