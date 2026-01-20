// Modal open/close functions
function openLoginModal() {
  document.getElementById("loginModal").style.display = "block";
}

function closeLoginModal() {
  document.getElementById("loginModal").style.display = "none";
}

function openSignupModal() {
  document.getElementById("signupModal").style.display = "block";
}

function closeSignupModal() {
  document.getElementById("signupModal").style.display = "none";
}

function openMailOtpModal() {
  $("#mailOtpModal input[name='email']").removeClass('d-none');
  $("#mailOtpModal input[name='first_name'], #mailOtpModal input[name='last_name'], #mailOtpModal .otp-field-div").addClass('d-none');
  $("#mailOtpModal input").val("");
  $("#mailOtpModal input[name='type']").val("get_otp");
  $("#mailOtpModal .mail_otp_btn").html("Send OTP");
  document.getElementById("mailOtpModal").style.display = "block";
}

function closeMailOtpModal() {
  document.getElementById("mailOtpModal").style.display = "none";
}

function switchToSignup() {
  closeLoginModal();
  closeMailOtpModal();
  openSignupModal();
}

function switchToLogin() {
  closeSignupModal();
  closeMailOtpModal();
  openLoginModal();
}

function switchToMailOtp() {
  closeLoginModal();
  closeSignupModal();
  openMailOtpModal();
}


// Password toggle visibility
function togglePassword(id, icon) {
  const input = document.getElementById(id);
  if (input.type === "password") {
    input.type = "text";
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye");
  }
}

// Password match validation
const passwordInput = document.getElementById("password");
const confirmPasswordInput = document.getElementById("confirmPassword");
const matchMsg = document.getElementById("matchMsg");

confirmPasswordInput.addEventListener("input", function () {
  if (confirmPasswordInput.value !== passwordInput.value) {
    matchMsg.textContent = "Passwords do not match";
    matchMsg.style.color = "red";
  } else {
    matchMsg.textContent = "Passwords match";
    matchMsg.style.color = "green";
  }
});

// Handle signup form submission
function handleSignup(event) {
  event.preventDefault();
  const password = passwordInput.value;
  const confirmPassword = confirmPasswordInput.value;

  if (password === confirmPassword) {
    // Simulate successful signup and switch to login
    closeSignupModal();
    openLoginModal();
  } else {
    matchMsg.textContent = "Passwords do not match!";
    matchMsg.style.color = "red";
  }
  return false;
}



// Close modals when clicking outside
window.onclick = function (event) {
  const loginModal = document.getElementById("loginModal");
  const signupModal = document.getElementById("signupModal");
  if (event.target === loginModal) closeLoginModal();
  if (event.target === signupModal) closeSignupModal();
};

// Set current year in footer
// document.getElementById("year").textContent = new Date().getFullYear();

$(document).ready(function () {
  $(".otp-field-div *:input[type!=hidden]:first").focus();
  let otp_fields = $(".otp-field-div .otp-field"),
    otp_value_field = $(".otp-field-div .otp-value");
  otp_fields
    .on("input", function (e) {
      $(this).val(
        $(this)
          .val()
          .replace(/[^0-9]/g, "")
      );
      let opt_value = "";
      otp_fields.each(function () {
        let field_value = $(this).val();
        if (field_value != "") opt_value += field_value;
      });
      otp_value_field.val(opt_value);
    })
    .on("keyup", function (e) {
      let key = e.keyCode || e.charCode;
      if (key == 8 || key == 46 || key == 37 || key == 40) {
        // Backspace or Delete or Left Arrow or Down Arrow
        $(this).prev().focus();
      } else if (key == 38 || key == 39 || $(this).val() != "") {
        // Right Arrow or Top Arrow or Value not empty
        $(this).next().focus();
      }
    })
    .on("paste", function (e) {
      let paste_data = e.originalEvent.clipboardData.getData("text");
      let paste_data_splitted = paste_data.split("");
      $.each(paste_data_splitted, function (index, value) {
        otp_fields.eq(index).val(value);
      });
    });

});