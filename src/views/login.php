<?php
session_start();
require_once '../php/classes/user.php';

// if (isset($_SESSION['user_id'])) {
//   header("Location: dashboard.php");
//   exit;
// }

$error_message = null;
if (isset($_SESSION['error_login'])) {
  $error_message = $_SESSION['error_login'];
  // 2. IMPORTANTE: Eliminar el mensaje de la sesión para que no se muestre de nuevo
  unset($_SESSION['error_login']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../styles/css/globalStyles.css">
  <!-- <link rel="stylesheet" href="../styles/css/formDefaultStyles.css"> -->
  <link rel="stylesheet" href="../styles/css/auth-forms.css">
  <!-- <link rel="stylesheet" href="../styles/css/formBackgrouns.css"> -->
  <title>Login</title>
</head>

<body>
  <div class="form-container">
    <img src="../images/Lubriken-log-o-type.png" alt="Logotipo Lubriken" class="logo">

    <h2>Iniciar Sesión</h2>

    <?php
    // Mostrar mensaje de error si existe
    if (isset($_SESSION['error_message'])) {
      echo '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
      // Limpiar el mensaje de error para que no se muestre de nuevo
      unset($_SESSION['error_message']);
    }
    ?>

    <form action="../php/services/loginService.php" method="POST">
      <div class="form-group">
        <label for="email">Correo</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required>
      </div>

      <button type="submit" class="submit-btn">Ingresar</button>
    </form>

    <div class="toggle-link">
      <p>¿No tienes una cuenta? <a href="register.php">Regístrate aquí</a></p>
    </div>
  </div>
</body>

</html>