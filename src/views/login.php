<?php
session_start();
require_once '../php/classes/user.php';

if (isset($_SESSION['user_id'])) {
  header("Location: dashboard.php");
  exit;
}

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
  <link rel="stylesheet" href="../styles/css/formDefaultStyles.css">
  <title>Document</title>
</head>

<body>
  <?php if ($error_message): ?>
    <p style="color: red; font-weight: bold; border: 1px solid red; padding: 10px;">
      <?php echo htmlspecialchars($error_message); ?>
    </p>
  <?php endif; ?>
  <form action="../php/services/loginService.php" method="post">
    <h2>Iniciar Sesión</h2>
    <a href="/loginPooMejorado/src/views/register.php">No tienes cuenta?, crea una.</a>
    <section>
      <label>Email:</label>
      <input type="email" name="email" required />
    </section>
    <section>
      <label>Contraseña:</label>
      <input type="password" name="password" required />
    </section>
    <button type="submit">Ingresar</button>
  </form>
</body>

</html>