<?php
// src/php/dashboard.php (Refactorizado para usar User::getUserDataById)
session_start();
//require_once '../php/classes/user.php';

// 1. Guardia de seguridad: Asegura que el usuario esté logueado
// if (!isset($_SESSION['user_id'], $_SESSION['usuario'])) {
//     // Si no está logueado, redirige
//     header("Location: login.php");
//     exit;
// }

if (isset($_SESSION['user_id'], $_SESSION['usuario'])) {
  $nombre_usuario = htmlspecialchars($_SESSION['usuario']);
  $id_usuario = $_SESSION['user_id'];
}

// Obtenemos el nombre del usuario directamente de la sesión para mostrarlo

// Nota: El código comentado que obtiene datos de la DB cada vez NO es necesario solo para mostrar el nombre.
// Lo mantienes comentado para evitar la carga innecesaria del modelo.

//Obtención de mensajes de error de actualización
$update_error_message = null;
if (isset($_SESSION['update_error'])) {
  $update_error_message = $_SESSION['update_error'];
  //Se limpia la sesión para que el mensaje no se muestre al recargar
  unset($_SESSION['update_error']);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../styles/css/globalStyles.css" />
  <link rel="stylesheet" href="../styles/css/userMenuHeader.css">
  <link rel="stylesheet" href="../styles/css/product.css">
  <link rel="stylesheet" href="../styles/css/testimonials.css">
  <link rel="stylesheet" id="sobrenosotros-style" href="../styles/css/section-sobrenosotros.css">
  <link rel="stylesheet" href="../styles/css/preguntas.css">
  <title>Dashboard</title>
</head>

<body>
  <header id="navigation-bar">
    <section id="desktop-navbar">
      <img src="../images/lubriken-log-o-type.png" alt="logotype" />
      <nav class="desktop-menu">
        <ul>
          <li><a href="#">Inicio</a></li>
          <li><a href="#sobrenosotros-view">Sobre nosotros</a></li>
          <li><a href="#productos">Nuestros Productos</a></li>
          <li><a href="#testimonios">testimonial de pasantias</a></li>
          <li><a href="#preguntas">FAQs</a></li>
          <?php if (isset($_SESSION['usuario'])): ?>
            <li class="user-menu-item">
              <a href="#perfil" id="user-name-link"><?php echo $nombre_usuario; ?></a>
              <!-- <a href="../php/controllers/UserController.php?action=logout" class="logout-btn">Cerrar Sesión</a> -->
            </li>
          <?php else: ?>
            <li><a href="./login.php">Iniciar Sesión</a></li>
          <?php endif; ?>
        </ul>
        </ul>
      </nav>
      <nav id="mobile-menu">
        <ul>
          <li><a href="#">Inicio</a></li>
          <li><a href="#sobrenosotros-view">Sobre nosotros</a></li>
          <li><a href="#productos">Nuestros Productos</a></li>
          <li><a href="#testimonios">testimonial de pasantias</a></li>
          <li><a href="#preguntas">FAQs</a></li>
          <?php if (isset($_SESSION['usuario'])): ?>
            <li class="user-menu-item">
              <a href="#perfil" id="user-name-link"><?php echo $nombre_usuario; ?></a>
              <!-- <a href="../php/controllers/UserController.php?action=logout" class="logout-btn">Cerrar Sesión</a> -->
            </li>
          <?php else: ?>
            <li><a href="./login.php">Iniciar Sesión</a></li>
          <?php endif; ?>
        </ul>
      </nav>
    </section>
    <button id="mobile-menu-btn">☰</button>
  </header>
  <main>
    <section id="sobrenosotros-view" class="content-view content-container sobrenosotros" style="display:none;">
      <h1 class="page-title">Conoce a Lubriken</h1>
      <hr>

      <section class="mission">
        <h2>Nuestra Misión </h2>
        <p>En Lubriken, nuestra misión es simplificar el mantenimiento y la protección de tus activos, ofreciendo
          lubricantes y productos químicos de la más alta calidad.</p>
      </section>

      <section class="history">
        <h2>Nuestra Historia </h2>
        <p>Fundada en 2020, Lubriken nació de la necesidad de un servicio especializado y una entrega eficiente en el
          sector industrial.</p>
      </section>

      <section class="values">
        <h2>Nuestros Valores </h2>
        <ul>
          <li><strong>Calidad:</strong> Productos certificados y probados.</li>
          <li><strong>Compromiso:</strong> Entrega rápida y atención al cliente.</li>
          <li><strong>Innovación:</strong> Soluciones constantes.</li>
        </ul>
      </section>
    </section>
    <!--PRODUCTOS-->
    <section id="productos" class="productos">
      <h2 class="productos-title">Productos</h2>
      <figure>
        <img src="../images/productos/aceite para carro inca.jpg" alt="aceite para carro inca" />
        <figcaption>Aceite para carro Inca - 1 litro</figcaption>
        <p>Precio:20$</p>
        <a href="#" class="btn">Comprar</a>
        <h3 class="status available">DISPONIBLE</h3>
      </figure>

      <figure>
        <img src="../images/productos/filtros-MHW.png" alt="filtro mhw" />
        <figcaption>Filtros MHW</figcaption>
        <p>Precio:15$</p>
        <a href="#" class="btn">Comprar</a>
        <h3 class="status available">DISPONIBLE</h3>
      </figure>

      <figure>
        <img src="../images/productos/base con bombin para filtros.jpg" alt="base con bombin para filtros" />
        <figcaption>Base Con Bombin Para Filtros</figcaption>
        <p>Precio:60$</p>
        <a href="#" class="btn">Comprar</a>
        <h3 class="status available">DISPONIBLE</h3>
      </figure>

      <figure>
        <img src="../images/productos/Filtros Combustible 3196.png" alt="Filtros Combustible 3196" />
        <figcaption>Filtros Combustible 3196</figcaption>
        <p>Precio:10$</p>
        <a href="#" class="btn">Comprar</a>
        <h3 class="status sold-out">AGOTADO</h3>
      </figure>

      <figure>
        <img src="../images/productos/filtro-de-aceite-30dolares.png" alt="Filtros de aceite" />
        <figcaption>Filtros De Aceite</figcaption>
        <p>Precio:30$</p>
        <a href="#" class="btn">Comprar</a>
        <h3 class="status available">DISPONIBLE</h3>
      </figure>

      <figure>
        <img src="../images/productos/Amortiguador Delantero Chevrolet Spark 96424026 Derecho (rh).webp"
          alt="amortiguador" />
        <figcaption>
          Amortiguador Delantero Chevrolet Spark 96424026 Derecho
        </figcaption>
        <p>Precio:40$</p>
        <a href="#" class="btn">Comprar</a>
        <h3 class="status sold-out">AGOTADO</h3>
      </figure>
    </section>
    <h2 id="testimonio-pepon">Testimonios de Pasantía</h2>
    <section id="testimonios" class="testimonial-container">

      <section class="testimonial-card">
        <blockquote>
          "Mi tiempo aquí fue una experiencia de aprendizaje increíble. Pude aplicar mis conocimientos de desarrollo web
          en un proyecto real y el equipo siempre estuvo dispuesto a ayudar."
        </blockquote>
        <section class="testimonial-author">
          <p class="author-name">Jose Correa</p>
          <p class="author-role">Pasante de Desarrollo Web</p>
        </section>
      </section>

      <section class="testimonial-card">
        <blockquote>
          "El ambiente de trabajo en Lubriken C.A. es excelente. Aprendí no solo sobre bases de datos y PHP, sino
          también sobre metodologías de trabajo y buenas prácticas en la industria."
        </blockquote>
        <section class="testimonial-author">
          <p class="author-name">Jonathan Campos</p>
          <p class="author-role">Pasante de Ingeniería de Software</p>
        </section>
      </section>

      <section class="testimonial-card">
        <blockquote>
          "Una pasantía muy completa. Pude participar en el análisis de requerimientos, diseño de la base de datos y
          desarrollo del backend. 100% recomendada."
        </blockquote>
        <section class="testimonial-author">
          <p class="author-name">Andres Jatar</p>
          <p class="author-role">Pasante de Backend</p>
        </section>
      </section>

      <section class="testimonial-card">
        <blockquote>
          "Fue una gran oportunidad para aplicar lo aprendido en la universidad. Participé activamente en el desarrollo
          de un nuevo módulo, desde el diseño de la interfaz con HTML y CSS hasta la implementación de la lógica del
          negocio en el backend. Aprendí muchísimo sobre control de versiones."
        </blockquote>
        <section class="testimonial-author">
          <p class="author-name">Kevyn Camacaro (The special one)</p>
          <p class="author-role">Pasante de Backend</p>
        </section>
      </section>

      <section class="testimonial-card">
        <blockquote>
          ""La pasantía superó mis expectativas. Pude trabajar directamente con PHP y MySQL en el sistema principal,
          optimizando consultas y aprendiendo sobre seguridad web. El equipo siempre estuvo dispuesto a guiarme y
          resolvió todas mis dudas."
        </blockquote>
        <section class="testimonial-author">
          <p class="author-name">Juan Pereira</p>
          <p class="author-role">Pasante de Backend</p>
        </section>
      </section>

    </section>
    <!--Preguntas frecuentes-->
    <h3 class="faq-title">Preguntas Frecuentes</h3>
    <section id="preguntas" class="preguntas">
      <article class="pregunta-card">
        <h4>¿Donde estan ubicados? C.A</h4>
        <p>Urbanización Los Crepusculos, Barquisimeto 3001, Lara</p>
      </article>

      <article class="pregunta-card">
        <h4>¿Cuales son sus horarios de atencion? C.A</h4>
        <p>Lunes a Viernes de 8:00 am a 5:00 pm</p>
      </article>

      <article class="pregunta-card">
        <h4>¿Realizan envios a domicilio? C.A</h4>
        <p>Por ahora no realizamos envio a domicilio.</p>
      </article>

      <article class="pregunta-card">
        <h4>¿Cuales son sus metodos de pago? C.A</h4>
        <p>Aceptamos pagos en efectivo y transferencias bancarias.</p>
      </article>

    </section>
    <!--Preguntas frecuentes-->
  </main>
  <footer>
    <section class="footer-content">
      <p>&copy; 2025 Lubriken. Todos los derechos reservados.</p>
      <p>Barquisimeto, Edo. Lara, Venezuela | Contacto: XXXX-XXXXXXX</p>
    </section>
  </footer>
  <script src="../js/header-component.js"></script>
</body>

</html>