<?php
/**
 * Choferes_model
 * CRUD de choferes con borrado suave (delete_chofer + estado_chofer = 0).
 *
 * Notas de la BD:
 *  - Uk_persona_chofer: una persona solo puede tener UN registro de chofer (incluso eliminado).
 *  - UK_licencia_chofer: la licencia es UNIQUE incluso entre choferes eliminados.
 *    Por eso los duplicados NO filtran delete_chofer; se recupera desde la papelera.
 * Las lecturas devuelven [] / null ante un error; las escrituras dejan subir la PDOException
 * para que el controlador la maneje (p. ej. 1062 = clave duplicada).
 */
class Choferes_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        if ($this->pdo) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    /** SELECT base: chofer + persona + días para el vencimiento + vehículos asignados vigentes. */
    private function baseSelect() {
        return "SELECT
                    c.id_chofer,
                    c.id_persona,
                    c.licencia_chofer,
                    c.categoria_licencia_chofer,
                    c.vencimiento_licencia_chofer,
                    CAST(IFNULL(c.estado_chofer, 1) AS UNSIGNED) AS estado_chofer,
                    c.create_chofer,
                    c.update_chofer,
                    c.delete_chofer,
                    p.carnet_persona,
                    p.nombre_persona,
                    p.apellido_paterno_persona,
                    p.apellido_materno_persona,
                    p.telefono_persona,
                    p.direccion_persona,
                    TRIM(CONCAT(p.nombre_persona, ' ', p.apellido_paterno_persona, ' ', IFNULL(p.apellido_materno_persona, ''))) AS nombre_completo,
                    DATEDIFF(c.vencimiento_licencia_chofer, CURDATE()) AS dias_vencimiento,
                    (SELECT COUNT(*) FROM socios so
                      WHERE so.id_persona = c.id_persona AND so.delete_socio IS NULL) AS es_socio,
                    (SELECT COUNT(*)
                       FROM vehiculos_choferes vc
                       INNER JOIN vehiculos v ON vc.id_vehiculo = v.id_vehiculo
                      WHERE vc.id_chofer = c.id_chofer
                        AND vc.delete_vehiculo_chofer IS NULL
                        AND (vc.estado_vehiculo_chofer = 1 OR vc.estado_vehiculo_chofer IS NULL)
                        AND v.delete_vehiculo IS NULL) AS total_vehiculos
                FROM choferes c
                INNER JOIN personas p ON c.id_persona = p.id_persona ";
    }

    /* ---------------- Lecturas ---------------- */

    /** Choferes no eliminados (activos e inactivos). */
    public function getChoferes() {
        try {
            $sql = $this->baseSelect() . "WHERE c.delete_chofer IS NULL ORDER BY c.id_chofer DESC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Choferes_model::getChoferes: ' . $e->getMessage());
            return [];
        }
    }

    /** Papelera: choferes eliminados, el más reciente primero. */
    public function getEliminados() {
        try {
            $sql = $this->baseSelect() . "WHERE c.delete_chofer IS NOT NULL ORDER BY c.delete_chofer DESC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Choferes_model::getEliminados: ' . $e->getMessage());
            return [];
        }
    }

    public function contarEliminados() {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM choferes WHERE delete_chofer IS NOT NULL")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /** Un chofer por id. Por defecto solo vigentes; $incluirEliminados = true para la papelera. */
    public function obtenerChofer($id, $incluirEliminados = false) {
        try {
            $sql = $this->baseSelect() . "WHERE c.id_chofer = :id";
            if (!$incluirEliminados) {
                $sql .= " AND c.delete_chofer IS NULL";
            }
            $stmt = $this->pdo->prepare($sql . " LIMIT 1");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Choferes_model::obtenerChofer: ' . $e->getMessage());
            return null;
        }
    }

    /** ¿La persona ya tiene registro de chofer (vigente O eliminado)? */
    public function buscarPorPersona($id_persona) {
        $stmt = $this->pdo->prepare(
            "SELECT id_chofer, delete_chofer FROM choferes WHERE id_persona = :p LIMIT 1"
        );
        $stmt->execute([':p' => $id_persona]);
        $f = $stmt->fetch(PDO::FETCH_ASSOC);
        return $f ? ['id_chofer' => (int)$f['id_chofer'], 'eliminado' => $f['delete_chofer'] !== null] : null;
    }

    /**
     * ¿Otro chofer (vigente O eliminado) usa esa licencia?
     * @return array|null ['eliminado' => bool]
     */
    public function buscarLicenciaDuplicada($licencia, $excluirId = 0) {
        $stmt = $this->pdo->prepare(
            "SELECT delete_chofer FROM choferes
             WHERE LOWER(licencia_chofer) = :l AND id_chofer <> :x LIMIT 1"
        );
        $stmt->execute([':l' => mb_strtolower($licencia, 'UTF-8'), ':x' => (int)$excluirId]);
        $f = $stmt->fetch(PDO::FETCH_ASSOC);
        return $f ? ['eliminado' => $f['delete_chofer'] !== null] : null;
    }

    /* ---------------- Escrituras ---------------- */

    /** $d: licencia, categoria, vencimiento. */
    public function crear($id_persona, $d) {
        $sql = "INSERT INTO choferes
                    (id_persona, licencia_chofer, categoria_licencia_chofer, vencimiento_licencia_chofer, estado_chofer, create_chofer)
                VALUES (:p, :l, :c, :v, 1, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':p' => $id_persona,
            ':l' => $d['licencia'],
            ':c' => $d['categoria'] !== '' ? $d['categoria'] : null,
            ':v' => $d['vencimiento'] !== '' ? $d['vencimiento'] : null,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar($id, $d) {
        $sql = "UPDATE choferes SET
                    licencia_chofer = :l,
                    categoria_licencia_chofer = :c,
                    vencimiento_licencia_chofer = :v,
                    update_chofer = NOW()
                WHERE id_chofer = :id AND delete_chofer IS NULL";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':l'  => $d['licencia'],
            ':c'  => $d['categoria'] !== '' ? $d['categoria'] : null,
            ':v'  => $d['vencimiento'] !== '' ? $d['vencimiento'] : null,
            ':id' => $id
        ]);
    }

    /** Persona ya existente que pasa a ser chofer: solo se refresca el teléfono (no se pisan nombres). */
    public function actualizarTelefonoPersona($id_persona, $telefono) {
        $stmt = $this->pdo->prepare("UPDATE personas SET telefono_persona = :t, update_persona = NOW() WHERE id_persona = :id");
        return $stmt->execute([':t' => $telefono, ':id' => $id_persona]);
    }

    /** Edición de los datos personales (el C.I. no se modifica). */
    public function actualizarPersona($id_persona, $d) {
        $sql = "UPDATE personas SET
                    nombre_persona = :n,
                    apellido_paterno_persona = :p,
                    apellido_materno_persona = :m,
                    telefono_persona = :t,
                    direccion_persona = :d,
                    update_persona = NOW()
                WHERE id_persona = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':n'  => $d['nombres'],
            ':p'  => $d['paterno'],
            ':m'  => $d['materno'] !== '' ? $d['materno'] : null,
            ':t'  => $d['celular'],
            ':d'  => $d['direccion'] !== '' ? $d['direccion'] : null,
            ':id' => $id_persona
        ]);
    }

    /** Activa (1) o desactiva (0) sin eliminar. */
    public function cambiarEstado($id, $estado) {
        $stmt = $this->pdo->prepare(
            "UPDATE choferes SET estado_chofer = :e, update_chofer = NOW()
             WHERE id_chofer = :id AND delete_chofer IS NULL"
        );
        $stmt->bindValue(':e', (int)$estado, PDO::PARAM_INT);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Borrado suave: marca delete_chofer y deja estado_chofer = 0.
     * No renombra nada: la licencia y la persona siguen reservadas hasta restaurarlo.
     */
    public function eliminarChofer($id, $fyh_eliminacion) {
        $stmt = $this->pdo->prepare(
            "UPDATE choferes SET delete_chofer = :fyh, estado_chofer = 0
             WHERE id_chofer = :id AND delete_chofer IS NULL"
        );
        $stmt->bindValue(':fyh', $fyh_eliminacion, PDO::PARAM_STR);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /** Saca al chofer de la papelera y lo deja activo. */
    public function restaurar($id) {
        $stmt = $this->pdo->prepare(
            "UPDATE choferes SET delete_chofer = NULL, estado_chofer = 1, update_chofer = NOW()
             WHERE id_chofer = :id AND delete_chofer IS NOT NULL"
        );
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>