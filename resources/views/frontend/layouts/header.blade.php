<!-- Header -->
<header>
    <div class="header-container">
      {{-- <img src="{{ getSingleMedia(appSettingData('get'), 'site_logo',null) }}" alt="Logo" class="logo" />
      <nav class="nav-links">
        <a href="#" class="home-link">HOME</a>
        <a href="plan-pricing/plan-pricing.html">PLAN & PRICING</a>
        <a href="programlist/programlist.html">PROGRAM LIST</a>
        <a href="about/about.html">ABOUT US</a>
        <a href="contact/contact.html">CONTACT US</a>
      </nav>




      <div class="header-actions">
        <a href="https://play.google.com/store/apps/details?id=com.yourapp.package" target="_blank">
          <button class="get-app-btn">GET APP</button>
        </a>
        <div class="profile-icon" onclick="openLoginModal()" style="text-decoration: none; cursor: pointer;">
          <img src="{{asset("asset/images/person.png")}}" alt="Profile" class="profile-img" />
          <p class="profile-text">Profile</p>
        </div>
      </div> --}}


      <nav class="navbar navbar-expand-lg navbar-light header-menu">
        <div class="container-fluid p-0">
          <a class="navbar-brand" href="{{route('web.home')}}"><img src="{{ getSingleMedia(appSettingData('get'), 'site_logo',null) }}" alt="Logo" class="logo" /></a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav  ms-auto me-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link {{activeRoute(route('web.home'))}}" aria-current="page" href="{{route('web.home')}}">HOME</a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{activeRoute(route('web.plan-pricing'))}}" href="{{route('web.plan-pricing')}}">PLAN & PRICING</a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{activeRoute(route('web.program-list'))}}" href="{{route('web.program-list')}}">PROGRAM LIST</a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{activeRoute(route('web.about'))}}" href="{{route('web.about')}}">ABOUT US</a>
              </li>
              <li class="nav-item">
                <a class="nav-link {{activeRoute(route('web.contact'))}}" href="{{route('web.contact')}}">CONTACT US</a>
              </li>
            </ul>
            <div class="header-actions">
                <a href="{{ SettingData('APPVERSION', 'APPVERSION_PLAYSTORE_URL') }}" target="_blank">
                  <button class="get-app-btn">GET APP</button>
                </a>
                @auth
                
                <a href="{{route('dashboard')}}" class="profile-icon"  style="text-decoration: none; cursor: pointer;">
                  <img src="{{asset("asset/images/person.png")}}" alt="Profile" class="profile-img" />
                  <p class="profile-text">Profile</p>
                </a>
                @else
                <div class="profile-icon" onclick="openLoginModal()" style="text-decoration: none; cursor: pointer;">
                  <img src="{{asset("asset/images/person.png")}}" alt="Profile" class="profile-img" />
                  <p class="profile-text">Login</p>
                </div>
                @endauth
              </div>
          </div>
        </div>
      </nav>

    </div>
  </header>
  <!-- Login Modal -->
  <div id="loginModal" class="modal">
    
      <div class="modal-content">
        <span class="close-btn" onclick="closeLoginModal()">×</span>
        <img src="{{ getSingleMedia(appSettingData('get'), 'site_logo',null) }}" alt="Logo" class="modal-logo" />
        <h2 class="login-txt">Login</h2>
        
        <form id="modalLoginForm" method="POST" action="{{ route('login') }}">
          
          <!-- Email Input -->
          <input type="email" name="email"   placeholder="Email" required />
        
          <!-- Password Input -->
          <input type="password" name="password" placeholder="Password" required />
        
          <!-- Forgot Password Link -->
          <div class="forgot-password">
            <a href="{{route('password.request')}}" class="text-decoration-none" >Forgot Password?</a>
          </div>
        
          <!-- Submit Button -->
          <button type="submit" class="login_btn">Login</button>
          <div class="res-message text-danger d-none mt-2"></div>
        </form>
        
        <div class="social-login-wrapper">
          <div class="social-login-inline">
            <p>Or connect with</p>
            <div class="social-buttons-inline">
              <a href="{{route('web.login.google')}}" class="social-icon google text-decoration-none"><i class="fab fa-google"></i></a>
              <a href="{{route('web.login.facebook')}}" class="social-icon facebook text-decoration-none"><i class="fab fa-facebook-f"></i></a>
              <a href="#" class="social-icon gmail text-decoration-none" onclick="switchToMailOtp()"><i class="fas fa-envelope"></i></a>
            </div>
          </div>
        </div>
        <p class="signup-link">Don't have an account? <a href="#" class="text-decoration-none" onclick="switchToSignup()">Sign Up</a></p>
        <span class="terms-note">
          * By continuing you agree to the Terms of <br /> Service and Privacy Policy.
        </span>
      </div>
    
  </div>

  <!-- Sign Up Modal -->
  <div id="signupModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeSignupModal()">×</span>
      <img src="{{ getSingleMedia(appSettingData('get'), 'site_logo',null) }}" alt="Logo" class="modal-logo" />
      <h2 class="login-txt">Sign Up</h2>
      <form id="modalSignupForm" method="POST" action="{{ route('web.login.register') }}">
          
        <!-- First Name -->
        <input type="text" id="firstName" name="first_name" placeholder="First Name" required />
      
        <!-- Last Name -->
        <input type="text" id="lastName" name="last_name" placeholder="Last Name" required />
      
        <!-- Email -->
        <input
          type="email"
          id="email"
          name="email"
          placeholder="Email"
          required
          title="Enter a valid email address"
        />
      
        <!-- Contact Number -->
        <div class="contact-wrapper">
          <span class="prefix">+91</span>
          <input
            type="tel"
            id="contact"
            name="phone_number"
            placeholder="Contact Number"
            pattern="[0-9]{10}"
            required
            title="Enter a 10-digit phone number"
          />
        </div>
      
        <!-- Password -->
        <div class="password-container">
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Password"
            required
            pattern="[A-Za-z0-9@$!%*?&]{4,}"
            title="Password must be at least 4 characters long and may contain letters, numbers, and symbols"
            />
          <i class="fas fa-eye toggle-password" onclick="togglePassword('password', this)"></i>
        </div>
      
        <!-- Confirm Password -->
        <div class="password-container">
          <input
            type="password"
            id="confirmPassword"
            name="password_confirmation"
            placeholder="Confirm Password"
            required
          />
          <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmPassword', this)"></i>
        </div>
      
        <!-- Password Match Message -->
        <span id="matchMsg" style="color: red; font-size: 12px;"></span>
      
        <!-- Submit -->
        <button type="submit" class="register_btn">Sign Up</button>
      </form>
      
      <p class="signup-linktxt">Already have an account? <a class="login" class="text-decoration-none" href="#" onclick="switchToLogin()">Login</a></p>
    </div>
  </div>


   <!-- Mail Otp Modal -->
   <div id="mailOtpModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeMailOtpModal()">×</span>
      <img src="{{ getSingleMedia(appSettingData('get'), 'site_logo',null) }}" alt="Logo" class="modal-logo" />
      <h2 class="login-txt mt-3 mb-5">Login With OTP</h2>
      <form id="modalMailOtpForm" method="POST" action="{{ route('web.login.register') }}">
          
      
          <input type="hidden" value="get_otp" name="type" />

        <!-- Email -->
        <input
          type="email"
          name="email"
          placeholder="Email"
          required
          title="Enter a valid email address"
        />
      
        {{-- <input
          type="text"
          class="d-none"
          name="otp"
          placeholder="OTP"
          required
          title="Enter a OTP"
        /> --}}
        
        <div class="d-none otp-field-div">
          <input class="otp-field" type="text" name="opt-field[]" maxlength=1>
          <input class="otp-field" type="text" name="opt-field[]" maxlength=1>
          <input class="otp-field" type="text" name="opt-field[]" maxlength=1>
          <input class="otp-field" type="text" name="opt-field[]" maxlength=1>
          <input class="otp-field" type="text" name="opt-field[]" maxlength=1>
          <input class="otp-field" type="text" name="opt-field[]" maxlength=1>

          <!-- Store OTP Value -->
          <input class="otp-value" type="hidden" name="otp">
        </div>
        <div class="d-none otp-field-div resent-div">
          Didn't receive the code? <a href="javascript:void(0)" onclick="resentOtpMail()" class="text-decoration-none text-danger">Resend now</a>
        </div>

        <!-- First Name -->
        <input type="text" class="d-none" name="first_name" placeholder="First Name" required />
      
        <!-- Last Name -->
        <input type="text" class="d-none" name="last_name" placeholder="Last Name" required />

        <!-- Submit -->
        <button type="submit" class="mail_otp_btn">Send OTP</button>
      </form>
      
      <p class="signup-linktxt">Move to <a class="login text-decoration-none" href="#" onclick="switchToLogin()">Login</a></p>
    </div>
  </div>