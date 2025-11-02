<?php
session_start();
// Al querer cerrar sesión, simplemente la quitamos
unset($_SESSION['user_id']);
unset($_SESSION['usuario']);

// 3. Eliminar TODAS las variables de sesión
$_SESSION = array();

// 4. Invalidar la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Destruir la sesión en el servidor
session_destroy();
header("Location: ../../views/login.php");
