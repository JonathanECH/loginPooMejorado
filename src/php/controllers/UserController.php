<?php
session_start();
require_once '../classes/user.php';

if($_SERVER["REQUEST_METHOD"]){
    $action = $_POST["action"] ?? '';
    $userModel = new User();

    switch($action){
        case "register":
            
            break;
        case "update":
            break;
        default:
            // Acción no reconocida
            $_SESSION['error_login'] = "Acción no reconocida.";
            header("Location: ../../views/login.php");
            exit;
    }
}
