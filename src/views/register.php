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
  <link rel="stylesheet" href="../styles/css/formDefaultStyles.css" />
  <link rel="stylesheet" href="../styles/css/formBackgrouns.css">
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
  <form action="/loginPooMejorado/src/php/CRUD/crear.php" method="post">
    <h2>Registro de usuario</h2>
    <a href="/loginPooMejorado/src/views/login.php">Ya tienes una cuenta?, inicia sesión</a>
    <section>
        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($form_data['nombre']); ?>" required />
    </section>
    <section>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" required />
    </section>
    
    <section>
        <label>Contraseña:</label>
        <input type="password" name="password" required />
    </section>
    <section>
        <label>Confirmar contraseña:</label>
        <input type="password" name="confirmPassword" required />
    </section>
    <button type="submit">Ingresar</button>
</form>
</body>

</html>