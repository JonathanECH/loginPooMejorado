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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/css/globalStyles.css" />
    <link rel="stylesheet" href="../styles/css/testimonials.css">
    <link rel="stylesheet" href="../styles/css/userMenuHeader.css">
    <link rel="stylesheet" href="../styles/css/dark-mode.css">
    <link rel="stylesheet" id="sobrenosotros-style" href="../styles/css/section-sobrenosotros.css">

    <title>Sobre Nosotros</title>
</head>

<body>
    <header id="navigation-bar">
        <section id="desktop-navbar">
            <a href="dashboard.php#inicio"><img src="../images/lubriken-log-o-type.png" alt="logotype" /></a>
            <nav class="desktop-menu">
                <ul>
                    <li><a href="dashboard.php#">Inicio</a></li>
                    <li><a href="dashboard.php#productos">Nuestros Productos</a></li>
                    <li><a href="dashboard.php#testimonios">testimonial de pasantias</a></li>
                    <li><a href="dashboard.php#preguntas">FAQs</a></li>
                    <li><a href="dashboard.php#formulario-contacto">Contacto</a></li>
                    <li>
                        <button id="theme-toggle-desktop" class="theme-btn" title="Cambiar tema">🌙</button>
                    </li>
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
                    <li><a href="dashboard.php#">Inicio</a></li>
                    <li><a href="dashboard.php#productos">Nuestros Productos</a></li>
                    <li><a href="dashboard.php#testimonios">testimonial de pasantias</a></li>
                    <li><a href="dashboard.php#preguntas">FAQs</a></li>
                    <li><a href="dashboard.php#formulario-contacto">Contacto</a></li>
                    <li>
                        <button id="theme-toggle-mobile" class="theme-btn" title="Cambiar tema">🌙</button>
                    </li>
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
    </header><!--Final del header-->
    <main>
        <section id="sobrenosotros-view" class="content-view content-container sobrenosotros" style="display:none;">
            <h1 class="page-title">Conoce a Lubriken</h1>
            <hr>
            <section class="container-information">
                <section class="mission-history">
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
                </section><!--.mission-history-->

                <section class="values">
                    <h2>Nuestros Valores </h2>
                    <ul>
                        <li><strong>Calidad:</strong> Productos certificados y probados.</li>
                        <li><strong>Compromiso:</strong> Entrega rápida y atención al cliente.</li>
                        <li><strong>Innovación:</strong> Soluciones constantes.</li>
                    </ul>
                </section>
            </section>

        </section><!--Final del de Sobre Nosotros-->
    </main>
    <footer>
        <section class="footer-content">
            <p>&copy; 2025 Lubriken. Todos los derechos reservados.</p>
            <p>Barquisimeto, Edo. Lara, Venezuela | Contacto: XXXX-XXXXXXX</p>
        </section>
    </footer>

    <script src="../js/header-component.js"></script>
    <script src="../js/theme.js"></script>
</body>

</html>