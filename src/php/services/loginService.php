<?php
// src/php/login.php (Controlador refactorizado)
session_start();
require_once '../classes/user.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recolección de datos
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 2. Ejecutar la lógica de la clase User
    $userModel = new User();
    $loginData = $userModel->login($email, $password);

    if ($loginData !== false) {
        // Éxito: Iniciar sesión y redirigir
        $_SESSION['usuario'] = $loginData['usuario'];
        $_SESSION['user_id'] = $loginData['user_id'];
        header("Location: ../../views/dashboard.php");
        exit;
    } else {
        // Fracaso: (Aquí puedes redirigir al login.html con un mensaje de error)
        $_SESSION['error_login'] = "Correo o contraseña incorrectos. Por favor, intente de nuevo.";
        // Redirigir a la pagina de login
        header("Location: ../../views/login.php");
        exit;
    }
}