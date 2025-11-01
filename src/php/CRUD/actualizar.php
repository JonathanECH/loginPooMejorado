<?php
// src/php/actualizar.php (Controlador refactorizado)
session_start();
require_once '../classes/user.php'; 

// 1. Guardia de seguridad
if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado. Debes iniciar sesión.");
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
    } else {
        // Fracaso: Muestra el error devuelto por el modelo
        die($result); 
    }
}
?>