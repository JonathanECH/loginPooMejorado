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
        // Manejo básico de error de preparación
        if ($stmt === false)
            return false;
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
        // Manejo básico de error de preparación
        if ($stmt === false)
            return false;

        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        // Revisamos el error 1062 (Duplicate entry for unique key)
        $error_code = $this->conn->errno;
        $stmt->close();
        return $error_code;
    }

    //Saber si un email ya existe en la DB
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

        // 1. Usar el método DML auxiliar para ejecutar la inserción
        $sql = "INSERT INTO usuarios (nombre, email, contrasenna) VALUES (?, ?, ?)";
        $result = $this->runDmlStatement($sql, "sss", $nombre, $email, $hashedPassword);

        // 2. Salida Anticipada si es exitoso
        if ($result === true)
            return true;

        // 3. Manejo de error específico (1062 - Duplicado)
        if ($result === 1062) {
            return "Error: El correo electrónico ya está registrado.";
        }

        // 4. Fallo genérico (cubre cualquier otro error, incluyendo el 'false' de la preparación)
        return "Error al registrar usuario. Inténtelo de nuevo más tarde.";
    }

    /** @return array|bool arreglo con el id del usuario y usuario si hubo éxito, un false si falla */
    public function login($email, $password)
    {
        // Se usa el método modularizado para SELECT
        $sql = "SELECT id, nombre, contrasenna FROM usuarios WHERE email = ?";
        $result = $this->runSelectStatement($sql, "s", $email);

        // 1. Cláusula de Guarda: Si la consulta falló O no se encontró 1 fila, retornar false.
        if (!$result || $result->num_rows !== 1)
            return false;

        // 2. Si el código llega aquí, tenemos 1 fila.
        $row = $result->fetch_assoc();

        // 3. Verificación de Contraseña (sin llaves en el if)
        if (password_verify($password, $row['contrasenna']))
            return ['user_id' => $row['id'], 'usuario' => $row['nombre']]; // Salida de Éxito

        // 4. Salida de Fracaso: Si la contraseña no es válida, llegamos a esta línea.
        return false;
    }

    /** @return bool|string si fue exitoso, string si es error */
    /* Se encarga de actualizar el usuario */
    public function update($id, $nombre, $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return "Error: Formato de email inválido.";

        // 2. NUEVA CLÁUSULA DE GUARDA: Verificar si el email ya existe para OTRO usuario
        $sql_check = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $result_check = $this->runSelectStatement($sql_check, "si", $email, $id);

        if ($result_check && $result_check->num_rows > 0) {
            return "Error: El correo electrónico ya está registrado por otra cuenta.";
        }

        // Se usa el método modularizado para DML (lenguaje de manipulación de datos)
        $sql = "UPDATE usuarios SET nombre=?, email=? WHERE id=?";
        $result = $this->runDmlStatement($sql, "ssi", $nombre, $email, $id);
        if ($result === true)
            return true;
        if ($result === 1062)
            return "Error: El nuevo correo electrónico ya está en uso por otro usuario.";
        return "Error al actualizar usuario.";
    }
}