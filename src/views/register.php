<?php
session_start();
require_once '../php/classes/user.php';

// if (isset($_SESSION['user_id'])) {
//   header("Location: dashboard.php");
//   exit;
// }

// Recuperar datos del formulario y errores
$errors = $_SESSION['errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? ['nombre' => '', 'email' => ''];

// Limpiar la sesión después de recuperarlos
unset($_SESSION['errors']);
unset($_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../styles/css/globalStyles.css" />
  <!-- <link rel="stylesheet" href="../styles/css/formDefaultStyles.css" /> -->
  <link rel="stylesheet" href="../styles/css/auth-forms.css">
  <!-- <link rel="stylesheet" href="../styles/css/formBackgrouns.css"> -->
  <title>Register User</title>
</head>

<body>
  <?php if (!empty($errors)): ?>
    <div style="color: red; border: 1px solid red; padding: 10px; margin-bottom: 20px;">
      <p class="errorMsg">**Se encontraron los siguientes errores:**</p>
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
  <div class="form-container">
    <img src="../images/Lubriken-log-o-type.png" alt="Logotipo Lubriken" class="logo">

    <h2>Crear Cuenta</h2>

    <?php
    // Mostrar mensaje de error si existe
    if (isset($_SESSION['error_message'])) {
      echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
      unset($_SESSION['error_message']);
    }
    ?>

    <form action="../php/controllers/crear.php" method="POST">
      <div class="form-group">
        <label for="nombre">Nombre de Usuario</label>
        <input type="text" id="nombre" name="nombre" required>
      </div>

      <div class="form-group">
        <label for="email">Correo</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>
      </div>

      <div class="form-group">
        <label for="confirmPassword">Confirmar Contraseña</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required>
      </div>

      <button type="submit" class="submit-btn">Registrarse</button>
    </form>

    <div class="toggle-link">
      <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>
  </div>
  </form>
</body>

</html>