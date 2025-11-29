<?php
require_once 'database.php';

class User
{
    private $conn;

    public function __construct(mysqli $connection)
    {
        // Inicializa la conexión a la DB a través de la clase Database
        $this->conn = $connection;
    }

    /**
     * Funcion que permite ejecutar consultas de lectura (Select).
     * @return mysqli_result|bool El resultado o false en caso de fallo.
     */
    private function runSelectStatement(string $sql, string $types, ...$params): mysqli_result|string|null
    {
        $stmt = $this->conn->prepare($sql);

        // 1. Manejo de error de PREPARACIÓN (Devuelve string si falla)
        if ($stmt === false) {
            // En producción, solo loguear el error y devolver 'false'
            return "Error de preparación de SELECT: " . $this->conn->error;
        }

        // 2. Manejo de error de BINDING
        if (!empty($params) && !$stmt->bind_param($types, ...$params)) {
            $stmt->close();
            return "Error de enlace de parámetros (bind_param): {$stmt->error}";
        }

        // 3. Manejo de error de EJECUCIÓN
        if (!$stmt->execute()) {
            $error_message = $stmt->error;
            $stmt->close();
            return "Error de ejecución de SELECT: " . $error_message;
        }

        $result = $stmt->get_result();
        $stmt->close();

        // Devolvemos el objeto mysqli_result o null si no hay resultado (get_result puede devolver null)
        return $result;
    }

    /**
     * Helper para ejecutar modificaciones(INSERT, UPDATE, DELETE).
     * @return bool|int True si éxito, el código de error 1062 para duplicados.
     */
    private function runDmlStatement(string $sql, string $types, ...$params): bool|int|string
    {
        $stmt = $this->conn->prepare($sql);

        // Si la preparación de la sentencia falla
        if ($stmt === false) {
            // Devuelve el mensaje de error de MySQLi, que es más útil que un simple 'false'
            return "Error de preparación: {$this->conn->error}";
        }

        $stmt->bind_param($types, ...$params);

        // Si la ejecución es exitosa
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        }

        // Si la ejecución falla, capturamos el código y el mensaje.
        $error_code = $this->conn->errno;
        $error_message = $this->conn->error;
        $stmt->close();

        // Revisamos el error 1062 (Duplicate entry for unique key)
        if ($error_code === 1062) {
            // Devolvemos el código numérico para que el Controlador lo maneje específicamente.
            return 1062;
        }

        // Fallo genérico: Devolvemos el mensaje de error SQL.
        // OJO: En un entorno de producción final, querrías loguear este error 
        // y devolver un mensaje genérico al usuario (ej: "Error interno del sistema").
        return "Error de ejecución: {$error_message}";
    }

    //Saber si un email ya existe en la DB
    public function emailExists($email)
    {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $result = $this->runSelectStatement($sql, "s", $email);

        // Si es una cadena, hubo un error de DB (devolver false para la lógica 'exists').
        if (is_string($result)) {
            // Loguear el error $result
            return false;
        }

        // Si es mysqli_result o null, procedemos con la lógica de negocio
        return $result && $result->num_rows > 0;
    }

    // FUNCIONES DE CRUD DE USUARIO

    /**
     * Obtiene los datos de un usuario por su ID.
     * @return array|bool Array con los datos del usuario, false si no existe.
     */
    public function getUserDataById($id)
    {
        $sql = "SELECT nombre, email FROM usuarios WHERE id = ?";
        $result = $this->runSelectStatement($sql, "i", $id);

        // 1. Manejar Error de DB
        if (is_string($result)) {
            // Loguear el error. Retornar false (no se encontraron datos).
            return false;
        }

        // 2. Lógica de negocio
        if ($result && $result->num_rows === 1)
            return $result->fetch_assoc();

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
        if ($result === 1062)
            return "Error: El correo electrónico ya está registrado.";

        // 4. Fallo genérico (cubre cualquier otro error, incluyendo el 'false' de la preparación)
        return "Error al registrar usuario. Inténtelo de nuevo más tarde.";
    }

    /** @return array|bool arreglo con el id del usuario y usuario si hubo éxito, un false si falla */
    public function login($email, $password)
    {
        // Se usa el método modularizado para SELECT
        $sql = "SELECT id, nombre, contrasenna FROM usuarios WHERE email = ?";
        $result = $this->runSelectStatement($sql, "s", $email);

        // 1. Manejo de Error de DB
        // Si $result es una cadena, significa que runSelectStatement() devolvió un error interno de la DB.
        if (is_string($result)) {
            // En un entorno real, aquí se debe LOGUEAR el contenido de $result.
            // Se devuelve false para indicar que el login falló debido a un error del sistema.
            return false;
        }

        // 2. Cláusula de Guarda: Si la consulta falló (ej. $result es null) O no se encontró 1 fila.
        // Esta línea solo se ejecuta si $result es un objeto mysqli_result o null.
        if (!$result || $result->num_rows !== 1)
            return false;

        // 3. Si el código llega aquí, tenemos 1 fila.
        $row = $result->fetch_assoc();

        // 4. Verificación de Contraseña
        if (password_verify($password, $row['contrasenna']))
            return ['user_id' => $row['id'], 'usuario' => $row['nombre']]; // Salida de Éxito

        // 5. Salida de Fracaso: Si la contraseña no es válida.
        return false;
    }

    /** @return bool|string si fue exitoso, string si es error */
    /* Se encarga de actualizar el usuario */
    public function update($id, $nombre, $email)
    {
        // 1. Verificar si el email ya existe para OTRO usuario
        $sql_check = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $result_check = $this->runSelectStatement($sql_check, "si", $email, $id);

        // 2. MANEJO DE ERRORES DEL SELECT DE VERIFICACIÓN
        // Si $result_check es una cadena, significa que runSelectStatement() devolvió un error interno (preparación, ejecución, etc.).
        if (is_string($result_check)) {
            // Loguear el error $result_check. 
            // Devolvemos un mensaje genérico al usuario en lugar del error detallado de SQL.
            return "Error interno del sistema al verificar el correo electrónico. Intente más tarde.";
        }

        // 3. CLÁUSULA DE GUARDA de LÓGICA DE NEGOCIO (Duplicado)
        // Se ejecuta solo si $result_check es un objeto mysqli_result o null.
        if ($result_check && $result_check->num_rows > 0) {
            return "Error: El correo electrónico ya está registrado por otra cuenta.";
        }

        // 4. Ejecutar la actualización (DML)
        $sql = "UPDATE usuarios SET nombre=?, email=? WHERE id=?";
        $result = $this->runDmlStatement($sql, "ssi", $nombre, $email, $id);

        // 5. Manejo de resultados de DML
        if ($result === true)
            return true;

        if ($result === 1062)
            return "Error: El nuevo correo electrónico ya está en uso por otro usuario.";

        // Si $result es una cadena (error de preparación/ejecución de DML)
        if (is_string($result))
            return "Error interno del sistema al actualizar datos. Detalle: {$result}";

        // Fallo genérico
        return "Error al actualizar usuario. Intente de nuevo.";
    }
}