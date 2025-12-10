<?php
// src/php/controllers/SettingsController.php
session_start();
require_once '../../../src/php/requires_central.php'; 
require_once '../models/SettingsModel.php';

class SettingsController
{
    private $settingsModel;

    public function __construct(SettingsModel $model)
    {
        $this->settingsModel = $model;
    }

    private function handleUpdateColors()
    {
        // 1. Seguridad: Solo Admin
        if (($_SESSION['user_rol'] ?? '') !== 'administrador') {
            die("Acceso denegado.");
        }
        
        // 2. CSRF
        $token = $_POST['csrf_token'] ?? '';
        if (!SecurityHelper::verifyCsrfToken($token)) {
            $_SESSION['update_error'] = "Error de seguridad (CSRF).";
            header("Location: ../../views/userdata.php");
            exit;
        }

        // 3. Recibir datos (AHORA SON 5)
        $primary = $_POST['primary_color'];
        $secondary = $_POST['secondary_color'];
        $text = $_POST['text_color'];
        $bg = $_POST['bg_color'];
        $card = $_POST['card_color']; // <--- AGREGADO

        // 4. Actualizar (Pasamos los 5 argumentos)
        $result = $this->settingsModel->updateThemeColors($primary, $secondary, $text, $bg, $card);

        if ($result === true) {
            $_SESSION['admin_msg'] = "¡Tema actualizado correctamente!";
        } else {
            $_SESSION['update_error'] = $result;
        }
        
        // Redirigir
        header("Location: ../../views/userdata.php?tab=theme");
        exit;
    }

    public function routeAction($action)
    {
        if ($action === 'update_theme') {
            $this->handleUpdateColors();
        }
    }
}

// Bootstrap
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $db = new Database();
    $model = new SettingsModel($db->getConnection());
    $controller = new SettingsController($model);
    $controller->routeAction($_POST['action'] ?? '');
}