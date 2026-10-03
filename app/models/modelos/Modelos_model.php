<?php
/**
 * Modelos_model
 * CRUD de modelos de vehículo con borrado suave (delete_modelo + estado_modelo = 0)
 * y guardado del plano (pisos + elementos).
 *
 * Notas de la BD:
 *  - UK_nombre_modelo: el nombre es UNIQUE incluso entre modelos eliminados (se recupera desde la papelera).
 *  - UK_numero_pisos (id_modelo, numero_piso) y UK_elemento_unico (id_piso, fila, columna) cuentan
 *    también las filas dadas de baja, por eso al guardar se REACTIVAN en vez de insertar de nuevo.
 *  - detalles_pasajes referencia elementos (FK): un elemento ya vendido alguna vez no se borra
 *    físicamente, se da de baja (estado_elemento = 0 + delete_elemento).
 *  - modelos.total_asientos_modelo se recalcula al guardar el plano (solo cuenta asientos vendibles).
 * Las lecturas devuelven [] / null ante error; las escrituras dejan subir la PDOException.
 */
class Modelos_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        if ($this->pdo) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    private function baseSelect() {
        return "SELECT
                    m.id_modelo,
                    m.nombre_modelo,
                    m.total_asientos_modelo,
                    CAST(IFNULL(m.estado_modelo, 1) AS UNSIGNED) AS estado_modelo,
                    m.create_modelo,
                    m.update_modelo,
                    m.delete_modelo,
                    (SELECT COUNT(*) FROM pisos p
                      WHERE p.id_modelo = m.id_modelo
                        AND (p.estado_piso = 1 OR p.estado_piso IS NULL)
                        AND p.delete_piso IS NULL) AS total_pisos,
                    (SELECT COUNT(*) FROM vehiculos v
                      WHERE v.id_modelo = m.id_modelo AND v.delete_vehiculo IS NULL) AS total_vehiculos
                FROM modelos m ";
    }

    /* ---------------- Lecturas ---------------- */

    public function getModelos() {
        try {
            $sql = $this->baseSelect() . "WHERE m.delete_modelo IS NULL ORDER BY m.nombre_modelo ASC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Modelos_model::getModelos: ' . $e->getMessage());
            return [];
        }
    }

    public function getEliminados() {
        try {
            $sql = $this->baseSelect() . "WHERE m.delete_modelo IS NOT NULL ORDER BY m.delete_modelo DESC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Modelos_model::getEliminados: ' . $e->getMessage());
            return [];
        }
    }

    public function contarEliminados() {
        try {
            return (int)$this->pdo->query("SELECT COUNT(*) FROM modelos WHERE delete_modelo IS NOT NULL")->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function obtenerModelo($id, $incluirEliminados = false) {
        try {
            $sql = $this->baseSelect() . "WHERE m.id_modelo = :id";
            if (!$incluirEliminados) {
                $sql .= " AND m.delete_modelo IS NULL";
            }
            $st = $this->pdo->prepare($sql . " LIMIT 1");
            $st->execute([':id' => $id]);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Modelos_model::obtenerModelo: ' . $e->getMessage());
            return null;
        }
    }

    /** ¿Otro modelo (vigente O eliminado) usa ese nombre? @return array|null ['eliminado' => bool] */
    public function nombreDuplicado($nombre, $excluirId = 0) {
        $st = $this->pdo->prepare(
            "SELECT delete_modelo FROM modelos WHERE LOWER(nombre_modelo) = :n AND id_modelo <> :x LIMIT 1"
        );
        $st->execute([':n' => mb_strtolower($nombre, 'UTF-8'), ':x' => (int)$excluirId]);
        $f = $st->fetch(PDO::FETCH_ASSOC);
        return $f ? ['eliminado' => $f['delete_modelo'] !== null] : null;
    }

    /** Tipos de elemento disponibles (Asiento, Chofer, Baño, Escalera, ...). */
    public function getTiposElementos() {
        try {
            $sql = "SELECT id_tipo_elemento, nombre_tipo_elemento
                    FROM tipos_elementos
                    WHERE (estado_tipo_elemento = 1 OR estado_tipo_elemento IS NULL) AND delete_tipo_elemento IS NULL
                    ORDER BY id_tipo_elemento ASC";
            return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Modelos_model::getTiposElementos: ' . $e->getMessage());
            return [];
        }
    }

    /** Pisos vigentes del modelo, cada uno con su lista de elementos vigentes. */
    public function getPisosConfig($id_modelo) {
        try {
            $st = $this->pdo->prepare(
                "SELECT id_piso, numero_piso, nombre_piso, filas_piso, columnas_piso
                 FROM pisos
                 WHERE id_modelo = :m AND (estado_piso = 1 OR estado_piso IS NULL) AND delete_piso IS NULL
                 ORDER BY numero_piso ASC"
            );
            $st->execute([':m' => $id_modelo]);
            $pisos = $st->fetchAll(PDO::FETCH_ASSOC);

            $stE = $this->pdo->prepare(
                "SELECT e.id_elemento, e.id_tipo_elemento, e.fila_elemento, e.columna_elemento, e.dato_elemento,
                        te.nombre_tipo_elemento AS tipo_elemento
                 FROM elementos e
                 INNER JOIN tipos_elementos te ON e.id_tipo_elemento = te.id_tipo_elemento
                 WHERE e.id_piso = :p AND (e.estado_elemento = 1 OR e.estado_elemento IS NULL) AND e.delete_elemento IS NULL
                 ORDER BY e.fila_elemento, e.columna_elemento"
            );
            foreach ($pisos as &$p) {
                $stE->execute([':p' => $p['id_piso']]);
                $p['elementos'] = $stE->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($p);
            return $pisos;
        } catch (PDOException $e) {
            error_log('Modelos_model::getPisosConfig: ' . $e->getMessage());
            return [];
        }
    }

    /** ¿Hay boletos activos en turnos abiertos que usen asientos de este modelo? (bloquea cambiar el plano) */
    public function tieneVentasAbiertas($id_modelo) {
        $sql = "SELECT COUNT(*)
                FROM detalles_pasajes dp
                INNER JOIN elementos e ON dp.id_elemento = e.id_elemento
                INNER JOIN pisos p ON e.id_piso = p.id_piso
                INNER JOIN turnos t ON dp.id_turno = t.id_turno
                INNER JOIN estados_turnos et ON t.id_estado_turno = et.id_estado_turno
                WHERE p.id_modelo = :m
                  AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL)
                  AND (t.estado_turno = 1 OR t.estado_turno IS NULL)
                  AND (LOWER(et.nombre_estado_turno) LIKE '%turno%' OR LOWER(et.nombre_estado_turno) = 'pendiente')";
        $st = $this->pdo->prepare($sql);
        $st->execute([':m' => $id_modelo]);
        return (int)$st->fetchColumn() > 0;
    }

    /* ---------------- Escrituras ---------------- */

    /** El total de asientos arranca en 0 y se recalcula al guardar el plano. */
    public function crear($nombre) {
        $st = $this->pdo->prepare(
            "INSERT INTO modelos (nombre_modelo, total_asientos_modelo, estado_modelo, create_modelo)
             VALUES (:n, 0, 1, NOW())"
        );
        $st->execute([':n' => $nombre]);
        return (int)$this->pdo->lastInsertId();
    }

    public function actualizar($id, $nombre) {
        $st = $this->pdo->prepare(
            "UPDATE modelos SET nombre_modelo = :n, update_modelo = NOW()
             WHERE id_modelo = :id AND delete_modelo IS NULL"
        );
        return $st->execute([':n' => $nombre, ':id' => $id]);
    }

    public function cambiarEstado($id, $estado) {
        $st = $this->pdo->prepare(
            "UPDATE modelos SET estado_modelo = :e, update_modelo = NOW()
             WHERE id_modelo = :id AND delete_modelo IS NULL"
        );
        $st->bindValue(':e', (int)$estado, PDO::PARAM_INT);
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    /** Borrado suave: marca delete_modelo y deja estado_modelo = 0. */
    public function eliminar($id, $fyh) {
        $st = $this->pdo->prepare(
            "UPDATE modelos SET delete_modelo = :f, estado_modelo = 0
             WHERE id_modelo = :id AND delete_modelo IS NULL"
        );
        $st->bindValue(':f', $fyh, PDO::PARAM_STR);
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    public function restaurar($id) {
        $st = $this->pdo->prepare(
            "UPDATE modelos SET delete_modelo = NULL, estado_modelo = 1, update_modelo = NOW()
             WHERE id_modelo = :id AND delete_modelo IS NOT NULL"
        );
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    /* ---------------- Plano ---------------- */

    /**
     * Sincroniza pisos y elementos del modelo en UNA transacción y recalcula el total de asientos.
     * $pisos viene ya validado por ValidarModelos::validarConfiguracion().
     */
    public function guardarConfiguracion($id_modelo, array $pisos, $totalAsientos) {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare("SELECT id_piso, numero_piso FROM pisos WHERE id_modelo = :m FOR UPDATE");
            $st->execute([':m' => $id_modelo]);
            $existentes = [];
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $f) {
                $existentes[(int)$f['numero_piso']] = (int)$f['id_piso'];
            }

            $conservados = [];
            foreach ($pisos as $p) {
                $conservados[$p['numero']] = true;

                if (isset($existentes[$p['numero']])) {
                    $idPiso = $existentes[$p['numero']];
                    $this->pdo->prepare(
                        "UPDATE pisos SET nombre_piso = :n, filas_piso = :f, columnas_piso = :c,
                                estado_piso = 1, delete_piso = NULL, update_piso = NOW()
                         WHERE id_piso = :id"
                    )->execute([':n' => $p['nombre'], ':f' => $p['filas'], ':c' => $p['columnas'], ':id' => $idPiso]);
                } else {
                    $this->pdo->prepare(
                        "INSERT INTO pisos (id_modelo, numero_piso, nombre_piso, filas_piso, columnas_piso, estado_piso, create_piso)
                         VALUES (:m, :num, :n, :f, :c, 1, NOW())"
                    )->execute([':m' => $id_modelo, ':num' => $p['numero'], ':n' => $p['nombre'], ':f' => $p['filas'], ':c' => $p['columnas']]);
                    $idPiso = (int)$this->pdo->lastInsertId();
                }

                $this->sincronizarElementos($idPiso, $p['elementos']);
            }

            foreach ($existentes as $numero => $idPiso) {
                if (!isset($conservados[$numero])) {
                    $this->retirarPiso($idPiso);
                }
            }

            $this->pdo->prepare("UPDATE modelos SET total_asientos_modelo = :t, update_modelo = NOW() WHERE id_modelo = :m")
                ->execute([':t' => (int)$totalAsientos, ':m' => $id_modelo]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Upsert por posición (fila/columna); lo que ya no está se borra o se da de baja si fue vendido. */
    private function sincronizarElementos($idPiso, array $nuevos) {
        $st = $this->pdo->prepare(
            "SELECT e.id_elemento, e.fila_elemento, e.columna_elemento,
                    EXISTS(SELECT 1 FROM detalles_pasajes dp WHERE dp.id_elemento = e.id_elemento) AS usado
             FROM elementos e WHERE e.id_piso = :p"
        );
        $st->execute([':p' => $idPiso]);
        $existentes = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $existentes[$f['fila_elemento'] . ':' . $f['columna_elemento']] = $f;
        }

        $upd = $this->pdo->prepare(
            "UPDATE elementos SET id_tipo_elemento = :t, dato_elemento = :d,
                    estado_elemento = 1, delete_elemento = NULL, update_elemento = NOW()
             WHERE id_elemento = :id"
        );
        $ins = $this->pdo->prepare(
            "INSERT INTO elementos (id_piso, id_tipo_elemento, fila_elemento, columna_elemento, dato_elemento, estado_elemento, create_elemento)
             VALUES (:p, :t, :f, :c, :d, 1, NOW())"
        );

        $vistos = [];
        foreach ($nuevos as $e) {
            $k = $e['fila'] . ':' . $e['columna'];
            $vistos[$k] = true;
            if (isset($existentes[$k])) {
                $upd->execute([':t' => $e['id_tipo'], ':d' => $e['dato'], ':id' => $existentes[$k]['id_elemento']]);
            } else {
                $ins->execute([':p' => $idPiso, ':t' => $e['id_tipo'], ':f' => $e['fila'], ':c' => $e['columna'], ':d' => $e['dato']]);
            }
        }

        $del  = $this->pdo->prepare("DELETE FROM elementos WHERE id_elemento = :id");
        $baja = $this->pdo->prepare(
            "UPDATE elementos SET estado_elemento = 0, delete_elemento = IFNULL(delete_elemento, NOW()) WHERE id_elemento = :id"
        );
        foreach ($existentes as $k => $f) {
            if (isset($vistos[$k])) continue;
            if ((int)$f['usado'] === 1) $baja->execute([':id' => $f['id_elemento']]);
            else $del->execute([':id' => $f['id_elemento']]);
        }
    }

    /** Quita un piso: borra lo que nunca se vendió y da de baja el resto (y el piso si quedó algo). */
    private function retirarPiso($idPiso) {
        $this->pdo->prepare(
            "DELETE e FROM elementos e
             WHERE e.id_piso = :p
               AND NOT EXISTS (SELECT 1 FROM detalles_pasajes dp WHERE dp.id_elemento = e.id_elemento)"
        )->execute([':p' => $idPiso]);

        $this->pdo->prepare(
            "UPDATE elementos SET estado_elemento = 0, delete_elemento = IFNULL(delete_elemento, NOW()) WHERE id_piso = :p"
        )->execute([':p' => $idPiso]);

        $st = $this->pdo->prepare("SELECT COUNT(*) FROM elementos WHERE id_piso = :p");
        $st->execute([':p' => $idPiso]);

        if ((int)$st->fetchColumn() === 0) {
            $this->pdo->prepare("DELETE FROM pisos WHERE id_piso = :p")->execute([':p' => $idPiso]);
        } else {
            $this->pdo->prepare(
                "UPDATE pisos SET estado_piso = 0, delete_piso = NOW(), update_piso = NOW() WHERE id_piso = :p"
            )->execute([':p' => $idPiso]);
        }
    }
}
?>