<?php
// src/php/dashboard.php (Refactorizado para usar User::getUserDataById)
session_start();
require_once '../php/classes/user.php';

// 1. Guardia de seguridad: Asegura que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
  header("Location: ../views/login.php");
  exit;
}

$id_usuario = $_SESSION['user_id'];

// 2. Usar la clase para obtener los datos
$userModel = new User();
$datos_usuario = $userModel->getUserDataById($id_usuario);

if (!$datos_usuario) {
  // En caso de error, destruye la sesión y finaliza
  session_destroy();
  die("Error: No se pudieron cargar los datos del usuario. Por favor, inicia sesión de nuevo.");
}

$nombre_actual = htmlspecialchars($datos_usuario['nombre']);
$email_actual = htmlspecialchars($datos_usuario['email']);

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
  <link rel="stylesheet" href="../styles/css/formDefaultStyles.css" />
  <title>Dashboard</title>
</head>

<body>
  <h2>¡Bienvenido, <?php echo $nombre_actual; ?>!</h2>

  <h3>Tus Datos Actuales</h3>
  <p>Nombre: <?php echo $nombre_actual; ?></p>
  <p>Email: <?php echo $email_actual; ?></p>
  <?php if ($update_error_message): ?>
    <p class="errorMsg"><?php echo htmlspecialchars($update_error_message); ?></p>
  <?php endif; ?>
  <form action="../php/CRUD/actualizar.php" method="post">
    <h4>Modificar Perfil</h4>
    <section>
      <label>Nuevo Nombre:</label>
      <input type="text" name="nombre" value="<?php echo $nombre_actual; ?>" required>
    </section>
    <section>
      <label>Nuevo Email:</label>
      <input type="email" name="email" value="<?php echo $email_actual; ?>" required>
    </section>
    <button type="submit">Guardar Cambios</button>
  </form>
  <form action="../php/services/logoff.php" method="post">
    <button type="submit">Cerrar Sesión</button>
</body>

</html>