/*====================================
COLMENERO
MAIN SCRIPT
====================================*/

/*====================================
MOBILE MENU
====================================*/

const menuToggle = document.querySelector(".menu-toggle");
const menuClose = document.querySelector(".menu-close");
const mobileNav = document.querySelector(".mobile-nav");

if (menuToggle && menuClose && mobileNav) {
  menuToggle.addEventListener("click", () => {
    mobileNav.classList.add("active");
  });

  menuClose.addEventListener("click", () => {
    mobileNav.classList.remove("active");
  });

  document.querySelectorAll(".mobile-nav a").forEach((link) => {
    link.addEventListener("click", () => {
      mobileNav.classList.remove("active");
    });
  });
}

/*====================================
WHATSAPP
====================================*/

const WHATSAPP_NUMBER = "524492223070";

const WHATSAPP_MESSAGE =
  "Hola, me gustaría solicitar una asesoría patrimonial.";

const whatsappURL = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(WHATSAPP_MESSAGE)}`;

document.querySelectorAll(".whatsapp-btn").forEach((button) => {
  button.addEventListener("click", function (event) {
    event.preventDefault();

    window.open(whatsappURL, "_blank");
  });
});

/*====================================
LUCIDE ICONS
====================================*/

lucide.createIcons();
