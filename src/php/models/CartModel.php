<?php
// src/php/models/CartModel.php

require_once 'DbModel.php';
require_once 'ProductModel.php'; // Necesario para gestionar el stock

class CartModel extends DbModel
{
    public function __construct(mysqli $connection)
    {
        // Hereda la conexión y los helpers runDml/runSelect
        parent::__construct($connection);
    }

    // ----------------------------------------------------
    // LÓGICA DE LIMPIEZA DE CARROS EXPIRADOS (Stock Release)
    // ----------------------------------------------------

    /**
     * Libera el stock de carritos inactivos que excedieron el tiempo límite (30 minutos).
     * Nota: ProductModel se instancia internamente para la liberación de stock.
     * @param int $expirationMinutes Tiempo en minutos antes de expirar.
     * @return bool True si la limpieza se realizó correctamente.
     */
    public function clearExpiredCarts(int $expirationMinutes = 30): bool
    {
        // 1. Calcular la marca de tiempo de expiración
        $expire_time = date('Y-m-d H:i:s', strtotime("-{$expirationMinutes} minutes"));

        // 2. Encontrar carritos expirados (usando fecha_actualizacion)
        $sql_expired_carts = "SELECT c.id, d.producto_id, d.cantidad 
                          FROM carritos_activos c
                          JOIN detalles_carrito d ON c.id = d.carrito_id
                          WHERE c.fecha_actualizacion < ?";

        $result = $this->runSelectStatement($sql_expired_carts, "s", $expire_time);

        if (is_string($result)) {
            error_log("Error de DB al buscar carritos expirados: " . $result);
            return false;
        }

        if ($result->num_rows > 0) {
            // Instanciar ProductModel para liberar el stock de forma segura
            // Nota: Le pasamos $this->conn para que use la misma conexión inyectada.
            $productModel = new ProductModel($this->conn);

            while ($row = $result->fetch_assoc()) {
                // 3. Liberar Stock: Usa cantidad negativa para devolver el stock al inventario
                // El resultado se ignora aquí, ya que el objetivo principal es limpiar el carrito.
                $productModel->deductStock($row['producto_id'], -$row['cantidad']);
            }

            // 4. Eliminar los carritos expirados de las tablas activas
            $sql_delete_carts = "DELETE FROM carritos_activos WHERE fecha_actualizacion < ?";
            $this->runDmlStatement($sql_delete_carts, "s", $expire_time);
        }

        return true;
    }


    // ----------------------------------------------------
    // LÓGICA DE GESTIÓN DEL CARRITO
    // ----------------------------------------------------

    /**
     * Obtiene el ID del carrito activo para un usuario, creándolo si no existe.
     * @return int|string El ID del carrito o un error de DB (string).
     */
    public function getOrCreateCartId(int $userId): int|string
    {
        // 1. Intentar encontrar un carrito activo
        $sql_select = "SELECT id FROM carritos_activos WHERE user_id = ?";
        $result = $this->runSelectStatement($sql_select, "i", $userId);

        if (is_string($result)) {
            return "Error al buscar carrito: {$result}";
        }

        if ($result && $result->num_rows > 0) {
            return (int) $result->fetch_assoc()['id'];
        }

        // 2. Si no existe, crearlo
        // NOTA: fecha_actualizacion se llenará automáticamente con CURRENT_TIMESTAMP
        $sql_insert = "INSERT INTO carritos_activos (user_id) VALUES (?)";
        $result_insert = $this->runDmlStatement($sql_insert, "i", $userId);

        if (is_string($result_insert)) {
            return "Error al crear carrito: {$result_insert}";
        }

        // Recuperar el último ID insertado
        return (int) $this->conn->insert_id;
    }

    /**
     * Añade un producto al carrito del usuario, deduciendo stock inmediatamente.
     * @return bool|string True si éxito, o mensaje de error (string).
     */
    public function addItem(int $userId, int $productId, int $quantity, ProductModel $productModel): bool|string
    {
        // Paso 1: Obtener o crear el ID del carrito
        $cartId = $this->getOrCreateCartId($userId);
        if (is_string($cartId)) {
            return $cartId; // Error de DB al obtener/crear carrito
        }

        // Paso 2: Deducir stock (Lógica crítica)
        // Esto verifica stock, lo decrementa en productos, o devuelve error.
        $stock_deduction_result = $productModel->deductStock($productId, $quantity);

        if (is_string($stock_deduction_result)) {
            return $stock_deduction_result; // Devuelve el mensaje de error de stock o DB.
        }

        // Paso 3: Añadir/Actualizar el detalle del carrito

        // --- 3a. Intentar actualizar si el ítem ya está en el carrito ---
        $sql_update_detail = "
        UPDATE detalles_carrito d
        JOIN carritos_activos c ON d.carrito_id = c.id
        SET d.cantidad = d.cantidad + ?
        WHERE c.user_id = ? AND d.producto_id = ?
    ";
        $affected_rows = $this->runDmlStatement($sql_update_detail, "iii", $quantity, $userId, $productId);

        if (is_string($affected_rows)) {
            // ERROR: Si falla el UPDATE, debemos DEVOLVER EL STOCK DEDUCIDO antes de salir.
            $productModel->deductStock($productId, -$quantity);
            return "Error de DB al actualizar carrito: {$affected_rows}";
        }

        if ($affected_rows === 0) {
            // --- 3b. Si el UPDATE no afectó filas, hacer INSERT ---
            $sql_insert_detail = "
            INSERT INTO detalles_carrito (carrito_id, producto_id, cantidad) 
            VALUES (?, ?, ?)
        ";
            $insert_result = $this->runDmlStatement($sql_insert_detail, "iii", $cartId, $productId, $quantity);

            if (is_string($insert_result)) {
                // ERROR: Si el INSERT falla, debemos DEVOLVER EL STOCK DEDUCIDO.
                $productModel->deductStock($productId, -$quantity);
                return "Error de DB al añadir ítem: {$insert_result}";
            }
        }

        // Paso 4: Actualizar la fecha del carrito (para la expiración)
        $sql_touch = "UPDATE carritos_activos SET fecha_actualizacion = NOW() WHERE id = ?";
        $this->runDmlStatement($sql_touch, "i", $cartId);

        return true; // Éxito total
    }

    /**
     * Obtiene todos los ítems en el carrito del usuario.
     * @return array|string Array de ítems o mensaje de error de DB (string).
     */
    public function viewCart(int $userId): array|string
    {
        // Consulta compleja para unir carrito, detalles y productos
        $sql = "
            SELECT
                d.producto_id,
                p.nombre,
                p.precio,
                p.imagen_url,
                d.cantidad,
                p.stock_actual AS stock_disponible,
                (d.cantidad * p.precio) AS subtotal
            FROM carritos_activos c
            JOIN detalles_carrito d ON c.id = d.carrito_id
            JOIN productos p ON d.producto_id = p.id
            WHERE c.user_id = ?
        ";

        $result = $this->runSelectStatement($sql, "i", $userId);

        if (is_string($result)) {
            return "Error al cargar el carrito: {$result}";
        }

        $items = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        }
        return $items;
    }
}