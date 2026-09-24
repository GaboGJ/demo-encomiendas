<?php
class Auth_model {
    private $pdo;

    public function __construct() {
        global $pdo; // Hereda la conexión global establecida en config.php
        $this->pdo = $pdo;
    }

    /**
     * Buscar un usuario por su correo electrónico o nombre de usuario
     * @param string $correo
     * @return mixed Devuelve un array con los datos del usuario o false si no existe
     */
    public function obtenerUsuarioPorCorreo($correo) {
        try {
            // Ajusta la consulta según el nombre real de tu tabla de usuarios
            $sql = "SELECT * FROM usuarios WHERE correo = :correo OR usuario = :usuario LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':correo' => $correo,
                ':usuario' => $correo
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejo de errores de base de datos
            return false;
        }
    }

    /**
     * Registrar un nuevo cliente en el sistema
     * @param array $datos
     * @return boolean Devuelve true si se registra con éxito o false en caso contrario
     */
    public function registrarCliente($datos) {
        try {
            $sql = "INSERT INTO usuarios (nombres, apellidos, telefono, documento, correo, password, rol, creado_en) 
                    VALUES (:nombres, :apellidos, :telefono, :documento, :correo, :password, 'cliente', NOW())";
            $stmt = $this->pdo->prepare($sql);
            
            return $stmt->execute([
                ':nombres' => $datos['nombres'],
                ':apellidos' => $datos['apellidos'],
                ':telefono' => $datos['telefono'],
                ':documento' => $datos['documento'],
                ':correo' => $datos['correo'],
                ':password' => password_hash($datos['password'], PASSWORD_BCRYPT) // Contraseña encriptada por seguridad
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>