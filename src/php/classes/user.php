<?php
require_once 'database.php';

class User
{
    private $conn;

    public function __construct()
    {
        // Inicializa la conexión a la DB a través de la clase Database
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Funcion que permite ejecutar consultas de lectura (Select).
     * @return mysqli_result|bool El resultado o false en caso de fallo.
     */
    private function runSelectStatement(string $sql, string $types, ...$params)
    {
        // ...$params es una sintaxis de PHP llamada "Splat Operator"
        // que convierte los argumentos sueltos en un array.

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            // Manejo básico de error de preparación
            return false;
        }
        // Usamos call_user_func_array para pasar dinámicamente el array de parámetros
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    /**
     * Helper para ejecutar modificaciones(INSERT, UPDATE, DELETE).
     * @return bool|int True si éxito, el código de error 1062 para duplicados.
     */
    private function runDmlStatement(string $sql, string $types, ...$params)
    {
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            // Manejo básico de error de preparación
            return false;
        }
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            // Revisamos el error 1062 (Duplicate entry for unique key)
            $error_code = $this->conn->errno;
            $stmt->close();
            return $error_code;
        }
    }

    public function emailExists($email)
    {
        // Reutilizamos el método modularizado de SELECT
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $result = $this->runSelectStatement($sql, "s", $email);

        // Si el resultado es una fila o más, el email existe.
        return $result && $result->num_rows > 0;
    }

    // FUNCIONES DE CRUD DE USUARIO

    /**
     * Obtiene los datos de un usuario por su ID.
     * @return array|bool Array con los datos del usuario, false si no existe.
     */
    public function getUserDataById($id)
    {
        // preparamos la petición sql
        $sql = "SELECT nombre, email FROM usuarios WHERE id = ?";
        $result = $this->runSelectStatement($sql, "i", $id);

        if ($result && $result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        return false;
    }

    /**
     * Registra un nuevo usuario.
     * @return bool|string True si es exitoso, un mensaje de error si falla.
     */
    public function register($nombre, $email, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (nombre, email, contrasenna) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        // Verifica si la preparación de la consulta falló (si tienes un método DML helper, úsalo)
        if ($stmt === false) {
            return "Error interno: Fallo al preparar la consulta.";
        }

        $stmt->bind_param("sss", $nombre, $email, $hashedPassword);

        if ($stmt->execute()) {
            return true;
        } else {
            if ($this->conn->errno === 1062) {
                return "Error: El correo electrónico ya está registrado.";
            }
            // Mensaje genérico para otros errores de la DB
            return "Error al registrar usuario. Inténtelo de nuevo más tarde.";
        }
    }

    /**
     * Intenta iniciar sesión.
     * @return array|bool Array con 'user_id' y 'usuario' si éxito, false si falla.
     */
    public function login($email, $password)
    {
        // Se usa el método modularizado para SELECT
        $sql = "SELECT id, nombre, contrasenna FROM usuarios WHERE email = ?";
        $result = $this->runSelectStatement($sql, "s", $email);

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['contrasenna'])) {
                return ['user_id' => $row['id'], 'usuario' => $row['nombre']];
            }
        }
        return false;
    }

    /**
     * Actualiza el nombre y email del usuario.
     * @return bool|string True si es exitoso, un mensaje de error si falla.
     */
    public function update($id, $nombre, $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Error: Formato de email inválido.";
        }

        // Se usa el método modularizado para DML (lenguaje de manipulación de datos)
        $sql = "UPDATE usuarios SET nombre=?, email=? WHERE id=?";
        $result = $this->runDmlStatement($sql, "ssi", $nombre, $email, $id);

        if ($result === true) {
            return true;
        } else if ($result === 1062) {
            return "Error: El nuevo correo electrónico ya está en uso por otro usuario.";
        } else {
            return "Error al actualizar usuario.";
        }
    }
}