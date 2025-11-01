<?php
// 1. Asegúrate de incluir la nueva clase
include '../classes/user.php'; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 2. Recolección y Sanitización
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password_original = $_POST['password'];
    $password_confirm = $_POST['confirmPassword'];

    // 3. Crear el objeto Usuario y llamar al método
    $userModel = new User();
    $result = $userModel->register($nombre, $email, $password_original, $password_confirm);

    if ($result === true) {
        // Éxito: Redirigir al login
        header("Location: ../../views/login.php?register=success");
        exit;
    } else {
        // Fracaso: Muestra el mensaje de error devuelto por la clase
        die($result); 
    }
}