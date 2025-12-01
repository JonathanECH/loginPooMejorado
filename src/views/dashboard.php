<?php
// src/php/dashboard.php (Refactorizado para usar User::getUserDataById)
session_start();
require_once '../php/requires_central.php';
require_once '../php/models/ProductModel.php';

$db = new Database();
$connection = $db->getConnection();
$productModel = new ProductModel($connection);
$products = $productModel->getAllProducts();
$product_error_message = is_string($products) ? $products : null;
if (is_string($products))
  $products = [];

// --- VARIABLES DE SESIÓN ---
$nombre_usuario = $_SESSION['usuario'] ?? 'Invitado'; // Valor predeterminado
$user_logged_in = isset($_SESSION['user_id']); // Simple flag
$user_rol = $_SESSION['user_rol'] ?? 'cliente'; // Asume 'cliente' si no está logueado

// Manejo de errores de actualización...
$update_error_message = $_SESSION['update_error'] ?? null;
unset($_SESSION['update_error']);
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
  <link rel="stylesheet" href="../styles/css/ContactForm.css">
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
          <li><a href="#formulario-contacto">Contacto</a></li>
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
          <li><a href="#formulario-contacto">Contacto</a></li>
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
    <form action="../php/controllers/UserController.php" method="POST">
      <input type="hidden" name="action" value="logout">
      <button type="submit" class="logout-btn">Cerrar Sesión</button>
    </form>
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
      <?php foreach ($products as $product):
        $stock = $product['stock_actual'];
        $status_class = ($stock > 0) ? 'available' : 'sold-out';
        $status_text = ($stock > 0) ? 'DISPONIBLE (' . $stock . ' en stock)' : 'AGOTADO';
        ?>
        <figure>
          <img src="<?php echo htmlspecialchars($product['imagen_url']); ?>"
            alt="<?php echo htmlspecialchars($product['nombre']); ?>" />

          <figcaption><?php echo htmlspecialchars($product['nombre']); ?></figcaption>
          <p><?php echo htmlspecialchars($product['descripcion'] ?? 'Sin descripción.'); ?></p>
          <p>Precio: **<?php echo htmlspecialchars($product['precio']); ?>$**</p>

          <?php if ($user_rol === 'administrador'): ?>
            <form action="../php/controllers/ProductController.php" method="POST" class="admin-controls">
              <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

              <input type="number" name="new_stock" value="<?php echo $stock; ?>" min="0" style="width: 50px;">
              <button type="submit" name="action" value="update_stock" class="btn admin-btn">Actualizar Stock</button>
              <button type="submit" name="action" value="delete_product" class="btn admin-btn delete-btn">Eliminar</button>
            </form>

          <?php else: ?>
            <form action="../php/controllers/ProductController.php" method="POST">
              <input type="hidden" name="action" value="add_to_cart">
              <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

              <?php if ($user_logged_in): ?>
                <input type="number" name="quantity" value="1" min="1" max="<?php echo $stock; ?>" <?php echo ($stock === 0) ? 'disabled' : ''; ?> style="width: 50px; text-align: center;">
                <button type="submit" class="btn" <?php echo ($stock === 0) ? 'disabled' : ''; ?>>
                  Reservar
                </button>
              <?php else: ?>
                <a href="./login.php" class="btn">Iniciar Sesión para Reservar</a>
              <?php endif; ?>
            </form>

          <?php endif; ?>
          <h3 class="status <?php echo $status_class; ?>">
            <?php echo $status_text; ?>
          </h3>
        </figure>
      <?php endforeach; ?>
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
    <section id="formulario-contacto" class="container-form">
      <h2 class="container-form__title">Formulario de contacto</h2>
      <form class="container-form__form" action="" method="POST">

        <div class="container-form__div">
          <label for="nombre_contacto">Nombre</label>
          <input type="text" id="nombre_contacto" placeholder="Nombre">
        </div>

        <div class="container-form__div">
          <label for="numero_contacto">Numero</label>
          <input type="number" id="numero_contacto" placeholder="Numero" min="1">
        </div>

        <div class="container-form__div">
          <label for="correo_contacto">Correo</label>
          <input type="email" id="correo_contacto" placeholder="Correo">
        </div>

        <div class="container-form__div">
          <label for="mensaje_contacto">Mensaje</label>
          <textarea name="mensaje_contacto" id="mensaje_contacto" placeholder="Deja un mensaje"></textarea>
        </div>

        <div class="container-form__div container-form__submit alinear-derecha">
          <button type="submit">Enviar</button>
        </div>
      </form>
    </section>
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