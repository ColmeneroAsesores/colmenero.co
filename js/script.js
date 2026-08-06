/*====================================
COLMENERO
MAIN SCRIPT
====================================*/

const menuToggle = document.querySelector(".menu-toggle");
const menuClose = document.querySelector(".menu-close");
const mobileNav = document.querySelector(".mobile-nav");

/*====================================
OPEN MENU
====================================*/

if (menuToggle && mobileNav) {

    menuToggle.addEventListener("click", () => {

        mobileNav.classList.add("active");
        document.body.classList.add("menu-open");

    });

}

/*====================================
CLOSE MENU
====================================*/

if (menuClose && mobileNav) {

    menuClose.addEventListener("click", () => {

        mobileNav.classList.remove("active");
        document.body.classList.remove("menu-open");

    });

}

/*====================================
CLOSE WHEN CLICKING A LINK
====================================*/

if (mobileNav) {

    mobileNav.querySelectorAll("a").forEach(link => {

        link.addEventListener("click", () => {

            mobileNav.classList.remove("active");
            document.body.classList.remove("menu-open");

        });

    });

}

/*====================================
RESET WHEN RETURNING TO DESKTOP
====================================*/

window.addEventListener("resize", () => {

    if (window.innerWidth > 1280) {

        if (mobileNav) {

            mobileNav.classList.remove("active");

        }

        document.body.classList.remove("menu-open");

    }

});

/*====================================
SAFETY RESET ON PAGE LOAD
====================================*/

window.addEventListener("load", () => {

    document.body.classList.remove("menu-open");

    if (mobileNav) {

        mobileNav.classList.remove("active");

    }

});
lucide.createIcons();