document.addEventListener("DOMContentLoaded", function () {
  const nav = document.getElementById("navigation-bar");
  const mobileMenuBtn = document.getElementById("mobile-menu-btn");
  const mobileMenu = document.getElementById("mobile-menu");
  
  // CAMBIO CLAVE: Usamos '>' para seleccionar solo los hijos directos del menú
  // Así evitamos seleccionar los 'li' de los productos dentro del carrito por accidente.
  const menuMobileLists = document.querySelectorAll("#mobile-menu > ul > li");
  
  let isActive = false;

  // Eventos de Scroll
  window.addEventListener("scroll", () => (mobileMenuBtn.style.opacity = ".5"));
  window.addEventListener("scrollend", () => {
    const currentScroll = window.scrollY || document.documentElement.scrollTop;
    const atBottom = window.innerHeight + currentScroll >= document.body.scrollHeight;
    atBottom ? (mobileMenuBtn.style.opacity = ".5") : (mobileMenuBtn.style.opacity = ".7");
  });

  // CLICK DE LOS ÍTEMS DEL MENÚ MÓVIL
  menuMobileLists.forEach(item => {
    
    // Si es el botón del carrito, NO le agregamos el evento de cerrar.
    if (item.classList.contains("mobile-cart-trigger")) {
        return; // Saltamos este elemento
    }

    item.addEventListener("click", function () {
      cerrarMenuConAnimacion();
      isActive = false;
    });
  });

  // CLICK DEL BOTÓN HAMBURGUESA
  mobileMenuBtn.addEventListener("click", function () {
    if (!isActive) {
      configurarMenu("✖", "flex", "0");
      isActive = true;
      return;
    }
    cerrarMenuConAnimacion();
    isActive = false;
  });

  // REDIMENSIONAR PANTALLA
  window.addEventListener("resize", function () {
    if (isActive) {
      cerrarMenuConAnimacion();
      isActive = false;
    }
  });

  // FUNCIONES AUXILIARES
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