// JS for pricing page
document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll(".plan-card button");

  buttons.forEach(button => {
    button.addEventListener("click", () => {
      const plan = button.closest(".plan-card").querySelector("h2").innerText;
      alert(`You selected the ${plan} plan!`);
    });
  });
});
function openLoginModal() {
  document.getElementById('loginModal').style.display = 'block';
}



