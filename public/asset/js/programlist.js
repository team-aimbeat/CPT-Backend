  // Function to open login modal
  function openLoginModal() {
    document.getElementById("loginModal").style.display = "block";
  }

  // Function to close login modal
  function closeLoginModal() {
    document.getElementById("loginModal").style.display = "none";
  }

  // Optional: Close modal when clicking outside of modal content
  window.onclick = function(event) {
    const loginModal = document.getElementById("loginModal");
    if (event.target === loginModal) {
      loginModal.style.display = "none";
    }
  }
