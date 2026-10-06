<?php
/**
 * Moviles_model
 * CRUD de vehículos (móviles) con borrado suave (delete_vehiculo + estado_vehiculo = 0).
 *
 * Notas:
 *  - Los móviles NO se filtran por sindicato: el socio titular puede ser de cualquier sindicato.
 *  - La BD no tiene UNIQUE en placa ni número interno, así que los duplicados se validan aquí
 *    (solo contra vehículos no eliminados). El número de unidad es único dentro del sindicato
 *    del socio titular; la placa es única en todo el sistema.
 *  - Despachos_model::getVehiculosChoferes filtra por estado_vehiculo, por eso al desactivar
 *    o eliminar se deja estado_vehiculo = 0.
 */
class Moviles_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        if ($this->pdo) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    /** SELECT base: vehículo + modelo + socio titular (+ su sindicato) + conteo de choferes vigentes. */
    private function baseSelect() {
        return "SELECT
                    v.id_vehiculo,
                    v.id_socio,
                    v.id_modelo,
                    v.placa_vehiculo,
                    v.numero_interno_vehiculo,
                    v.color_vehiculo,
                    CAST(IFNULL(v.estado_vehiculo, 1) AS UNSIGNED) AS estado_vehiculo,
                    v.create_vehiculo,
                    v.update_vehiculo,
                    v.delete_vehiculo,
                    m.nombre_modelo,
                    m.total_asientos_modelo,
                    so.codigo_socio,
                    so.id_sindicato AS id_sindicato_socio,
                    sn.nombre_sindicato AS nombre_sindicato_socio,
                    CONCAT(p.nombre_persona, ' ', p.apellido_paterno_persona) AS socio_nombre,
                    (SELECT COUNT(*) FROM vehiculos_choferes vc
                      WHERE vc.id_vehiculo = v.id_vehiculo
                        AND (vc.estado_vehiculo_chofer = 1 OR vc.estado_vehiculo_chofer IS NULL)
                        AND vc.delete_vehiculo_chofer IS NULL) AS total_choferes
                FROM vehiculos v
                INNER JOIN socios so   ON v.id_socio = so.id_socio
                INNER JOIN sindicatos sn ON so.id_sindicato = sn.id_sindicato
                INNER JOIN personas p  ON so.id_persona = p.id_persona
                INNER JOIN modelos m   ON v.id_modelo = m.id_modelo ";
    }

    /* ---------------- Lecturas ---------------- */

    /** Móviles no eliminados (activos e inactivos). */
    public function getMoviles() {
        try {
            $sql = $this->baseSelect() . "WHERE v.delete_vehiculo IS NULL ORDER BY v.id_vehiculo DESC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Moviles_model::getMoviles: ' . $e->getMessage());
            return [];
        }
    }

    /** Papelera: móviles eliminados, el más reciente primero. */
    public function getEliminados() {
        try {
            $sql = $this->baseSelect() . "WHERE v.delete_vehiculo IS NOT NULL ORDER BY v.delete_vehiculo DESC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Moviles_model::getEliminados: ' . $e->getMessage());
            return [];
        }
    }

    public function contarEliminados() {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM vehiculos WHERE delete_vehiculo IS NOT NULL")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /** Un móvil. Por defecto solo vigentes; $incluirEliminados = true para la papelera. */
    public function obtenerMovil($id, $incluirEliminados = false) {
        try {
            $sql = $this->baseSelect() . "WHERE v.id_vehiculo = :id";
            if (!$incluirEliminados) {
                $sql .= " AND v.delete_vehiculo IS NULL";
            }
            $st = $this->pdo->prepare($sql . " LIMIT 1");
            $st->execute([':id' => $id]);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Moviles_model::obtenerMovil: ' . $e->getMessage());
            return null;
        }
    }

    /** Choferes vigentes asignados al móvil (para el detalle). */
    public function getChoferesAsignados($id_vehiculo) {
        try {
            $sql = "SELECT
                        vc.id_vehiculo_chofer,
                        CAST(IFNULL(vc.titular_vehiculo_chofer, 0) AS UNSIGNED) AS titular,
                        ch.licencia_chofer,
                        p.telefono_persona,
                        CONCAT(p.nombre_persona, ' ', p.apellido_paterno_persona) AS nombre_chofer
                    FROM vehiculos_choferes vc
                    INNER JOIN choferes ch ON vc.id_chofer = ch.id_chofer
                    INNER JOIN personas p  ON ch.id_persona = p.id_persona
                    WHERE vc.id_vehiculo = :v
                      AND (vc.estado_vehiculo_chofer = 1 OR vc.estado_vehiculo_chofer IS NULL)
                      AND vc.delete_vehiculo_chofer IS NULL
                    ORDER BY titular DESC, nombre_chofer ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute([':v' => $id_vehiculo]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /** Socios vigentes de TODOS los sindicatos (para el select de socio titular). */
    public function getSociosActivos() {
        try {
            $sql = "SELECT so.id_socio, so.codigo_socio, so.id_sindicato, sn.nombre_sindicato,
                           CONCAT(p.nombre_persona, ' ', p.apellido_paterno_persona) AS nombre_socio
                    FROM socios so
                    INNER JOIN personas p ON so.id_persona = p.id_persona
                    INNER JOIN sindicatos sn ON so.id_sindicato = sn.id_sindicato
                    WHERE (so.estado_socio = 1 OR so.estado_socio IS NULL)
                      AND so.delete_socio IS NULL
                      AND sn.delete_sindicato IS NULL
                    ORDER BY sn.nombre_sindicato ASC, p.nombre_persona ASC, p.apellido_paterno_persona ASC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /** Modelos vigentes (para el select de modelo). */
    public function getModelosActivos() {
        try {
            $sql = "SELECT id_modelo, nombre_modelo, total_asientos_modelo
                    FROM modelos
                    WHERE (estado_modelo = 1 OR estado_modelo IS NULL) AND delete_modelo IS NULL
                    ORDER BY nombre_modelo ASC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /** ¿El socio existe, está vigente y activo? (sin importar su sindicato). */
    public function socioValido($id_socio) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM socios
             WHERE id_socio = :so AND delete_socio IS NULL AND (estado_socio = 1 OR estado_socio IS NULL)"
        );
        $st->execute([':so' => $id_socio]);
        return (int)$st->fetchColumn() > 0;
    }

    /** Sindicato al que pertenece el socio (0 si no existe). */
    public function getSindicatoDeSocio($id_socio) {
        $st = $this->pdo->prepare("SELECT id_sindicato FROM socios WHERE id_socio = :so LIMIT 1");
        $st->execute([':so' => $id_socio]);
        return (int)$st->fetchColumn();
    }

    public function modeloExiste($id_modelo) {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM modelos WHERE id_modelo = :m AND delete_modelo IS NULL");
        $st->execute([':m' => $id_modelo]);
        return (int)$st->fetchColumn() > 0;
    }

    /**
     * ¿Otro móvil vigente usa ese número de unidad (dentro del sindicato indicado, el del socio
     * titular) o esa placa (global)? $excluirId ignora el propio registro al editar/restaurar.
     * @return array|null ['campo' => 'numero'|'placa']
     */
    public function buscarDuplicado($numero, $placa, $id_sindicato, $excluirId = 0) {
        $sql = "SELECT
                    MAX(so.id_sindicato = :s AND LOWER(v.numero_interno_vehiculo) = :n) AS dup_numero,
                    MAX(:p1 <> '' AND UPPER(v.placa_vehiculo) = :p2) AS dup_placa
                FROM vehiculos v
                INNER JOIN socios so ON v.id_socio = so.id_socio
                WHERE v.delete_vehiculo IS NULL
                AND v.id_vehiculo <> :x";
        $st = $this->pdo->prepare($sql);
        $st->execute([
            ':s'  => (int)$id_sindicato,
            ':n'  => mb_strtolower($numero, 'UTF-8'),
            ':p1' => $placa,
            ':p2' => $placa,
            ':x'  => (int)$excluirId
        ]);
        $f = $st->fetch(PDO::FETCH_ASSOC);

        if ($f && (int)$f['dup_numero'] === 1) return ['campo' => 'numero'];
        if ($f && (int)$f['dup_placa']  === 1) return ['campo' => 'placa'];
        return null;
    }

    /** ¿Tiene turnos abiertos (no despachados ni cancelados)? Misma regla que Despachos_model::esTurnoAbierto. */
    public function tieneTurnosAbiertos($id_vehiculo) {
        $sql = "SELECT COUNT(*)
                FROM turnos t
                INNER JOIN vehiculos_choferes vc ON t.id_vehiculo_chofer = vc.id_vehiculo_chofer
                INNER JOIN estados_turnos et     ON t.id_estado_turno = et.id_estado_turno
                WHERE vc.id_vehiculo = :v
                  AND (t.estado_turno = 1 OR t.estado_turno IS NULL)
                  AND (LOWER(et.nombre_estado_turno) LIKE '%turno%' OR LOWER(et.nombre_estado_turno) = 'pendiente')";
        $st = $this->pdo->prepare($sql);
        $st->execute([':v' => $id_vehiculo]);
        return (int)$st->fetchColumn() > 0;
    }

    /* ---------------- Escrituras ---------------- */

    /** $d: id_socio, id_modelo, numero, placa, color */
    public function crear($d) {
        $sql = "INSERT INTO vehiculos
                    (id_socio, id_modelo, placa_vehiculo, numero_interno_vehiculo, color_vehiculo, estado_vehiculo, create_vehiculo)
                VALUES (:so, :m, :p, :n, :c, 1, NOW())";
               $st = $this->pdo->prepare($sql);
        $st->execute($this->parametros($d));
        $id = (int)$this->pdo->lastInsertId();

        // Titular: el id_chofer (tabla choferes) de la persona del socio, no el id_persona
        $c = $this->pdo->prepare(
            "SELECT ch.id_chofer FROM socios so
             INNER JOIN choferes ch ON ch.id_persona = so.id_persona
             WHERE so.id_socio = :so AND ch.delete_chofer IS NULL
               AND (ch.estado_chofer = 1 OR ch.estado_chofer IS NULL)
             LIMIT 1"
        );
        $c->execute([':so' => $d['id_socio']]);
        $idChofer = (int)$c->fetchColumn();

        if ($idChofer > 0) {
            $this->pdo->prepare(
                "INSERT INTO vehiculos_choferes
                    (id_vehiculo, id_chofer, titular_vehiculo_chofer, estado_vehiculo_chofer, create_vehiculo_chofer)
                 VALUES (:v, :c, 1, 1, NOW())"
            )->execute([':v' => $id, ':c' => $idChofer]);
        }

        return $id;
    }

    /** Actualiza los datos (no toca estado). Solo móviles no eliminados. */

    /** Actualiza los datos (no toca estado). Solo móviles no eliminados. */
    public function actualizar($id, $d) {
        $sql = "UPDATE vehiculos SET
                    id_socio = :so,
                    id_modelo = :m,
                    placa_vehiculo = :p,
                    numero_interno_vehiculo = :n,
                    color_vehiculo = :c,
                    update_vehiculo = NOW()
                WHERE id_vehiculo = :id AND delete_vehiculo IS NULL";
        $st = $this->pdo->prepare($sql);
        return $st->execute($this->parametros($d) + [':id' => $id]);
    }

    /** Activa (1) o desactiva (0) el móvil. */
    public function cambiarEstado($id, $estado) {
        $st = $this->pdo->prepare(
            "UPDATE vehiculos SET estado_vehiculo = :e, update_vehiculo = NOW()
             WHERE id_vehiculo = :id AND delete_vehiculo IS NULL"
        );
        $st->bindValue(':e', (int)$estado, PDO::PARAM_INT);
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    /** Borrado suave: marca delete_vehiculo y deja estado_vehiculo = 0. */
    public function eliminar($id, $fyh_eliminacion) {
        $st = $this->pdo->prepare(
            "UPDATE vehiculos SET delete_vehiculo = :fyh, estado_vehiculo = 0
             WHERE id_vehiculo = :id AND delete_vehiculo IS NULL"
        );
        $st->bindValue(':fyh', $fyh_eliminacion, PDO::PARAM_STR);
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    /** Saca el móvil de la papelera y lo deja activo. */
    public function restaurar($id) {
        $st = $this->pdo->prepare(
            "UPDATE vehiculos SET delete_vehiculo = NULL, estado_vehiculo = 1, update_vehiculo = NOW()
             WHERE id_vehiculo = :id AND delete_vehiculo IS NOT NULL"
        );
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    private function parametros($d) {
        return [
            ':so' => $d['id_socio'],
            ':m'  => $d['id_modelo'],
            ':p'  => $d['placa'] !== '' ? $d['placa'] : null,
            ':n'  => $d['numero'],
            ':c'  => $d['color'] !== '' ? $d['color'] : null
        ];
    }

    /** Choferes activos para el modal de asignación, marcando los ya asignados a este móvil. */
    public function getChoferesParaAsignar($id_vehiculo) {
        try {
            $sql = "SELECT
                        ch.id_chofer, ch.licencia_chofer, p.carnet_persona,
                        TRIM(CONCAT(p.nombre_persona, ' ', p.apellido_paterno_persona, ' ', IFNULL(p.apellido_materno_persona, ''))) AS nombre_chofer,
                        IF(vc.id_vehiculo_chofer IS NULL, 0, 1) AS asignado,
                        CAST(IFNULL(vc.titular_vehiculo_chofer, 0) AS UNSIGNED) AS titular,
                        IF(tit.id_persona IS NULL, 0, 1) AS es_socio_titular
                    FROM choferes ch
                    INNER JOIN personas p ON ch.id_persona = p.id_persona
                    LEFT JOIN (
                        SELECT so.id_persona
                        FROM vehiculos v INNER JOIN socios so ON v.id_socio = so.id_socio
                        WHERE v.id_vehiculo = :v1
                    ) tit ON tit.id_persona = ch.id_persona
                    LEFT JOIN vehiculos_choferes vc
                        ON vc.id_chofer = ch.id_chofer AND vc.id_vehiculo = :v2
                        AND vc.delete_vehiculo_chofer IS NULL
                        AND (vc.estado_vehiculo_chofer = 1 OR vc.estado_vehiculo_chofer IS NULL)
                    WHERE ch.delete_chofer IS NULL
                    AND (ch.estado_chofer = 1 OR ch.estado_chofer IS NULL)
                    AND (
                            tit.id_persona IS NOT NULL
                        OR vc.id_vehiculo_chofer IS NOT NULL
                        OR NOT EXISTS (SELECT 1 FROM socios s2
                                        WHERE s2.id_persona = ch.id_persona AND s2.delete_socio IS NULL)
                    )
                    ORDER BY es_socio_titular DESC, asignado DESC, titular DESC, nombre_chofer ASC";
            $st = $this->pdo->prepare($sql);
            $st->execute([':v1' => $id_vehiculo, ':v2' => $id_vehiculo]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Moviles_model::getChoferesParaAsignar: ' . $e->getMessage());
            return [];
        }
    }

    private function vcTieneTurnosAbiertos($id_vehiculo_chofer) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM turnos t
             INNER JOIN estados_turnos et ON t.id_estado_turno = et.id_estado_turno
             WHERE t.id_vehiculo_chofer = :vc
               AND (t.estado_turno = 1 OR t.estado_turno IS NULL)
               AND (LOWER(et.nombre_estado_turno) LIKE '%turno%' OR LOWER(et.nombre_estado_turno) = 'pendiente')"
        );
        $st->execute([':vc' => $id_vehiculo_chofer]);
        return (int)$st->fetchColumn() > 0;
    }

    /**
     * Sincroniza vehiculos_choferes del móvil en UNA transacción:
     *  - chofer seleccionado: se crea la fila, o se reactiva si ya existía (UK_vehiculo_chofer
     *    cuenta también las filas dadas de baja), con titular = 1 solo para $id_titular.
     *  - chofer ya asignado y no seleccionado: baja suave (estado 0 + delete_vehiculo_chofer),
     *    salvo que tenga turnos abiertos.
     * Lanza Exception con mensaje legible.
     */
    public function sincronizarChoferes($id_vehiculo, array $ids) {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        // El titular es el chofer que corresponde al socio titular del vehículo (uno solo).
        $st = $this->pdo->prepare(
            "SELECT ch.id_chofer
             FROM vehiculos v
             INNER JOIN socios so ON v.id_socio = so.id_socio
             INNER JOIN choferes ch ON ch.id_persona = so.id_persona
             WHERE v.id_vehiculo = :v
               AND ch.delete_chofer IS NULL
               AND (ch.estado_chofer = 1 OR ch.estado_chofer IS NULL)
             LIMIT 1"
        );
        $st->execute([':v' => $id_vehiculo]);
        $id_titular = (int)$st->fetchColumn(); // 0 si el socio no tiene chofer activo

        // El titular siempre queda asignado
        if ($id_titular > 0 && !in_array($id_titular, $ids, true)) {
            $ids[] = $id_titular;
        }

        if ($ids) {
            $in = implode(',', array_fill(0, count($ids), '?'));
            $st = $this->pdo->prepare(
                "SELECT COUNT(*) FROM choferes
                 WHERE id_chofer IN ($in) AND delete_chofer IS NULL AND (estado_chofer = 1 OR estado_chofer IS NULL)"
            );
            $st->execute($ids);
            if ((int)$st->fetchColumn() !== count($ids)) {
                throw new Exception('Algún chofer seleccionado está inactivo o eliminado. Quítelo de la selección.');
            }
        }

        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                "SELECT id_vehiculo_chofer, id_chofer,
                        (delete_vehiculo_chofer IS NULL AND (estado_vehiculo_chofer = 1 OR estado_vehiculo_chofer IS NULL)) AS vigente
                 FROM vehiculos_choferes WHERE id_vehiculo = :v FOR UPDATE"
            );
            $st->execute([':v' => $id_vehiculo]);
            $existentes = [];
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $f) $existentes[(int)$f['id_chofer']] = $f;

            foreach ($ids as $idChofer) {
                $titular = ($idChofer === $id_titular) ? 1 : 0;
                if (isset($existentes[$idChofer])) {
                    $this->pdo->prepare(
                        "UPDATE vehiculos_choferes
                         SET titular_vehiculo_chofer = $titular, estado_vehiculo_chofer = 1,
                             delete_vehiculo_chofer = NULL, update_vehiculo_chofer = NOW()
                         WHERE id_vehiculo_chofer = :id"
                    )->execute([':id' => $existentes[$idChofer]['id_vehiculo_chofer']]);
                } else {
                    $this->pdo->prepare(
                        "INSERT INTO vehiculos_choferes
                            (id_vehiculo, id_chofer, titular_vehiculo_chofer, estado_vehiculo_chofer, create_vehiculo_chofer)
                         VALUES (:v, :c, $titular, 1, NOW())"
                    )->execute([':v' => $id_vehiculo, ':c' => $idChofer]);
                }
            }

            foreach ($existentes as $idChofer => $f) {
                if (in_array($idChofer, $ids, true) || !(int)$f['vigente']) continue;
                if ($this->vcTieneTurnosAbiertos($f['id_vehiculo_chofer'])) {
                    throw new Exception('No se puede quitar un chofer que tiene turnos abiertos en este móvil. Despache o cancele esos turnos primero.');
                }
                $this->pdo->prepare(
                    "UPDATE vehiculos_choferes
                     SET titular_vehiculo_chofer = 0, estado_vehiculo_chofer = 0,
                         delete_vehiculo_chofer = NOW(), update_vehiculo_chofer = NOW()
                     WHERE id_vehiculo_chofer = :id"
                )->execute([':id' => $f['id_vehiculo_chofer']]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
}
?>