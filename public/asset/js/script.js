// script.js

document.addEventListener('DOMContentLoaded', () => {
  const getAppBtn = document.querySelector('.get-app-btn');

  getAppBtn.addEventListener('click', (e) => {
    alert('Redirecting to app download page...');
  });

  // Example: Highlight active link
  const links = document.querySelectorAll('.nav-links a');
  links.forEach(link => {
    if (link.href === window.location.href) {
      link.classList.add('active-link'); 
    }
  });
});
document.addEventListener('DOMContentLoaded', () => {
  const buttons = document.querySelectorAll('.get-started-btn');

  buttons.forEach(button => {
    button.addEventListener('click', () => {
      const isLoggedIn = localStorage.getItem("isLoggedIn");

      if (isLoggedIn === "true") {
        // User is logged in, go to profile
        window.location.href = "profile/profile.html";
      } else {
        // User is not logged in, go to login
        window.location.href = "auth/login.html";
      }
    });
  });
});

    