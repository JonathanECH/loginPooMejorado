<?php
// src/php/models/Product.php

class ProductModel
{
    private $conn;

    public function __construct(mysqli $connection)
    {
        $this->conn = $connection;
        // Asume que los helpers runSelectStatement y runDmlStatement 
        // son heredados o importados aquí.
        // Si no los puedes heredar, tendrías que copiarlos o hacer que Product 
        // use la misma lógica de consultas preparadas que User.
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
            return "Error al cargar productos: " . $result;
        }

        $products = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }
}