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
}