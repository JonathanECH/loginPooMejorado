<?php
// src/php/controllers/ProductController.php
session_start();
// Asegúrate de que esta ruta cargue Database, DbModel, User, etc.
require_once '../../../src/php/requires_central.php'; 
require_once '../models/ProductModel.php';
require_once '../models/CartModel.php';

class ProductController
{
    private $productModel;
    private $cartModel;

    public function __construct(ProductModel $productModel, CartModel $cartModel)
    {
        $this->productModel = $productModel;
        $this->cartModel = $cartModel;
    }

    // ----------------------------------------------------
    // 1. Método para manejar la adición al carrito
    // ----------------------------------------------------
    private function handleAddToCart()
    {
        // 1. Guardia de seguridad: Debe estar logueado y no ser administrador
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol'] ?? 'cliente') === 'administrador') {
            $_SESSION['error_login'] = "Acceso denegado. Debes iniciar sesión como cliente.";
            header("Location: ../../views/login.php");
            exit;
        }

        $userId = $_SESSION['user_id'];
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        // 2. Validación Básica de Entrada
        if (!$productId || $productId <= 0 || !$quantity || $quantity <= 0) {
            $_SESSION['cart_error'] = "Cantidad o producto inválido.";
            header("Location: ../../views/dashboard.php#productos");
            exit;
        }

        // 3. Validación avanzada de Stock (Controlador)
        // Aunque el Modelo hace la verificación final (transaccional),
        // esta verificación reduce la carga innecesaria.
        $product_data = $this->productModel->getProductById($productId);
        
        if (is_string($product_data)) {
            $_SESSION['cart_error'] = "Error de sistema al verificar producto.";
            header("Location: ../../views/dashboard.php#productos");
            exit;
        }
        if (!$product_data || $product_data['stock_actual'] < $quantity) {
             $_SESSION['cart_error'] = "Stock insuficiente para la reserva solicitada.";
            header("Location: ../../views/dashboard.php#productos");
            exit;
        }

        // 4. Llamar a la lógica de reserva del Modelo (Añadir ítem y deducir stock)
        $result = $this->cartModel->addItem($userId, $productId, $quantity, $this->productModel);
        
        if ($result === true) {
            // Éxito: Redirigir al carrito o al dashboard con mensaje de éxito
            $_SESSION['cart_success'] = "¡Producto añadido a la reserva!";
            header("Location: ../../views/dashboard.php");
            exit;
        }

        // 5. Fracaso (Error de Stock o DB devuelto por el Modelo)
        $_SESSION['cart_error'] = $result; 
        header("Location: ../../views/dashboard.php");
        exit;
    }

    // ----------------------------------------------------
    // 2. Método para eliminar producto (ejemplo)
    // ----------------------------------------------------
    private function handleRemoveItem() 
    {
        // (Lógica para eliminar un producto del carrito y devolver stock)
    }

    // ----------------------------------------------------
    // 3. Método para actualizar cantidad (ejemplo)
    // ----------------------------------------------------
    private function handleUpdateQuantity() 
    {
        // (Lógica para actualizar la cantidad del carrito y ajustar stock)
    }


    // ----------------------------------------------------
    // 4. Enrutamiento de Acciones
    // ----------------------------------------------------
    public function routeAction($action)
    {
        switch ($action) {
            case "add_to_cart":
                $this->handleAddToCart();
                break;
            case "remove_item":
                $this->handleRemoveItem();
                break;
            case "update_quantity":
                $this->handleUpdateQuantity();
                break;
            default:
                $_SESSION['cart_error'] = "Acción de producto no reconocida.";
                header("Location: ../../views/dashboard.php");
                exit;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Conexión a la base de datos
    require_once '../../../src/php/requires_central.php';
    
    $db = new Database();
    $connection = $db->getConnection(); 

    // 2. Instanciación de Modelos e Inyección de Dependencia
    // Asegúrate de que ProductModel y CartModel existan y se hayan incluido
    $productModel = new ProductModel($connection);
    $cartModel = new CartModel($connection);

    // 3. Pasamos los Modelos al Controlador
    $controller = new ProductController($productModel, $cartModel);

    $action = $_POST["action"] ?? '';
    $controller->routeAction($action);
}