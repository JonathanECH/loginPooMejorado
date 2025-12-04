<?php
session_start();
require_once '../../../src/php/requires_central.php';
require_once '../models/OrderModel.php';

class OrderController
{
    private $orderModel;

    public function __construct(OrderModel $model)
    {
        $this->orderModel = $model;
    }

    // ACCIÓN USUARIO: Solicitar Pedido
    private function handleCheckout()
    {
        if (!isset($_SESSION['user_id']))
            header("Location: ../../views/login.php");

        // CSRF Check aquí (recomendado)

        $result = $this->orderModel->createOrderFromCart($_SESSION['user_id']);

        if ($result === true) {
            $_SESSION['cart_success'] = "¡Pedido realizado con éxito! Espera la confirmación.";
            header("Location: ../../views/dashboard.php"); // O a una página de 'mis pedidos'
        } else {
            $_SESSION['cart_error'] = $result;
            header("Location: ../../views/carrito.php");
        }
    }

    // ACCIÓN ADMIN: Confirmar Pedido
    private function handleConfirmOrder()
    {
        if (($_SESSION['user_rol'] ?? '') !== 'administrador')
            die("Acceso denegado");

        $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);

        $result = $this->orderModel->confirmOrder($orderId);

        if ($result === true) {
            $_SESSION['admin_msg'] = "Pedido #$orderId confirmado y stock descontado.";
        } else {
            $_SESSION['admin_error'] = $result;
        }
        header("Location: ../../views/admin_pedidos.php"); // Vista de admin que crearemos
    }

    public function routeAction($action)
    {
        switch ($action) {
            case 'checkout':
                $this->handleCheckout();
                break;
            case 'confirm_order':
                $this->handleConfirmOrder();
                break;
        }
    }
}

// Bootstrap
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once '../../../src/php/requires_central.php';
    require_once '../models/OrderModel.php'; // Asegúrate de incluirlo en requires_central también

    $db = new Database();
    $orderModel = new OrderModel($db->getConnection());
    $controller = new OrderController($orderModel);

    $controller->routeAction($_POST['action'] ?? '');
}