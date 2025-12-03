<?php
// src/php/models/ProductModel.php

require_once 'DbModel.php';

class ProductModel extends DbModel
{
    public function __construct(mysqli $connection)
    {
        // Llama al constructor del padre (DbModel) para inicializar $this->conn
        parent::__construct($connection);
    }

    // --- MÉTODOS DE LECTURA (Catálogo) ---

    /**
     * Obtiene todos los productos del catálogo, incluyendo el stock disponible (calculado).
     * @return array|string Array de productos o mensaje de error.
     */
    public function getAllProducts(): array|string
    {
        $sql = "
            SELECT 
                id, 
                nombre, 
                descripcion, 
                precio, 
                stock_actual,
                stock_comprometido,
                -- CALCULAR EL STOCK DISPONIBLE (lo que el cliente puede reservar)
                (stock_actual - stock_comprometido) AS stock_disponible, 
                imagen_url 
            FROM 
                productos 
            ORDER BY 
                nombre ASC
        ";

        $result = $this->runSelectStatement($sql, ""); 

        if (is_string($result)) {
            return "Error al cargar el catálogo: {$result}"; 
        }

        $products = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }

    /**
     * Obtiene un producto por su ID para ver detalles o verificar stock.
     * @return array|string|false Datos del producto, error de DB (string), o false si no existe.
     */
    public function getProductById(int $productId): array|string|false
    {
        $sql = "SELECT id, nombre, descripcion, precio, stock_actual, stock_comprometido, imagen_url FROM productos WHERE id = ?";

        $result = $this->runSelectStatement($sql, "i", $productId);

        if (is_string($result)) {
            return "Error de DB al buscar producto: " . $result;
        }

        if ($result && $result->num_rows === 1) {
            return $result->fetch_assoc();
        }

        return false;
    }

    // --- MÉTODOS DE MANIPULACIÓN DE STOCK (Reservas y Administración) ---

    /**
     * Añade o remueve stock del comprometido. Es el método clave de la reserva.
     * @param int $quantity Cantidad a sumar (positiva) o restar (negativa).
     * @return int|string Filas afectadas (1) o mensaje de error (string).
     */
    public function manipulateCompromisedStock(int $productId, int $quantity): int|string
    {
        // Esta lógica maneja la reserva (quantity > 0) y la liberación (quantity < 0).

        $sql = "UPDATE productos SET stock_comprometido = stock_comprometido + ? WHERE id = ?";
        $params = [$quantity, $productId];
        $types = "ii";
        
        // Cláusula de seguridad: Si intentamos AUMENTAR el stock comprometido (reservar),
        // debemos verificar que el stock actual sea mayor o igual al nuevo stock comprometido.
        if ($quantity > 0) {
             // stock_actual debe ser mayor o igual al stock_comprometido que ya tengo + la nueva cantidad
             $sql .= " AND stock_actual >= (stock_comprometido + ?)";
             $params[] = $quantity; // Agregamos la cantidad que queremos sumar al comprometido
             $types = "iii";
        }
        
        // Si $quantity es negativa (liberación/devolución), no necesitamos la cláusula AND stock_actual >=.

        $result = $this->runDmlStatement($sql, $types, ...$params);

        if (is_int($result) && $result === 1) {
            return $result; // Éxito
        }
        
        if (is_string($result)) {
            return "Error de DB al modificar stock comprometido: {$result}";
        }
        
        // Si devuelve 0 y la cantidad era positiva, significa que la condición WHERE falló (sin stock)
        if ($quantity > 0 && $result === 0) {
             return "No hay suficiente stock disponible para la reserva.";
        }
        
        // Retorno de 0 o error genérico
        return $result;
    }
    
    
    /**
     * Actualiza el stock físico de forma directa (Admin Action: update_stock).
     * @return bool|string True si éxito, o error de DB (string).
     */
    public function updateStockDirect(int $productId, int $newStock): bool|string
    {
        // Esta actualización debe ser segura para no hacer el stock_actual menor que el stock_comprometido
        $sql = "UPDATE productos SET stock_actual = ? WHERE id = ? AND stock_actual >= stock_comprometido";
        $result = $this->runDmlStatement($sql, "ii", $newStock, $productId);
        
        if (is_string($result)) {
            return "Error de DB al actualizar stock: {$result}";
        }
        
        if ($result === 0) {
             return "No se pudo reducir el stock: El nuevo valor es menor que el stock comprometido.";
        }

        return true; 
    }

    /**
     * Método para eliminar un producto (Admin Action: delete_product).
     * @return bool|string True si éxito, o error de DB (string).
     */
    public function deleteProduct(int $productId): bool|string
    {
        // NOTA: Se asume que la FK en detalles_carrito previene la eliminación si hay reservas activas.
        $sql = "DELETE FROM productos WHERE id = ?";
        $result = $this->runDmlStatement($sql, "i", $productId);
        
        if (is_string($result)) {
            // Capturamos el error de la DB (ej. error 1451: constraint fail)
            return "Error de DB al eliminar: {$result}";
        }
        
        if ($result === 0) {
            return "El producto no existe o no se pudo eliminar (verifique reservas activas).";
        }
        
        return true;
    }
    
    // Aquí irían otros métodos como confirmSaleDeductStock() para la deducción final del stock_actual.
}