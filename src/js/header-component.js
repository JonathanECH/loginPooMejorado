document.addEventListener("DOMContentLoaded", function () {
  const nav = document.getElementById("navigation-bar");
  const mobileMenuBtn = document.getElementById("mobile-menu-btn");
  const mobileMenu = document.getElementById("mobile-menu");
  const menuMobileLists = mobileMenu.getElementsByTagName("li");
  let isActive = false;

  // Agrega el evento de scroll
  window.addEventListener("scroll", () => (mobileMenuBtn.style.opacity = ".5"));
  window.addEventListener("scrollend", () => {
    const currentScroll = window.scrollY || document.documentElement.scrollTop;
    const atBottom = window.innerHeight + currentScroll >= document.body.scrollHeight;
    atBottom ? (mobileMenuBtn.style.opacity = ".5") : (mobileMenuBtn.style.opacity = ".7");
  });

  // CLICK DE LOS ÍTEMS DEL MENÚ MÓVIL
  for (let i = 0; i < menuMobileLists.length; i++) {
    menuMobileLists[i].addEventListener("click", function () {
      cerrarMenuConAnimacion();
      isActive = false;
    });
  }

  // CLICK DEL BOTÓN DEL MENÚ MÓVIL
  mobileMenuBtn.addEventListener("click", function () {
    console.log("click");
    if (!isActive) {
      configurarMenu("✖", "flex", "0");
      isActive = true;
      return;
    }
    cerrarMenuConAnimacion();
    isActive = false;
  });

  //EVENTO  DE CAMBIO DE TAMAÑO DE VENTANA
  window.addEventListener("resize", function () {
    console.log("resize");
    if (isActive) {
      cerrarMenuConAnimacion();
      isActive = false;
    }
  });

  //FUNCIONES DE CONFIGURACIÓN Y CIERRE DEL MENÚ
  function configurarMenu(btnText, menuDisplay, menuRight) {
    mobileMenuBtn.innerHTML = btnText;
    mobileMenu.style.display = menuDisplay;
    requestAnimationFrame(() => {
      mobileMenu.style.right = menuRight;
    });
  }
  function cerrarMenuConAnimacion() {
    mobileMenuBtn.innerHTML = "☰";
    mobileMenu.style.right = "100%";

    const handleTransitionEnd = () => {
      mobileMenu.style.display = "none";
      mobileMenu.removeEventListener("transitionend", handleTransitionEnd);
    };
    mobileMenu.addEventListener("transitionend", handleTransitionEnd);
  }
});
