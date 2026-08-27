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
  const mobileNavLinks = mobileNav.querySelectorAll("a");
  const focusableSelector =
    'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

  const openMenu = () => {
    mobileNav.classList.add("active");
    mobileNav.setAttribute("aria-hidden", "false");
    menuToggle.setAttribute("aria-expanded", "true");
    menuToggle.setAttribute("aria-label", "Cerrar menú");
    document.body.classList.add("menu-open");
    menuClose.focus();
  };

  const closeMenu = ({ restoreFocus = true } = {}) => {
    mobileNav.classList.remove("active");
    mobileNav.setAttribute("aria-hidden", "true");
    menuToggle.setAttribute("aria-expanded", "false");
    menuToggle.setAttribute("aria-label", "Abrir menú");
    document.body.classList.remove("menu-open");

    if (restoreFocus) {
      menuToggle.focus();
    }
  };

  const keepFocusInsideMenu = (event) => {
    if (event.key !== "Tab" || !mobileNav.classList.contains("active")) {
      return;
    }

    const focusableElements = [...mobileNav.querySelectorAll(focusableSelector)];
    const firstElement = focusableElements[0];
    const lastElement = focusableElements.at(-1);

    if (event.shiftKey && document.activeElement === firstElement) {
      event.preventDefault();
      lastElement.focus();
    } else if (!event.shiftKey && document.activeElement === lastElement) {
      event.preventDefault();
      firstElement.focus();
    }
  };

  menuToggle.addEventListener("click", openMenu);
  menuClose.addEventListener("click", closeMenu);

  mobileNavLinks.forEach((link) => {
    link.addEventListener("click", () => {
      closeMenu();
    });
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && mobileNav.classList.contains("active")) {
      closeMenu();
    }

    keepFocusInsideMenu(event);
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth > 1280 && mobileNav.classList.contains("active")) {
      if (mobileNav.contains(document.activeElement)) {
        document.activeElement.blur();
      }
      closeMenu({ restoreFocus: false });
    }
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

if (window.lucide) {
  lucide.createIcons();
}
