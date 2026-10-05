<?php
class Usuarios_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    private function baseSelect() {
        return "SELECT 
                    u.id_usuario,
                    u.id_persona,
                    u.id_sucursal,
                    u.id_rol,
                    s.id_sindicato,
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

    /** Usuarios no eliminados del sindicato indicado. */
    public function getUsuarios($id_sindicato) {
        try {
            $sql = $this->baseSelect() . "WHERE u.delete_usuario IS NULL AND s.id_sindicato = :s ORDER BY u.id_usuario DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':s' => (int)$id_sindicato]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Usuarios_model::getUsuarios: ' . $e->getMessage());
            return [];
        }
    }

    /** Un usuario, solo si pertenece al sindicato indicado. */
    public function obtenerUsuario($id_usuario, $id_sindicato) {
        try {
            $sql = $this->baseSelect() . "WHERE u.id_usuario = :id AND u.delete_usuario IS NULL AND s.id_sindicato = :s LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id_usuario, ':s' => (int)$id_sindicato]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Usuarios_model::obtenerUsuario: ' . $e->getMessage());
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

    /** Los roles son globales: se valida que exista y esté vigente. */
    public function rolValido($id_rol) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM roles
             WHERE id_rol = :r AND delete_rol IS NULL AND (estado_rol = 1 OR estado_rol IS NULL)"
        );
        $st->execute([':r' => (int)$id_rol]);
        return (int)$st->fetchColumn() > 0;
    }

    /** Sucursales activas del sindicato. */
    public function getSucursalesActivas($id_sindicato) {
        try {
            $sql = "SELECT id_sucursal, nombre_sucursal, ciudad_sucursal FROM sucursales 
                    WHERE id_sindicato = :s AND (estado_sucursal = 1 OR estado_sucursal IS NULL) AND delete_sucursal IS NULL
                    ORDER BY ciudad_sucursal ASC, nombre_sucursal ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute([':s' => (int)$id_sindicato]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function sucursalValida($id_sucursal, $id_sindicato) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM sucursales
             WHERE id_sucursal = :id AND id_sindicato = :s AND delete_sucursal IS NULL
               AND (estado_sucursal = 1 OR estado_sucursal IS NULL)"
        );
        $st->execute([':id' => (int)$id_sucursal, ':s' => (int)$id_sindicato]);
        return (int)$st->fetchColumn() > 0;
    }

    public function existeUsuarioVigentePorPersona($id_persona, $excluir_id = 0) {
        $sql = "SELECT COUNT(*) FROM usuarios 
                WHERE id_persona = :p AND delete_usuario IS NULL AND id_usuario != :x";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':p' => $id_persona, ':x' => $excluir_id]);
        return (int)$stmt->fetchColumn() > 0;
    }

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
            ':materno'   => $data['materno'] !== '' ? $data['materno'] : null,
            ':telefono'  => $data['celular'],
            ':direccion' => $data['direccion'] !== '' ? $data['direccion'] : null,
            ':id'        => $id_persona
        ]);
    }

    /** Activa/desactiva, restringido al sindicato. */
    public function cambiarEstado($id_usuario, $estado, $id_sindicato) {
        $sql = "UPDATE usuarios SET estado_usuario = :estado, update_usuario = NOW() 
                WHERE id_usuario = :id AND delete_usuario IS NULL
                  AND id_sucursal IN (SELECT id_sucursal FROM sucursales WHERE id_sindicato = :s)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':estado', (int)$estado, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$id_usuario, PDO::PARAM_INT);
        $stmt->bindValue(':s', (int)$id_sindicato, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /** Borrado suave, restringido al sindicato. */
    public function Eliminar_usuario(int $id_usuario, string $fyh_eliminacion, int $id_sindicato) {
        try {
            $sql = "UPDATE usuarios SET 
                        delete_usuario = :fyh_eliminacion,
                        estado_usuario = 0
                    WHERE id_usuario = :id_usuario 
                      AND delete_usuario IS NULL
                      AND id_sucursal IN (SELECT id_sucursal FROM sucursales WHERE id_sindicato = :s)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':fyh_eliminacion', $fyh_eliminacion, PDO::PARAM_STR);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':s', $id_sindicato, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Error al realizar borrado suave de usuario: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarCarnet($id_persona, $ci) {
        $st = $this->pdo->prepare("UPDATE personas SET carnet_persona = :ci, update_persona = NOW() WHERE id_persona = :id");
        return $st->execute([':ci' => $ci, ':id' => $id_persona]);
    }
}
?>