<?php
// src/php/models/DbModel.php

/**
 * Clase Base para Modelos de Base de Datos.
 * Contiene métodos genéricos para la ejecución segura de consultas SQL.
 */
class DbModel
{
    /**
     * @var mysqli $conn La conexión activa a la base de datos. 
     * protected permite que las clases hijas (User, Product) lo usen.
     */
    protected $conn;

    /**
     * Constructor que recibe la conexión (Inyección de Dependencia).
     */
    public function __construct(mysqli $connection)
    {
        $this->conn = $connection;
    }

    // ----------------------------------------------------
    // 1. SELECT STATEMENT (LECTURA)
    // ----------------------------------------------------

    /**
     * Helper para ejecutar consultas de lectura (Select) usando prepared statements.
     * @param string $sql La consulta SQL con placeholders (?).
     * @param string $types String con los tipos de parámetros ('s', 'i', 'd', etc.).
     * @param mixed ...$params Los parámetros a enlazar.
     * @return mysqli_result|string|null El resultado de la consulta o un mensaje de error (string).
     */
    protected function runSelectStatement(string $sql, string $types, ...$params): mysqli_result|string|null
    {
        $stmt = $this->conn->prepare($sql);

        // 1. Manejo de error de PREPARACIÓN (Devuelve string si falla)
        if ($stmt === false) {
            return "Error de preparación de SELECT: " . $this->conn->error;
        }

        // 2. ENLACE Y MANEJO DE ERROR DE BINDING (Guard clause)
        // Solo intenta bind_param si hay tipos y parámetros
        if (!empty($types) && !empty($params)) {
            if (!$stmt->bind_param($types, ...$params)) {
                $stmt->close();
                $error = $stmt->error ?: $this->conn->error;
                return "Error de enlace de parámetros (bind_param): {$error}";
            }
        }
        
        // 3. Manejo de error de EJECUCIÓN
        if (!$stmt->execute()) {
            $error_message = $stmt->error;
            $stmt->close();
            return "Error de ejecución de SELECT: " . $error_message;
        }

        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    // ----------------------------------------------------
    // 2. DML STATEMENT (MODIFICACIÓN: INSERT, UPDATE, DELETE)
    // ----------------------------------------------------

    /**
     * Helper para ejecutar modificaciones (DML) usando prepared statements.
     * @param string $sql La consulta SQL con placeholders (?).
     * @param string $types String con los tipos de parámetros ('s', 'i', 'd', etc.).
     * @param mixed ...$params Los parámetros a enlazar.
     * @return int|string El número de filas afectadas si éxito, el código de error 1062, o un string de error.
     */
    protected function runDmlStatement(string $sql, string $types, ...$params): int|string
    {
        $stmt = $this->conn->prepare($sql);

        // 1. Manejo de error de PREPARACIÓN
        if ($stmt === false) {
            return "Error de preparación de DML: {$this->conn->error}";
        }

        // 2. ENLACE Y MANEJO DE ERROR DE BINDING (Guard clause para evitar ValueError)
        if (!empty($types) && !empty($params)) {
            if (!$stmt->bind_param($types, ...$params)) {
                $stmt->close();
                $error = $stmt->error ?: $this->conn->error;
                return "Error de enlace de parámetros (bind_param): {$error}";
            }
        }

        // 3. Ejecución
        if ($stmt->execute()) {
            $filas_afectadas = $this->conn->affected_rows; 
            $stmt->close();
            
            // Retornamos el número de filas afectadas (fundamental para el contador de IP)
            return $filas_afectadas;
        }

        // 4. Manejo de errores de EJECUCIÓN
        $error_code = $this->conn->errno;
        $error_message = $this->conn->error;
        $stmt->close();

        // Revisamos el error 1062 (Duplicate entry for unique key)
        if ($error_code === 1062) {
            return 1062;
        }

        // Fallo genérico
        return "Error de ejecución de DML: {$error_message}";
    }
}