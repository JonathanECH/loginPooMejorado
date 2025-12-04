<?php
// src/views/userdata.php
session_start();
require_once '../php/requires_central.php';

// 1. GUARDIA DE SEGURIDAD
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

$id_usuario = $_SESSION['user_id'];
$user_rol = $_SESSION['user_rol'] ?? 'cliente'; 

// 2. OBTENER DATOS
$db = new Database();
$connection = $db->getConnection(); 
$userModel = new UserModel($connection); 
$datos_usuario = $userModel->getUserDataById($id_usuario);

if (!$datos_usuario) {
    session_destroy();
    header("Location: ../views/login.php");
    exit;
}

$nombre_actual = htmlspecialchars($datos_usuario['nombre']);
$email_actual = htmlspecialchars($datos_usuario['email']);
$csrf_token = SecurityHelper::getCsrfToken();

$update_error_message = $_SESSION['update_error'] ?? null;
$success_message = isset($_GET['update']) && $_GET['update'] == 'success' ? "Datos actualizados correctamente." : null;
unset($_SESSION['update_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/css/globalStyles.css">
    <link rel="stylesheet" href="../styles/css/userdatastyles.css">
    <title>Configuración de Usuario - Lubriken</title>
</head>
<body>

<div class="dashboard-container">
    
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Panel de <?php echo ucfirst($user_rol); ?></h3>
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>
        </div>
        
        <nav>
            <ul>
                <li><a href="dashboard.php">🏠 Volver al Inicio</a></li>
                <li><a href="userdata.php" class="active">⚙️ Configuración</a></li>

                <?php if ($user_rol === 'administrador'): ?>
                    <li><a href="orders.php">📋 Manejar Pedidos</a></li>
                    <li><a href="dashboard.php#productos">📦 Gestionar Inventario</a></li>
                <?php else: ?>
                    <li><a href="carrito.php">🛒 Revisar Carrito</a></li>
                    <li><a href="orders.php">📦 Mis Pedidos</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <form action="../php/controllers/UserController.php" method="POST" class="logout-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="logout-btn">Cerrar Sesión</button>
        </form>
    </aside>

    <main class="main-content">
        <div style="max-width: 800px; margin: 0 auto;"> <h2>Bienvenido, <?php echo $nombre_actual; ?></h2>
            <p style="color: #666; margin-bottom: 20px;">Gestiona tu información personal y seguridad.</p>
            
            <hr style="border: 0; border-top: 1px solid #ccc; margin-bottom: 30px;">

            <?php if ($update_error_message): ?>
                <div class="errorMsg" style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($update_error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="successMsg" style="color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form action="../php/controllers/UserController.php" method="post" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="update">
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; font-weight:bold; margin-bottom: 8px; color: #333;">Correo Electrónico</label>
                    <input type="text" value="<?php echo $email_actual; ?>" disabled 
                           style="width:100%; padding:10px; background:#f8f9fa; border:1px solid #ced4da; border-radius: 5px; color: #6c757d;">
                    <small style="color: #999;">El correo no se puede modificar.</small>
                </div>

                <div style="margin-bottom: 20px;">
                    <label for="nombre" style="display:block; font-weight:bold; margin-bottom: 8px; color: #333;">Nombre de Usuario</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo $nombre_actual; ?>" required 
                           style="width:100%; padding:10px; border:1px solid #ccc; border-radius: 5px;">
                </div>
                
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px dashed #ddd;">
                    <h4 style="margin: 0 0 15px 0; color: #333;">Cambiar Contraseña (Opcional)</h4>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="password" style="display:block; margin-bottom: 5px;">Nueva Contraseña</label>
                        <input type="password" id="password" name="password" placeholder="Dejar vacío para no cambiar" 
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius: 5px;">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label for="confirmPassword" style="display:block; margin-bottom: 5px;">Confirmar Contraseña</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Repetir nueva contraseña" 
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius: 5px;">
                    </div>
                </div>
                
                <button type="submit" class="btn" 
                        style="background:#007bff; color:white; padding:12px 25px; border:none; border-radius: 5px; cursor:pointer; font-size: 1rem; transition: background 0.3s;">
                    Guardar Cambios
                </button>
            </form>
        </div>
    </main>

</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    }
</script>

</body>
</html>