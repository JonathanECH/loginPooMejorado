<?php

require_once 'DbModel.php';

class ProductModel extends DbModel
{
    public function __construct(mysqli $connection)
    {
        // Llama al constructor del padre (DbModel) para inicializar $this->conn
        parent::__construct($connection);
    }

    /**
     * Obtiene todos los productos disponibles.
     * @return array|string Array de productos o mensaje de error.
     */
    public function getAllProducts()
    {
        // NOTA: Debes usar runSelectStatement si lo tienes disponible.
        // Si no lo tienes, usa mysqli simple, pero con precaución.
        $sql = "SELECT id, nombre, descripcion, precio, stock_actual, imagen_url FROM productos ORDER BY nombre ASC";

        // Simulación de uso de runSelectStatement (asumiendo que está disponible)
        $result = $this->runSelectStatement($sql, ""); // Sin parámetros, $types es vacío

        // Manejo de error de DB
        if (is_string($result)) {
            return "Error al cargar productos: {$result}";
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
        $sql = "SELECT id, nombre, descripcion, precio, stock_actual, imagen_url FROM productos WHERE id = ?";

        $result = $this->runSelectStatement($sql, "i", $productId);

        if (is_string($result)) {
            return "Error de DB al buscar producto: " . $result;
        }

        if ($result && $result->num_rows === 1) {
            return $result->fetch_assoc();
        }

        return false;
    }

    /**
     * Reduce o incrementa (si cantidad es negativa) el stock de un producto.
     * Utiliza una cláusula WHERE para verificar stock antes de la actualización (seguridad).
     * @return int|string Filas afectadas (1) o mensaje de error (string).
     */
    public function deductStock(int $productId, int $quantity): int|string
    {
        // Si la cantidad es positiva, la restamos (RESERVA).
        // Usamos la condición stock_actual >= ? para garantizar que no se reserve más de lo que hay.
        $sql = "UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?";
        $params = [$quantity, $productId];
        $types = "ii";

        // Añadimos una verificación de stock solo si la operación es una deducción (reserva)
        if ($quantity > 0) {
            $sql .= " AND stock_actual >= ?";
            $params[] = $quantity; // Agregamos el stock mínimo requerido
            $types = "iii"; // Ahora son tres enteros
        }

        // Si la cantidad es negativa, la sumamos (DEVOLUCIÓN de stock).
        // Si $quantity es -5, hacemos SET stock_actual = stock_actual - (-5) -> +5

        $result = $this->runDmlStatement($sql, $types, ...$params);

        if (is_int($result) && $result === 1) {
            return $result; // Éxito
        }

        if (is_string($result)) {
            return "Error de DB al modificar stock: {$result}";
        }

        // Si devuelve 0 (y la cantidad era positiva), significa que la condición WHERE falló (sin stock)
        if ($quantity > 0 && $result === 0) {
            return "No hay suficiente stock disponible para la reserva.";
        }

        // Retorno de 0 o error genérico
        return $result;
    }
}