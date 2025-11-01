<?php
session_start();
// Al querer cerrar sesión, simplemente la quitamos
unset($_SESSION['user_id']);
//$_SESSION = array();

// if (ini_get("session.use_cookies")) {
//     $params = session_get_cookie_params();
//     setcookie(session_name(), '', time() - 42000,
//         $params["path"], $params["domain"],
//         $params["secure"], $params["httponly"]
//     );
// }

// session_destroy();

// Y redirigimos al login
header("Location: ../../views/login.php");
