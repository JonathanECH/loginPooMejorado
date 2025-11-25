<?php
// src/php/actualizar.php (Controlador refactorizado)

// Traigo la sesión con los datos del usuario
session_start();
require_once '../classes/user.php';

// 1. Guardia de seguridad
//Si no hay una sección, detengo la ejecución
if (!isset($_SESSION['user_id'])) {
    //Mando un mensaje de error por acceso no autorizado
    $_SESSION['error_login'] = "Acceso denegado. Debes iniciar sesión para actualizar tu perfil.";

    //Reedirigir a la pagina de login
    header("Location: ../../views/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Recolección de datos
    $id = $_SESSION['user_id'];
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];

    // 3. Ejecutar la lógica de la clase User
    $userModel = new User();
    $result = $userModel->update($id, $nombre, $email);

    if ($result === true) {
        // Éxito: Actualizar el nombre en la sesión y redirigir
        $_SESSION['usuario'] = $nombre;
        header("Location: ../../views/dashboard.php?update=success");
        exit;
    }
    
    // Fracaso: Muestra el error devuelto por el modelo
    $_SESSION['update_error'] = $result;
    header("Location: ../../views/dashboard.php?update=fail");
    exit;
}