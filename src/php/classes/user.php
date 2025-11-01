<?php 
require_once 'database.php'; 

class User {
    private $conn;

    public function __construct() {
        // Inicializa la conexión a la DB a través de la clase Database
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Registra un nuevo usuario.
     * @return bool|string True si es exitoso, un mensaje de error si falla.
     */
    public function register($nombre, $email, $password, $confirmPassword) {
        // Lógica de validación
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Error: Formato de email inválido.";
        }
        if ($password !== $confirmPassword) {
            return "Error: Las contraseñas no coinciden.";
        }

        // Ejecución de la consulta
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (nombre, email, contrasenna) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $nombre, $email, $hashedPassword);

        if ($stmt->execute()) {
            return true;
        } else {
            if ($this->conn->errno === 1062) {
                return "Error: El correo electrónico ya está registrado.";
            }
            return "Error al registrar usuario.";
        }
    }

    /**
     * Intenta iniciar sesión.
     * @return array|bool Array con 'user_id' y 'usuario' si éxito, false si falla.
     */
    public function login($email, $password) {
        $sql = "SELECT id, nombre, contrasenna FROM usuarios WHERE email = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
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
    public function update($id, $nombre, $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Error: Formato de email inválido.";
        }
        
        $sql = "UPDATE usuarios SET nombre=?, email=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $nombre, $email, $id);

        if ($stmt->execute()) {
            return true;
        } else {
             if ($this->conn->errno === 1062) {
                 return "Error: El nuevo correo electrónico ya está en uso por otro usuario.";
            }
            return "Error al actualizar usuario.";
        }
    }
    
    /**
     * Obtiene los datos de un usuario por su ID.
     * @return array|bool Array con los datos del usuario, false si no existe.
     */
    public function getUserDataById($id) {
        $sql = "SELECT nombre, email FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        return false;
    }
}