<?php
// src/php/controllers/UserController.php

session_start();
require_once '../models/user.php';
// TEMPORAL: Verifica si el controlador se ejecuta
file_put_contents('debug_log.txt', 'Controlador ejecutado: ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
// TEMPORAL: Muestra qué acción se recibió
file_put_contents('debug_log.txt', 'Acción recibida: ' . ($_POST['action'] ?? 'NULA') . "\n", FILE_APPEND);

class UserController
{

    private $userModel;
    public function __construct(User $userModel)
    {
        $this->userModel = $userModel; 
    }

    // ----------------------------------------------------
    // 1. Método para manejar el registro
    // ----------------------------------------------------
    private function handleRegister()
    {
        $errors = [];
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password_original = $_POST['password'];
        $password_confirm = $_POST['confirmPassword'];

        // Verifico los campos del formulario
        if (empty($nombre))
            $errors[] = "Error: El nombre no puede estar vacío.";

        if (strlen($nombre) < 3 || strlen($nombre) > 64)
            $errors[] = "Error: El nombre debe tener entre 3 y 64 caracteres.";

        // Valido si el email no es valido, para luego validad si existe
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Error: Formato de email inválido.";
        } else {
            if (strlen($email) > 254)
                $errors[] = "Error: El correo electrónico es demasiado largo.";
            // Verifico si el email ya existe
            if ($this->userModel->emailExists($email))
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
            $result = $this->userModel->register($nombre, $email, $password_original); // Llama a User::register()

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
    }

    private function handleLogin()
    {
        // 1. Recolección de datos
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // 2. Ejecutar la lógica de la clase User (Modelo)
        $loginData = $this->userModel->login($email, $password); // Llama a User::login()

        if ($loginData !== false) {
            // Éxito: Iniciar sesión y redirigir al dashboard
            $_SESSION['usuario'] = $loginData['usuario'];
            $_SESSION['user_id'] = $loginData['user_id'];
            header("Location: ../../views/dashboard.php");
            exit;
        }

        // Fracaso: Redirigir al login con un mensaje de error
        $_SESSION['error_login'] = "Correo o contraseña incorrectos. Por favor, intente de nuevo.";
        header("Location: ../../views/login.php");
        exit;
    }

    private function handleLogout()
    {
        // 1. Limpiar variables específicas de la sesión
        unset($_SESSION['user_id']);
        unset($_SESSION['usuario']);

        // 2. Eliminar TODAS las variables de sesión
        $_SESSION = [];

        // 3. Invalidar la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // 4. Destruir la sesión en el servidor
        session_destroy();

        // 5. Redirigir a la página de inicio de sesión
        header("Location: ../../views/login.php");
        exit;
    }

    //Método del controlador para actualizar datos del usuario
    private function handleUpdate()
    {
        $email = $_POST['email'];
        // AGREGAR: Validación de formato de email en el Controlador
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['update_error'] = "Error: Formato de email inválido.";
            header("Location: ../../views/dashboard.php?update=fail");
            exit;
        }

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

        // 3. Ejecutar la lógica de la clase User
        $result = $this->userModel->update($id, $nombre, $email);

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

    //Metodo para determinar la acción a ejecutar según la ruta
    public function routeAction($action)
    {
        switch ($action) {
            case "register":
                $this->handleRegister();
                break;
            case "update":
                $this->handleUpdate();
                break;
            case "login":
                $this->handleLogin();
                break;
            case "logout":
                $this->handleLogout();
                break;
            default:
                $_SESSION['error_login'] = "Acción no reconocida.";
                header("Location: ../../views/login.php");
                exit;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $userModel = new User();
    $controller = new UserController($userModel);

    $action = $_POST["action"] ?? '';
    $controller->routeAction($action);
}