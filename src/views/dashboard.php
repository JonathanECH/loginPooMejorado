<?php
// src/views/dashboard.php 
session_start();
// Asegúrate de que esta ruta sea correcta y cargue todos los Modelos (User, Product, Cart) y Database.
require_once '../php/requires_central.php';
require_once '../php/models/ProductModel.php';
require_once '../php/models/CartModel.php';

// --- SETUP DE CONEXIÓN E INYECCIÓN DE MODELOS ---
$db = new Database();
$connection = $db->getConnection();

$productModel = new ProductModel($connection);
$cartModel = new CartModel($connection);

$products = $productModel->getAllProducts();
$product_error_message = is_string($products) ? $products : null;
if (is_string($products))
  $products = [];

// --- VARIABLES DE SESIÓN Y ROL ---
$nombre_usuario = $_SESSION['usuario'] ?? 'Invitado';
$user_logged_in = isset($_SESSION['user_id']);
$id_usuario = $_SESSION['user_id'] ?? null;
$user_rol = $_SESSION['user_rol'] ?? 'cliente'; // Asume 'cliente' si no está logueado

// Manejo de mensajes de actualización (update=fail)
$update_error_message = $_SESSION['update_error'] ?? null;
unset($_SESSION['update_error']);

// Lógica de Carrito para la UI (solo si está logueado y es cliente)
$cart_items = [];
$total_items_in_cart = 0;

if ($user_logged_in && $user_rol !== 'administrador') {
  $cart_result = $cartModel->viewCart($id_usuario);

  if (is_array($cart_result)) {
    $cart_items = $cart_result;
    $total_items_in_cart = array_sum(array_column($cart_items, 'cantidad'));
  }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../styles/css/globalStyles.css" />
  <link rel="stylesheet" href="../styles/css/userMenuHeader.css">
  <link rel="stylesheet" href="../styles/css/dark-mode.css">
  <link rel="stylesheet" href="../styles/css/product.css">
  <link rel="stylesheet" href="../styles/css/testimonials.css">
  <link rel="stylesheet" href="../styles/css/preguntas.css">
  <link rel="stylesheet" href="../styles/css/ContactForm.css">
  <title>Dashboard</title>
</head>

<body>
  <header id="navigation-bar">
    <section id="desktop-navbar">
      <a href="#"><img src="../images/lubriken-log-o-type.png" alt="logotype" /></a>
      <nav class="desktop-menu">
        <ul>
          <li><a href="#">Inicio</a></li>
          <li><a href="nosotros.php" target="_self">Sobre nosotros</a></li>
          <li><a href="#productos">Nuestros Productos</a></li>
          <li><a href="#testimonios">testimonial de pasantias</a></li>
          <li><a href="#preguntas">FAQs</a></li>
          <li><a href="#formulario-contacto">Contacto</a></li>
          <li>
            <button id="theme-toggle-desktop" class="theme-btn" title="Cambiar tema">🌙</button>
          </li>

          <?php if ($user_logged_in && $user_rol !== 'administrador'): ?>
            <li class="cart-icon-container">
              <a href="carrito.php" id="cart-link">
                🛒 Carrito
                <?php if ($total_items_in_cart > 0): ?>
                  <span class="cart-count">(<?php echo $total_items_in_cart; ?>)</span>
                <?php endif; ?>
              </a>
            </li>
          <?php endif; ?>
        </ul>
        </ul>
      </nav>
      <nav id="mobile-menu">
        <ul>
          <li><a href="#">Inicio</a></li>
          <li><a href="nosotros.php">Sobre nosotros</a></li>
          <li><a href="#productos">Nuestros Productos</a></li>
          <li><a href="#testimonios">testimonial de pasantias</a></li>
          <li><a href="#preguntas">FAQs</a></li>
          <li><a href="#formulario-contacto">Contacto</a></li>
          <li>
            <button id="theme-toggle-mobile" class="theme-btn" title="Cambiar tema">🌙</button>
          </li>
          <?php if (isset($_SESSION['usuario'])): ?>
            <li class="user-menu-item">
              <a href="userdata.php" id="user-name-link"><?php echo $nombre_usuario; ?></a>
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

    <?php if ($update_error_message): ?>
      <p class="errorMsg" style="color: red; padding: 10px; border: 1px solid red;">
        <?php echo htmlspecialchars($update_error_message); ?>
      </p>
    <?php endif; ?>

    <section id="sobrenosotros-view" class="content-view content-container sobrenosotros" style="display:none;">
    </section>

    <section id="productos" class="productos">
      <h2 class="productos-title">Nuestros Productos</h2>

      <?php if ($product_error_message): ?>
        <p class="errorMsg" style="color: red; grid-column: 1 / -1;">
          **Error al cargar los datos del catálogo:** <?php echo htmlspecialchars($product_error_message); ?>
        </p>
      <?php elseif (empty($products)): ?>
        <p style="grid-column: 1 / -1;">Actualmente no hay productos disponibles en el catálogo.</p>
      <?php endif; ?>

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
  </main>
  <footer>
  </footer>
  <script src="../js/header-component.js"></script>
  <script src="../js/theme.js"></script>
</body>

</html>