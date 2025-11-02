<?php
session_start();
// 1. Asegúrate de incluir la nueva clase
include '../classes/user.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 2. Recolección y Sanitización
    $errors = [];
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password_original = $_POST['password'];
    $password_confirm = $_POST['confirmPassword'];

    if (empty($nombre)) {
        $errors[] = "Error: El nombre no puede estar vacío.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Error: Formato de email inválido.";
    }

    if (strlen($password_original) < 6) { // Ejemplo de otra validación
        $errors[] = "Error: La contraseña debe tener al menos 6 caracteres.";
    }

    if ($password_original !== $password_confirm) {
        $errors[] = "Error: Las contraseñas no coinciden.";
    }
    // 3. Crear el objeto y llamar al método SOLO si no hay errores locales.
    if (empty($errors)) {
        $userModel = new User();
        // Nota: Solo pasamos $password_original, ya que la validación de confirmación se hizo arriba.
        $result = $userModel->register($nombre, $email, $password_original);

        if ($result === true) {
            header("Location: ../../views/login.php?register=success");
            exit;
        } else {
            $errors[] = $result;
        }
    }

    // 4. Salida Única de Fracaso: Guardar TODOS los errores recolectados

    // Guardar errores y datos del formulario en la sesión
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = [
        'nombre' => $nombre,
        'email' => $email
    ];

    // Redirigir de vuelta al formulario de registro (register.php)
    header("Location: ../../views/register.php");
    exit;
}

header("Location: ../../views/register.php"); 
exit;