<?php
class Usuarios_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /** SELECT base: usuario + persona + rol + sucursal (sin password). */
    private function baseSelect() {
        return "SELECT 
                    u.id_usuario,
                    u.id_persona,
                    u.id_sucursal,
                    u.id_rol,
                    CAST(IFNULL(u.estado_usuario, 1) AS UNSIGNED) AS estado_usuario,
                    u.create_usuario,
                    u.update_usuario,
                    p.carnet_persona,
                    p.nombre_persona,
                    p.apellido_paterno_persona,
                    p.apellido_materno_persona,
                    p.telefono_persona,
                    p.direccion_persona,
                    CONCAT(p.nombre_persona, ' ', p.apellido_paterno_persona, ' ', IFNULL(p.apellido_materno_persona, '')) AS nombre_completo,
                    r.nombre_rol,
                    s.nombre_sucursal,
                    s.ciudad_sucursal
                FROM usuarios u
                INNER JOIN personas p ON u.id_persona = p.id_persona
                INNER JOIN roles r ON u.id_rol = r.id_rol
                INNER JOIN sucursales s ON u.id_sucursal = s.id_sucursal ";
    }

    /** Usuarios no eliminados (activos e inactivos). */
    public function getUsuarios() {
        try {
            $sql = $this->baseSelect() . "WHERE u.delete_usuario IS NULL ORDER BY u.id_usuario DESC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerUsuario($id_usuario) {
        try {
            $sql = $this->baseSelect() . "WHERE u.id_usuario = :id AND u.delete_usuario IS NULL LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id_usuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getRolesActivos() {
        try {
            $sql = "SELECT id_rol, nombre_rol FROM roles 
                    WHERE (estado_rol = 1 OR estado_rol IS NULL) AND delete_rol IS NULL
                    ORDER BY nombre_rol ASC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getSucursalesActivas() {
        try {
            $sql = "SELECT id_sucursal, nombre_sucursal, ciudad_sucursal FROM sucursales 
                    WHERE (estado_sucursal = 1 OR estado_sucursal IS NULL) AND delete_sucursal IS NULL
                    ORDER BY ciudad_sucursal ASC, nombre_sucursal ASC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * ¿La persona ya tiene un usuario vigente (no eliminado)?
     * $excluir_id permite ignorar el propio usuario al editar.
     */
    public function existeUsuarioVigentePorPersona($id_persona, $excluir_id = 0) {
        $sql = "SELECT COUNT(*) FROM usuarios 
                WHERE id_persona = :p AND delete_usuario IS NULL AND id_usuario != :x";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':p' => $id_persona, ':x' => $excluir_id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /** Inserta el usuario. Los errores de BD suben al controlador. */
    public function crearUsuario($data) {
        $sql = "INSERT INTO usuarios 
                    (id_persona, id_sucursal, id_rol, password_usuario, estado_usuario, create_usuario)
                VALUES 
                    (:persona, :sucursal, :rol, :password, 1, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':persona'  => $data['id_persona'],
            ':sucursal' => $data['id_sucursal'],
            ':rol'      => $data['id_rol'],
            ':password' => password_hash($data['password'], PASSWORD_BCRYPT)
        ]);
        return $this->pdo->lastInsertId();
    }

    /** Actualiza rol/sucursal y, solo si se envió, la contraseña. */
    public function actualizarUsuario($id_usuario, $data) {
        $sql = "UPDATE usuarios SET id_sucursal = :sucursal, id_rol = :rol, update_usuario = NOW()";
        $params = [
            ':sucursal' => $data['id_sucursal'],
            ':rol'      => $data['id_rol'],
            ':id'       => $id_usuario
        ];

        if (!empty($data['password'])) {
            $sql .= ", password_usuario = :password";
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql .= " WHERE id_usuario = :id AND delete_usuario IS NULL";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /** Actualiza los datos personales (el C.I. no se modifica desde aquí). */
    public function actualizarPersona($id_persona, $data) {
        $sql = "UPDATE personas SET 
                    nombre_persona = :nombre,
                    apellido_paterno_persona = :paterno,
                    apellido_materno_persona = :materno,
                    telefono_persona = :telefono,
                    direccion_persona = :direccion,
                    update_persona = NOW()
                WHERE id_persona = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nombre'    => $data['nombres'],
            ':paterno'   => $data['paterno'],
            ':materno'   => $data['materno'],
            ':telefono'  => $data['celular'],
            ':direccion' => $data['direccion'] !== '' ? $data['direccion'] : null,
            ':id'        => $id_persona
        ]);
    }

    /** Activa (1) o desactiva (0) la cuenta sin eliminarla. */
    public function cambiarEstado($id_usuario, $estado) {
        $sql = "UPDATE usuarios SET estado_usuario = :estado, update_usuario = NOW() 
                WHERE id_usuario = :id AND delete_usuario IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':estado', (int)$estado, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // ------------------------------------------------------------------------
    // FUNCIÓN: Eliminar Usuario (Borrado Suave)
    // ------------------------------------------------------------------------
    /**
     * Borrado suave: marca delete_usuario y deja estado_usuario = 0.
     *
     * A diferencia del sistema anterior NO se renombra nada: en esta BD la tabla
     * `usuarios` no tiene campos UNIQUE (el login depende del carnet de `personas`,
     * que no se debe alterar porque la persona se usa en otras tablas). La regla
     * "una persona = un usuario" se valida en la app solo contra usuarios vigentes
     * (delete_usuario IS NULL), por lo que la persona queda libre para re-registrarse.
     *
     * @param int    $id_usuario      ID del usuario a eliminar.
     * @param string $fyh_eliminacion Fecha y hora (Y-m-d H:i:s).
     * @return bool  True si se eliminó, false si no existía / ya estaba eliminado / hubo error.
     */
    public function Eliminar_usuario(int $id_usuario, string $fyh_eliminacion) {
        try {
            $sql = "UPDATE usuarios SET 
                        delete_usuario = :fyh_eliminacion,
                        estado_usuario = 0
                    WHERE id_usuario = :id_usuario 
                      AND delete_usuario IS NULL";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':fyh_eliminacion', $fyh_eliminacion, PDO::PARAM_STR);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Error al realizar borrado suave de usuario: " . $e->getMessage());
            return false;
        }
    }
}
?>