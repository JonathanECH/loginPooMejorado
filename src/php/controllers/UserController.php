<?php
// src/php/controllers/UserController.php
session_start();
require_once '../../../src/php/requires_central.php';
// TEMPORAL: Verifica si el controlador se ejecuta
file_put_contents('debug_log.txt', 'Controlador ejecutado: ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
// TEMPORAL: Muestra qué acción se recibió
file_put_contents('debug_log.txt', 'Acción recibida: ' . ($_POST['action'] ?? 'NULA') . "\n", FILE_APPEND);

class UserController
{

    private $userModel;
    private $validator;
    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
        $this->validator = new UserValidator($userModel);
    }

    // ----------------------------------------------------
    // 1. Método para manejar el registro
    // ----------------------------------------------------
    private function handleRegister()
    {
        // 1. Delegar toda la validación al servicio
        $errors = $this->validator->validateRegistration($_POST);

        // Recolectar datos sanitizados para la DB o crudos para rellenar formulario
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password_original = $_POST['password'];

        // 2. Llamar al método SOLO si no hay errores
        if (empty($errors)) {
            $result = $this->userModel->register($nombre, $email, $password_original);

            if ($result === true) {
                header("Location: ../../views/login.php?register=success");
                exit;
            }
            $errors[] = $result; // Captura el error de la DB
        }

        // 3. Salida de Fracaso
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = ['nombre' => $nombre, 'email' => $email];
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
        // Asegurarse de que el usuario esté logueado
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_login'] = "Acceso denegado. Debes iniciar sesión para actualizar tu perfil.";
            header("Location: ../../views/login.php");
            exit;
        }

        $id = $_SESSION['user_id'];

        // 1. Delegar la validación al servicio
        $errors = $this->validator->validateUpdate($id, $_POST);

        $email = $_POST['email'];
        $nombre = $_POST['nombre'];

        if (!empty($errors)) {
            // Fracaso en la validación local
            $_SESSION['update_error'] = implode(' ', $errors); // Une los errores para el mensaje
            header("Location: ../../views/dashboard.php?update=fail");
            exit;
        }

        // 2. Ejecutar la lógica del modelo
        $result = $this->userModel->update($id, $nombre, $email);

        if ($result === true) {
            $_SESSION['usuario'] = $nombre;
            header("Location: ../../views/dashboard.php?update=success");
            exit;
        }

        // Fracaso del modelo
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
    // Asegúrate de que requires_central.php haya cargado la clase Database y User
    require_once '../../../src/php/requires_central.php';

    // 1. Conexión a la base de datos
    $db = new Database();
    $connection = $db->getConnection(); // Obtiene el objeto mysqli/PDO

    // 2. Aplicamos la conexión al modelo
    $userModel = new User($connection); // <- CAMBIO AQUÍ

    // 3. Pasamos el modelo al controlador
    $controller = new UserController($userModel);

    $action = $_POST["action"] ?? '';
    $controller->routeAction($action);
}