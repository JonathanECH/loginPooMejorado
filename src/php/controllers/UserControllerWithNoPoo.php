<?php
session_start();
require_once '../classes/user.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? '';
    $userModel = new User();

    switch ($action) {
        case "register":
            //MÉTODO CREATE PARA REGISTRAR UN USUARIO
            // 2. Recolección y Sanitización
            $errors = [];
            $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password_original = $_POST['password'];
            $password_confirm = $_POST['confirmPassword'];

            // Verifico los campos del formulario
            if (empty($nombre))
                $errors[] = "Error: El nombre no puede estar vacío.";

            // Valido si el email no es valido, para luego validad si existe
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Error: Formato de email inválido.";
            } else {
                // Verifico si el email ya existe
                if ($userModel->emailExists($email))
                    $errors[] = "Error: El correo electrónico ya está registrado.";
            }

            if (strlen($password_original) < 6)  // Ejemplo de otra validación
                $errors[] = "Error: La contraseña debe tener al menos 6 caracteres.";

            if ($password_original !== $password_confirm)
                $errors[] = "Error: Las contraseñas no coinciden.";

            // 3. Llamar al método SOLO si no hay errores locales.
            if (empty($errors)) {
                // La instancia $userModel ya existe
                // Nota: Solo pasamos $password_original, ya que la validación de confirmación se hizo arriba.
                $result = $userModel->register($nombre, $email, $password_original); // Llama a User::register()

                if ($result === true) {
                    header("Location: ../../views/login.php?register=success");
                    exit;
                }
                // Captura el error de duplicado (1062) si la base de datos lo detecta
                $errors[] = $result;
            }

            // 4. Salida Única de Fracaso: Guardar TODOS los errores recolectados

            // Guardar errores y datos del formulario en la sesión
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = [
                'nombre' => $nombre,
                'email' => $email
            ];
            // Redirigir al formulario de registro al haber fallado la validación
            header("Location: ../../views/register.php");
            exit;

        case "update":
            //Si no hay una sección, detengo la ejecución
            if (!isset($_SESSION['user_id'])) {
                //Mando un mensaje de error por acceso no autorizado
                $_SESSION['error_login'] = "Acceso denegado. Debes iniciar sesión para actualizar tu perfil.";

                //Reedirigir a la pagina de login
                header("Location: ../../views/login.php");
                exit;
            }

            // 2. Recolección de datos
            $id = $_SESSION['user_id'];
            $nombre = $_POST['nombre'];
            $email = $_POST['email'];

            // 3. Ejecutar la lógica de la clase User
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
        default:
            // Acción no reconocida
            $_SESSION['error_login'] = "Acción no reconocida.";
            header("Location: ../../views/login.php");
            exit;
    }
}
