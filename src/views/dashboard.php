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
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <title>Dashboard</title>
</head>

<body>
  <h2>Bienvenido, <?php echo $nombre_actual; ?>!</h2>
  
  <h3>Tus Datos Actuales</h3>
  <p>Nombre: <?php echo $nombre_actual; ?></p>
  <p>Email: <?php echo $email_actual; ?></p>

  <form action="../php/CRUD/actualizar.php" method="post">
    <h4>Modificar Perfil</h4>
    <label>Nuevo Nombre:</label>
    <input type="text" name="nombre" value="<?php echo $nombre_actual; ?>" required><br>
    <label>Nuevo Email:</label>
    <input type="email" name="email" value="<?php echo $email_actual; ?>" required><br>
    <button type="submit">Guardar Cambios</button>
  </form>
  <form action="../php/services/logoff.php" method="post">
    <button type="submit">Cerrar Sesión</button>
  </body>
</html>