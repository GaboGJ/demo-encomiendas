<?php
/**
 * Auth_model
 * Login por C.I. (personas.carnet_persona) + registro de Cliente y Sindicato.
 * Los errores de BD suben al controlador, que maneja la transacción.
 *
 * Tablas afectadas en el registro:
 *   personas  -> usuarios                              (Cliente)
 *   personas  -> sindicatos -> sucursales -> usuarios  (Sindicato)
 *   roles (se crea si no existe)
 */
class Auth_model {
    const ROL_CLIENTE   = 'Cliente';
    const ROL_ADMIN_SIN = 'Administrador Sindicato';

    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        // Garantiza que cualquier error SQL lance PDOException (no falle en silencio)
        if ($this->pdo) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    /* ---------------- Transacciones ---------------- */
    public function iniciarTransaccion() { if (!$this->pdo->inTransaction()) $this->pdo->beginTransaction(); }
    public function confirmar()          { if ($this->pdo->inTransaction()) $this->pdo->commit(); }
    public function revertir()           { if ($this->pdo->inTransaction()) $this->pdo->rollBack(); }

    /* ---------------- Login ---------------- */

    /** Usuario vigente (no eliminado) con persona, rol y sucursal. Incluye el hash. */
    public function obtenerUsuarioPorCarnet($ci) {
        $sql = "SELECT 
                    u.id_usuario, u.id_persona, u.id_sucursal, u.id_rol, u.password_usuario,
                    CAST(IFNULL(u.estado_usuario, 1) AS UNSIGNED) AS estado_usuario,
                    p.carnet_persona, p.nombre_persona, p.apellido_paterno_persona,
                    r.nombre_rol,
                    CAST(IFNULL(r.estado_rol, 1) AS UNSIGNED) AS estado_rol,
                    s.nombre_sucursal, s.ciudad_sucursal, s.id_sindicato,
                    CAST(IFNULL(s.estado_sucursal, 1) AS UNSIGNED) AS estado_sucursal
                FROM usuarios u
                INNER JOIN personas p   ON u.id_persona = p.id_persona
                INNER JOIN roles r      ON u.id_rol = r.id_rol
                INNER JOIN sucursales s ON u.id_sucursal = s.id_sucursal
                WHERE p.carnet_persona = :ci AND u.delete_usuario IS NULL
                ORDER BY u.id_usuario DESC
                LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':ci' => $ci]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Actualiza el hash (se llama tras un login correcto si el algoritmo cambió). */
    public function actualizarHash($id_usuario, $password) {
        $st = $this->pdo->prepare("UPDATE usuarios SET password_usuario = :pw, update_usuario = NOW() WHERE id_usuario = :id");
        $st->execute([':pw' => password_hash($password, PASSWORD_BCRYPT), ':id' => $id_usuario]);
    }

    /* ---------------- Personas ---------------- */

    public function buscarPersonaPorCi($ci) {
        $st = $this->pdo->prepare("SELECT * FROM personas WHERE carnet_persona = :ci LIMIT 1");
        $st->execute([':ci' => $ci]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function existeUsuarioVigentePorPersona($id_persona) {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_persona = :p AND delete_usuario IS NULL");
        $st->execute([':p' => $id_persona]);
        return (int)$st->fetchColumn() > 0;
    }

    /** personas: apellido_paterno_persona y telefono_persona son NOT NULL. */
    public function insertarPersona($ci, $nombres, $paterno, $materno, $telefono) {
        $st = $this->pdo->prepare(
            "INSERT INTO personas (carnet_persona, nombre_persona, apellido_paterno_persona, apellido_materno_persona, telefono_persona, estado_persona, create_persona)
             VALUES (:ci, :n, :p, :m, :t, 1, NOW())"
        );
        $st->execute([
            ':ci' => $ci, ':n' => $nombres, ':p' => $paterno,
            ':m'  => $materno !== '' ? $materno : null, ':t' => $telefono
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /** Si la persona ya existía solo se actualiza el teléfono (no se pisan nombres). */
    public function actualizarTelefonoPersona($id_persona, $telefono) {
        $st = $this->pdo->prepare("UPDATE personas SET telefono_persona = :t, update_persona = NOW() WHERE id_persona = :id");
        $st->execute([':t' => $telefono, ':id' => $id_persona]);
    }

    /* ---------------- Roles / sucursal por defecto ---------------- */

    /** Devuelve el id del rol por nombre; si no existe lo crea (la BD no trae datos semilla). */
    public function obtenerOCrearRol($nombre, $descripcion) {
        // nombre_rol es UNIQUE: se busca sin filtrar delete_rol para no chocar con uno eliminado
        $st = $this->pdo->prepare("SELECT id_rol FROM roles WHERE LOWER(nombre_rol) = :n LIMIT 1");
        $st->execute([':n' => mb_strtolower($nombre, 'UTF-8')]);
        $id = $st->fetchColumn();
        if ($id) return (int)$id;

        $ins = $this->pdo->prepare("INSERT INTO roles (nombre_rol, descripcion_rol, estado_rol, create_rol) VALUES (:n, :d, 1, NOW())");
        $ins->execute([':n' => $nombre, ':d' => $descripcion]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * usuarios.id_sucursal es NOT NULL y un cliente no pertenece a una sucursal:
     * se le asigna la de la casa matriz (sindicato principal) o, si no hay, la primera activa.
     */
    public function obtenerSucursalPorDefecto() {
        $sql = "SELECT s.id_sucursal 
                FROM sucursales s
                INNER JOIN sindicatos sn ON s.id_sindicato = sn.id_sindicato
                WHERE (s.estado_sucursal = 1 OR s.estado_sucursal IS NULL) AND s.delete_sucursal IS NULL
                ORDER BY IFNULL(sn.es_principal_sindicato, 0) DESC, s.id_sucursal ASC
                LIMIT 1";
        $id = $this->pdo->query($sql)->fetchColumn();
        return $id ? (int)$id : null;
    }

    /* ---------------- Sindicato / Sucursal / Usuario ---------------- */

    public function existeSindicato($nombre) {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM sindicatos WHERE LOWER(nombre_sindicato) = :n");
        $st->execute([':n' => mb_strtolower($nombre, 'UTF-8')]);
        return (int)$st->fetchColumn() > 0;
    }

    /** $d: nombre, sigla, nit (personeria), telefono, direccion */
    public function insertarSindicato($d) {
        $st = $this->pdo->prepare(
            "INSERT INTO sindicatos (nombre_sindicato, sigla_sindicato, personeria_sindicato, telefono_sindicato, direccion_sindicato, es_principal_sindicato, estado_sindicato, create_sindicato)
             VALUES (:n, :s, :p, :t, :d, 0, 1, NOW())"
        );
        $st->execute([
            ':n' => $d['nombre'],
            ':s' => $d['sigla'] !== '' ? $d['sigla'] : null,
            ':p' => $d['nit'],
            ':t' => $d['telefono'],
            ':d' => $d['direccion'] !== '' ? $d['direccion'] : null
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /** sucursales: direccion_sucursal es NOT NULL. */
    public function insertarSucursal($id_sindicato, $nombre, $ciudad, $direccion) {
        $st = $this->pdo->prepare(
            "INSERT INTO sucursales (id_sindicato, nombre_sucursal, ciudad_sucursal, direccion_sucursal, estado_sucursal, create_sucursal)
             VALUES (:si, :n, :c, :d, 1, NOW())"
        );
        $st->execute([':si' => $id_sindicato, ':n' => $nombre, ':c' => $ciudad, ':d' => $direccion]);
        return (int)$this->pdo->lastInsertId();
    }

    public function crearUsuario($id_persona, $id_sucursal, $id_rol, $password) {
        $st = $this->pdo->prepare(
            "INSERT INTO usuarios (id_persona, id_sucursal, id_rol, password_usuario, estado_usuario, create_usuario)
             VALUES (:p, :s, :r, :pw, 1, NOW())"
        );
        $st->execute([
            ':p' => $id_persona, ':s' => $id_sucursal, ':r' => $id_rol,
            ':pw' => password_hash($password, PASSWORD_BCRYPT)
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}
?>