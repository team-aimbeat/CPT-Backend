 <!-- Footer -->
 <footer class="main-footer">
    <div class="row">
        <div class="col-lg-2 col-md-2 ">
            <a href="{{route("web.home")}}">
                <img src="{{ getSingleMedia(appSettingData('get'), 'site_logo',null) }}" alt="Footer Logo" class="footer-logo" /> 
            </a>
        </div>
        <div class="col-lg-1 d-lg-none d-xl-block">
        </div>
        <div class="col-lg-2 col-md-2  col-sm-4">
            <h5 class="heading">Address</h5>
            <div class="footer-links">
                <a href="javascript:void(0)">{{appSettingData('get')->address}}</a>
            </div> 
            <div class="social-link">
                @if(appSettingData('get')->facebook_url)
                    <a href="{{appSettingData('get')->facebook_url}}" target="_blank" class="social-icon"><i class='fa-brands fa-facebook'></i></a>
                @endif
                @if(appSettingData('get')->twitter_url)
                    <a href="{{appSettingData('get')->twitter_url}}" target="_blank" class="social-icon"><i class='fa-brands fa-twitter'></i></a>
                @endif
                @if(appSettingData('get')->linkedin_url)
                    <a href="{{appSettingData('get')->linkedin_url}}" target="_blank" class="social-icon"><i class='fa-brands fa-linkedin'></i></a>
                @endif
                @if(appSettingData('get')->instagram_url)
                    <a href="{{appSettingData('get')->instagram_url}}" target="_blank" class="social-icon"><i class='fa-brands fa-instagram'></i></a>
                @endif
            </div>
             {{--    <a href="#">Web-designers</a>
                <a href="#">Marketers</a>
                <a href="#">Small Business</a>
                <a href="#">Website Builder</a>
            </div> --}}
        </div>
        <div class="col-lg-2 col-md-2  col-sm-4">
            <h5 class="heading">Company</h5>
            <div class="footer-links">
                <a href="{{route('web.plan-pricing')}}">Plan & Pricing</a>
                <a href="{{route('web.program-list')}}">Program List</a>
                <a href="{{route('web.about')}}">About Us</a>
                <a href="{{route('web.contact')}}">Contact Us</a>
                
            </div>
        </div>
        <div class="col-lg-2 col-md-2  col-sm-4">
            <h5 class="heading">Company</h5>
            <div class="footer-links">
                <a href="{{route('web.blog')}}">Blogs</a>
                <a href="{{route('web.faq')}}">FAQ</a>
            </div>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 mx-auto">
            <p class="subscribe-text">Subscribe</p>
            <p class="subscribe-description">
            Subscribe to stay tuned for latest web designs<br/>and updates. Let's do it!
            </p>
            <form id="modalSubscribeForm" method="POST" action="{{ route('web.login.register') }}">
                {{csrf_field()}}
                <div class="subscribe-box">
                    <input type="email" name="email" placeholder="Enter your email" required/>
                    <button type="submit" class="subscribe-btn">Subscribe</button>
                </div>
            </form>
            <div class="mobile-app-footer">
                <a href="{{ SettingData('APPVERSION', 'APPVERSION_PLAYSTORE_URL') }}" target="_blank">
                    <img src="{{asset("asset/images/playstore.png")}}" alt="Footer Logo" class="footer-img1" />     
                </a>
                <a href="{{ SettingData('APPVERSION', 'APPVERSION_APPSTORE_URL') }}" target="_blank">
                    <img src="{{asset("asset/images/apple.png")}}" alt="Footer Logo" class="footer-img2" />
                </a>
            </div>
        </div>
    </div>
   
    <div class="bottom-footer">
      <div class="footer-links">
        <a href="{{route('web.privacy-policy')}}">Privacy Policy</a>
        <a href="{{route('web.term-condition')}}">Terms of Use</a>
      </div>
      <div class="footer-copy">
        {{appSettingData('get')->site_copyright}}
      </div>
    </div>
  </footer>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js" integrity="sha256-AlTido85uXPlSyyaZNsjJXeCs07eSv3r43kyCVc8ChI=" crossorigin="anonymous"></script>
  {{-- <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-element-bundle.min.js"></script> --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
  <script src="{{asset('package/jquery-validation/jquery.validate.min.js')}}"></script>
  <script src="{{asset("asset/js/script.js")}}"></script>
  <script src="{{asset("asset/js/modal.js")}}"></script>
  <script src="https://cdn.jsdelivr.net/npm/lobibox@1.2.7/dist/js/lobibox.min.js" integrity="sha256-TlLYgK04bUHQHZqxnMcjHIoA3K1In7/VymeJAIVHa4A=" crossorigin="anonymous"></script>

<script>
    function btnLoading(elem) {
        $(elem).attr("data-original-text", $(elem).html());
        $(elem).prop("disabled", true);
        $(elem).html('<i class="spinner-border spinner-border-sm"></i> Loading...');
    }

    function btnLoadingReset(elem) {
        $(elem).prop("disabled", false);
        $(elem).html($(elem).attr("data-original-text"));
    }
    
    function round_warning_noti(message) {
        Lobibox.notify('warning', {
            sound: false,
            pauseDelayOnHover: true,
            size: 'mini',
            rounded: true,
            delayIndicator: false,
            icon: 'bx bx-error',
            continueDelayOnInactiveTab: false,
            position: 'top right',
            msg: message
        });
    }

    function round_error_noti(message) {
        Lobibox.notify('error', {
            sound: false,
            pauseDelayOnHover: true,
            size: 'mini',
            rounded: true,
            delayIndicator: false,
            icon: 'bx bx-x-circle',
            continueDelayOnInactiveTab: false,
            position: 'top right',
            msg: message
        });
    }

    function round_success_noti(message) {
        Lobibox.notify('success', {
            sound: false,
            pauseDelayOnHover: true,
            size: 'mini',
            rounded: true,
            icon: 'bx bx-check-circle',
            delayIndicator: false,
            continueDelayOnInactiveTab: false,
            position: 'top right',
            msg: message
        });
    }


    function resentOtpMail(){
        $.ajax({
            type: "POST",
            url: "{{route('web.login.mail_otp')}}",
            data: $("#modalMailOtpForm").serialize(),
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(data)
            {
                btnLoadingReset($(".mail_otp_btn"));
                if (data.status) {
                    $("#mailOtpModal .otp-field-div").removeClass('d-none');
                    $("#mailOtpModal input[name='email']").addClass('d-none');
                    $("#mailOtpModal input[name='type']").val("check_otp");
                    $("#mailOtpModal .mail_otp_btn").html("Verify OTP");
                    round_success_noti(data.message);
                }
                else {
                    round_error_noti(data.message);
                }
            },
            error: function (res) {
                btnLoadingReset($(".mail_otp_btn"));
                let message = "Something went wrong";

                let resData = res.responseJSON;
                if(resData.errors){
                    message = Object.keys(resData.errors).map((key) => resData.errors[key]).join("<br>");
                }
                round_error_noti(message);
            }
        });
    }

    $( document ).ready(function() {
        // $('.select2').select2({theme: "bootstrap-5",});
        
        $("#modalLoginForm").validate({rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true
            }
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
            btnLoading($(".login_btn"));
            $.ajax({
                type: "POST",
                url: "{{route('web.login.submit')}}",
                data: $(form).serialize(),
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(data)
                {
                    btnLoadingReset($(".login_btn"));
                    if (data.status) {
                        window.location.href = "{{route('dashboard')}}";
                    }
                    else {
                        $(".res-message.text-danger").html(data.message);
                        $(".res-message.text-danger").removeClass("d-none");
                    }
                },
                error: function (res) {
                    btnLoadingReset($(".login_btn"));
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

        $("#modalSubscribeForm").validate({rules: {
        },
        errorClass: "error",
        validClass: "valid",
        errorElement: 'div',
        errorPlacement: function(error, element) {
            if(element.parent('.subscribe-box').length) {
                error.insertAfter(element.parent());
            }else if(element.parent('.input-group').length) {
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
            btnLoading($(".subscribe-btn"));
            $.ajax({
                type: "POST",
                url: "{{route('web.subscriber')}}",
                data: $(form).serialize(),
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(data)
                {
                    btnLoadingReset($(".subscribe-btn"));
                    if (data.status) {
                        $("#modalSubscribeForm input").val('');
                        round_success_noti(data.message);
                        
                    }
                    else {
                        round_error_noti(data.message);
                    }
                },
                error: function (res) {
                    btnLoadingReset($(".subscribe-btn"));
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

        $("#modalSignupForm").validate({rules: {
            email: {
                required: true,
                email: true
            },
            password: {
                required: true,
                minlength: 6,
            },
            password_confirmation: {
                equalTo: "#password"
            }
        },
        errorClass: "error",
        validClass: "valid",
        errorElement: 'div',
        errorPlacement: function(error, element) {
            if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            }else if(element.parent('.form-check').length) {
                error.insertAfter(element.parent());
            } else if(element.parent('.contact-wrapper').length) {
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
            btnLoading($(".register_btn"));
            $.ajax({
                type: "POST",
                url: "{{route('web.login.register')}}",
                data: $(form).serialize(),
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function(data)
                {
                    btnLoadingReset($(".register_btn"));
                    if (data.status) {
                        window.location.href = "{{route('dashboard')}}";
                    }
                    else {
                        $(".res-message.text-danger").html(data.message);
                        $(".res-message.text-danger").removeClass("d-none");
                    }
                },
                error: function (res) {
                    btnLoadingReset($(".register_btn"));
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


        $("#modalMailOtpForm").validate({rules: {
            
        },
        errorClass: "error",
        validClass: "valid",
        errorElement: 'div',
        errorPlacement: function(error, element) {
            if(element.parent('.input-group').length) {
                error.insertAfter(element.parent());
            }else if(element.parent('.form-check').length) {
                error.insertAfter(element.parent());
            } else if(element.parent('.contact-wrapper').length) {
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
            btnLoading($(".mail_otp_btn"));

            if($("#mailOtpModal input[name='type']").val()=='get_otp'){
                resentOtpMail();
            }else if($("#mailOtpModal input[name='type']").val()=='check_otp'){
                $.ajax({
                    type: "POST",
                    url: "{{route('web.login.mail_otp.check')}}",
                    data: $(form).serialize(),
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(data)
                    {
                        btnLoadingReset($(".mail_otp_btn"));
                        if (data.status) {
                            if(data.get_name){
                                $("#mailOtpModal input[name='first_name'], #mailOtpModal input[name='last_name']").removeClass('d-none');
                                $("#mailOtpModal input[name='email']").addClass('d-none');
                                $("#mailOtpModal .otp-field-div").addClass('d-none');
                                $("#mailOtpModal input[name='type']").val("register");
                                $("#mailOtpModal .mail_otp_btn").html("Continue");
                            }else if(data.url){
                                window.location.href = data.url;
                            }
                        }
                        else {
                            round_error_noti(data.message);
                        }
                    },
                    error: function (res) {
                        btnLoadingReset($(".mail_otp_btn"));
                        let message = "Something went wrong";
    
                        let resData = res.responseJSON;
                        if(resData.errors){
                            message = Object.keys(resData.errors).map((key) => resData.errors[key]).join("<br>");
                        }
                        round_error_noti(message);
                    }
                });
            }else if($("#mailOtpModal input[name='type']").val()=='register'){
                $.ajax({
                    type: "POST",
                    url: "{{route('web.login.mail_otp.register')}}",
                    data: $(form).serialize(),
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(data)
                    {
                        btnLoadingReset($(".mail_otp_btn"));
                        if (data.status) {
                            window.location.href = "{{route('dashboard')}}";
                        }
                        else {
                            round_error_noti(data.message);
                        }
                    },
                    error: function (res) {
                        btnLoadingReset($(".mail_otp_btn"));
                        let message = "Something went wrong";
    
                        let resData = res.responseJSON;
                        if(resData.errors){
                            message = Object.keys(resData.errors).map((key) => resData.errors[key]).join("<br>");
                        }
                        round_error_noti(message);
                    }
                });
            }
        }
        });
    });
</script>
@stack('scripts')