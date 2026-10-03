<?php
/**
 * Rutas_model
 * Una ruta = par (sucursal origen -> sucursal destino).
 *   - precios_pasajes      : precio base del pasaje (UK origen+destino, cuenta también las eliminadas).
 *   - tarifas_encomiendas  : tarifas por tipo de contenido de ese mismo par.
 * Borrado suave: delete_* + estado = 0 en la ruta y en sus tarifas, con el MISMO timestamp,
 * así al restaurar solo vuelven las tarifas que se eliminaron junto con la ruta.
 * Se filtra por el sindicato del ORIGEN (sucursales.id_sindicato).
 * Las lecturas devuelven [] / null ante error; las escrituras dejan subir la excepción.
 */
class Rutas_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        if ($this->pdo) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    private function baseSelect() {
        $tar = "te.id_sucursal_origen = pp.id_sucursal_origen
                AND te.id_sucursal_destino = pp.id_sucursal_destino
                AND te.delete_tarifa_encomienda IS NULL";
        return "SELECT
                    pp.id_precio_pasaje,
                    pp.id_sucursal_origen,
                    pp.id_sucursal_destino,
                    pp.base_precio_pasaje,
                    CAST(IFNULL(pp.estado_precio_pasaje, 1) AS UNSIGNED) AS estado_ruta,
                    pp.create_precio_pasaje,
                    pp.update_precio_pasaje,
                    pp.delete_precio_pasaje,
                    so.ciudad_sucursal AS ciudad_origen,
                    so.nombre_sucursal AS nombre_origen,
                    sd.ciudad_sucursal AS ciudad_destino,
                    sd.nombre_sucursal AS nombre_destino,
                    (SELECT COUNT(*) FROM tarifas_encomiendas te WHERE $tar) AS total_tarifas,
                    (SELECT MIN(te.precio_tarifa_encomienda) FROM tarifas_encomiendas te WHERE $tar) AS tarifa_min,
                    (SELECT MAX(te.precio_tarifa_encomienda) FROM tarifas_encomiendas te WHERE $tar) AS tarifa_max
                FROM precios_pasajes pp
                INNER JOIN sucursales so ON pp.id_sucursal_origen = so.id_sucursal
                INNER JOIN sucursales sd ON pp.id_sucursal_destino = sd.id_sucursal ";
    }

    /* ---------------- Lecturas ---------------- */

    public function getRutas($id_sindicato) {
        try {
            $st = $this->pdo->prepare($this->baseSelect() .
                "WHERE so.id_sindicato = :s AND pp.delete_precio_pasaje IS NULL
                 ORDER BY so.ciudad_sucursal ASC, sd.ciudad_sucursal ASC");
            $st->execute([':s' => $id_sindicato]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Rutas_model::getRutas: ' . $e->getMessage());
            return [];
        }
    }

    public function getEliminados($id_sindicato) {
        try {
            $st = $this->pdo->prepare($this->baseSelect() .
                "WHERE so.id_sindicato = :s AND pp.delete_precio_pasaje IS NOT NULL
                 ORDER BY pp.delete_precio_pasaje DESC");
            $st->execute([':s' => $id_sindicato]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Rutas_model::getEliminados: ' . $e->getMessage());
            return [];
        }
    }

    public function contarEliminados($id_sindicato) {
        try {
            $st = $this->pdo->prepare(
                "SELECT COUNT(*) FROM precios_pasajes pp
                 INNER JOIN sucursales so ON pp.id_sucursal_origen = so.id_sucursal
                 WHERE so.id_sindicato = :s AND pp.delete_precio_pasaje IS NOT NULL"
            );
            $st->execute([':s' => $id_sindicato]);
            return (int)$st->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function obtenerRuta($id, $id_sindicato, $incluirEliminados = false) {
        try {
            $sql = $this->baseSelect() . "WHERE pp.id_precio_pasaje = :id AND so.id_sindicato = :s";
            if (!$incluirEliminados) $sql .= " AND pp.delete_precio_pasaje IS NULL";
            $st = $this->pdo->prepare($sql . " LIMIT 1");
            $st->execute([':id' => $id, ':s' => $id_sindicato]);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Rutas_model::obtenerRuta: ' . $e->getMessage());
            return null;
        }
    }

    /** Tarifas de la ruta no eliminadas (activas o no, según el estado de la ruta). */
    public function getTarifas($id_origen, $id_destino) {
        try {
            $st = $this->pdo->prepare(
                "SELECT te.id_tarifa_encomienda, te.id_encomienda_contenido, ec.nombre_encomienda_contenido,
                        te.peso_minimo, te.peso_maximo, te.precio_tarifa_encomienda
                 FROM tarifas_encomiendas te
                 INNER JOIN encomiendas_contenidos ec ON te.id_encomienda_contenido = ec.id_encomienda_contenido
                 WHERE te.id_sucursal_origen = :o AND te.id_sucursal_destino = :d
                   AND te.delete_tarifa_encomienda IS NULL
                 ORDER BY ec.nombre_encomienda_contenido ASC"
            );
            $st->execute([':o' => $id_origen, ':d' => $id_destino]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /** Sucursales vigentes del sindicato (posibles orígenes). */
    public function getOrigenes($id_sindicato) {
        try {
            $st = $this->pdo->prepare(
                "SELECT id_sucursal, ciudad_sucursal, nombre_sucursal FROM sucursales
                 WHERE id_sindicato = :s AND (estado_sucursal = 1 OR estado_sucursal IS NULL) AND delete_sucursal IS NULL
                 ORDER BY ciudad_sucursal ASC, nombre_sucursal ASC"
            );
            $st->execute([':s' => $id_sindicato]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /** Todas las sucursales vigentes (posibles destinos). */
    public function getDestinos() {
        try {
            return $this->pdo->query(
                "SELECT id_sucursal, ciudad_sucursal, nombre_sucursal FROM sucursales
                 WHERE (estado_sucursal = 1 OR estado_sucursal IS NULL) AND delete_sucursal IS NULL
                 ORDER BY ciudad_sucursal ASC, nombre_sucursal ASC"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getContenidosActivos() {
        try {
            return $this->pdo->query(
                "SELECT id_encomienda_contenido, nombre_encomienda_contenido FROM encomiendas_contenidos
                 WHERE (estado_encomienda_contenido = 1 OR estado_encomienda_contenido IS NULL)
                   AND delete_encomienda_contenido IS NULL
                 ORDER BY nombre_encomienda_contenido ASC"
            )->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function origenPerteneceASindicato($id_sucursal, $id_sindicato) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM sucursales
             WHERE id_sucursal = :id AND id_sindicato = :s AND delete_sucursal IS NULL
               AND (estado_sucursal = 1 OR estado_sucursal IS NULL)"
        );
        $st->execute([':id' => $id_sucursal, ':s' => $id_sindicato]);
        return (int)$st->fetchColumn() > 0;
    }

    public function destinoValido($id_sucursal) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM sucursales
             WHERE id_sucursal = :id AND delete_sucursal IS NULL
               AND (estado_sucursal = 1 OR estado_sucursal IS NULL)"
        );
        $st->execute([':id' => $id_sucursal]);
        return (int)$st->fetchColumn() > 0;
    }

    /** IDs de contenidos vigentes entre los pedidos (valida que existan). */
    public function contenidosValidos(array $ids) {
        if (!$ids) return 0;
        $in = implode(',', array_fill(0, count($ids), '?'));
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM encomiendas_contenidos
             WHERE id_encomienda_contenido IN ($in) AND delete_encomienda_contenido IS NULL
               AND (estado_encomienda_contenido = 1 OR estado_encomienda_contenido IS NULL)"
        );
        $st->execute($ids);
        return (int)$st->fetchColumn();
    }

    /** ¿Ya existe la ruta (vigente O eliminada)? UK_precios_pasajes cuenta también las eliminadas. */
    public function buscarDuplicado($id_origen, $id_destino, $excluirId = 0) {
        $st = $this->pdo->prepare(
            "SELECT delete_precio_pasaje FROM precios_pasajes
             WHERE id_sucursal_origen = :o AND id_sucursal_destino = :d AND id_precio_pasaje <> :x LIMIT 1"
        );
        $st->execute([':o' => $id_origen, ':d' => $id_destino, ':x' => (int)$excluirId]);
        $f = $st->fetch(PDO::FETCH_ASSOC);
        return $f ? ['eliminado' => $f['delete_precio_pasaje'] !== null] : null;
    }

    /** ¿Hay turnos abiertos (no despachados ni cancelados) en esta ruta? Misma regla que Despachos. */
    public function tieneTurnosAbiertos($id_origen, $id_destino) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM turnos t
             INNER JOIN estados_turnos et ON t.id_estado_turno = et.id_estado_turno
             WHERE t.id_sucursal_origen = :o AND t.id_sucursal_destino = :d
               AND (t.estado_turno = 1 OR t.estado_turno IS NULL)
               AND (LOWER(et.nombre_estado_turno) LIKE '%turno%' OR LOWER(et.nombre_estado_turno) = 'pendiente')"
        );
        $st->execute([':o' => $id_origen, ':d' => $id_destino]);
        return (int)$st->fetchColumn() > 0;
    }

    /* ---------------- Escrituras ---------------- */

    /** Crea la ruta y sus tarifas en UNA transacción. @return int id_precio_pasaje */
    public function crear(array $d) {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                "INSERT INTO precios_pasajes
                    (id_sucursal_origen, id_sucursal_destino, base_precio_pasaje, estado_precio_pasaje, create_precio_pasaje)
                 VALUES (:o, :d, :p, 1, NOW())"
            )->execute([':o' => $d['id_origen'], ':d' => $d['id_destino'], ':p' => $d['precio']]);
            $id = (int)$this->pdo->lastInsertId();

            $this->sincronizarTarifas($d['id_origen'], $d['id_destino'], $d['tarifas'], 1);

            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Actualiza precio y tarifas en UNA transacción (origen/destino no cambian). */
    public function actualizar($id, array $ruta, array $d) {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                "UPDATE precios_pasajes SET base_precio_pasaje = :p, update_precio_pasaje = NOW()
                 WHERE id_precio_pasaje = :id AND delete_precio_pasaje IS NULL"
            )->execute([':p' => $d['precio'], ':id' => $id]);

            $this->sincronizarTarifas(
                $ruta['id_sucursal_origen'], $ruta['id_sucursal_destino'], $d['tarifas'], (int)$ruta['estado_ruta']
            );

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Upsert por tipo de contenido (reactiva filas dadas de baja); lo que ya no está en la lista
     * se da de baja (estado 0 + delete). Debe llamarse dentro de una transacción.
     */
    private function sincronizarTarifas($o, $d, array $tarifas, $estado) {
        $st = $this->pdo->prepare(
            "SELECT id_tarifa_encomienda, id_encomienda_contenido, delete_tarifa_encomienda
             FROM tarifas_encomiendas WHERE id_sucursal_origen = :o AND id_sucursal_destino = :d
             ORDER BY id_tarifa_encomienda ASC FOR UPDATE"
        );
        $st->execute([':o' => $o, ':d' => $d]);
        $existentes = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $k = (int)$f['id_encomienda_contenido'];
            if (!isset($existentes[$k])) $existentes[$k] = $f; // si hubiera duplicados heredados, se usa el primero
        }

        $upd = $this->pdo->prepare(
            "UPDATE tarifas_encomiendas SET peso_minimo = :pmin, peso_maximo = :pmax, precio_tarifa_encomienda = :p,
                    estado_tarifa_encomienda = :e, delete_tarifa_encomienda = NULL, update_tarifa_encomienda = NOW()
             WHERE id_tarifa_encomienda = :id"
        );
        $ins = $this->pdo->prepare(
            "INSERT INTO tarifas_encomiendas
                (id_sucursal_origen, id_sucursal_destino, id_encomienda_contenido, peso_minimo, peso_maximo,
                 precio_tarifa_encomienda, estado_tarifa_encomienda, create_tarifa_encomienda)
             VALUES (:o, :d, :c, :pmin, :pmax, :p, :e, NOW())"
        );

        $conservados = [];
        foreach ($tarifas as $t) {
            $c = (int)$t['id_contenido'];
            $conservados[$c] = true;
            if (isset($existentes[$c])) {
                $upd->execute([':pmin' => $t['peso_min'], ':pmax' => $t['peso_max'], ':p' => $t['precio'],
                               ':e' => (int)$estado, ':id' => $existentes[$c]['id_tarifa_encomienda']]);
            } else {
                $ins->execute([':o' => $o, ':d' => $d, ':c' => $c, ':pmin' => $t['peso_min'],
                               ':pmax' => $t['peso_max'], ':p' => $t['precio'], ':e' => (int)$estado]);
            }
        }

        $baja = $this->pdo->prepare(
            "UPDATE tarifas_encomiendas SET estado_tarifa_encomienda = 0, delete_tarifa_encomienda = NOW()
             WHERE id_sucursal_origen = :o AND id_sucursal_destino = :d
               AND id_encomienda_contenido = :c AND delete_tarifa_encomienda IS NULL"
        );
        foreach ($existentes as $c => $f) {
            if (!isset($conservados[$c]) && $f['delete_tarifa_encomienda'] === null) {
                $baja->execute([':o' => $o, ':d' => $d, ':c' => $c]);
            }
        }
    }

    /** Activa/desactiva la ruta y sus tarifas vigentes (Encomiendas y Despachos filtran por estado). */
    public function cambiarEstado($id, array $ruta, $estado) {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                "UPDATE precios_pasajes SET estado_precio_pasaje = :e, update_precio_pasaje = NOW()
                 WHERE id_precio_pasaje = :id AND delete_precio_pasaje IS NULL"
            );
            $st->bindValue(':e', (int)$estado, PDO::PARAM_INT);
            $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $st->execute();
            $ok = $st->rowCount() > 0;

            $st2 = $this->pdo->prepare(
                "UPDATE tarifas_encomiendas SET estado_tarifa_encomienda = :e, update_tarifa_encomienda = NOW()
                 WHERE id_sucursal_origen = :o AND id_sucursal_destino = :d AND delete_tarifa_encomienda IS NULL"
            );
            $st2->bindValue(':e', (int)$estado, PDO::PARAM_INT);
            $st2->bindValue(':o', (int)$ruta['id_sucursal_origen'], PDO::PARAM_INT);
            $st2->bindValue(':d', (int)$ruta['id_sucursal_destino'], PDO::PARAM_INT);
            $st2->execute();

            $this->pdo->commit();
            return $ok;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Borrado suave de la ruta y sus tarifas con el mismo timestamp. */
    public function eliminar($id, array $ruta, $fyh) {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                "UPDATE precios_pasajes SET delete_precio_pasaje = :f, estado_precio_pasaje = 0
                 WHERE id_precio_pasaje = :id AND delete_precio_pasaje IS NULL"
            );
            $st->execute([':f' => $fyh, ':id' => $id]);
            $ok = $st->rowCount() > 0;

            $this->pdo->prepare(
                "UPDATE tarifas_encomiendas SET delete_tarifa_encomienda = :f, estado_tarifa_encomienda = 0
                 WHERE id_sucursal_origen = :o AND id_sucursal_destino = :d AND delete_tarifa_encomienda IS NULL"
            )->execute([':f' => $fyh, ':o' => $ruta['id_sucursal_origen'], ':d' => $ruta['id_sucursal_destino']]);

            $this->pdo->commit();
            return $ok;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Saca la ruta de la papelera (activa) y recupera las tarifas que se borraron junto con ella. */
    public function restaurar($id, array $ruta) {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                "UPDATE tarifas_encomiendas SET delete_tarifa_encomienda = NULL, estado_tarifa_encomienda = 1,
                        update_tarifa_encomienda = NOW()
                 WHERE id_sucursal_origen = :o AND id_sucursal_destino = :d AND delete_tarifa_encomienda = :f"
            )->execute([':o' => $ruta['id_sucursal_origen'], ':d' => $ruta['id_sucursal_destino'],
                        ':f' => $ruta['delete_precio_pasaje']]);

            $st = $this->pdo->prepare(
                "UPDATE precios_pasajes SET delete_precio_pasaje = NULL, estado_precio_pasaje = 1, update_precio_pasaje = NOW()
                 WHERE id_precio_pasaje = :id AND delete_precio_pasaje IS NOT NULL"
            );
            $st->execute([':id' => $id]);
            $ok = $st->rowCount() > 0;

            $this->pdo->commit();
            return $ok;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
}
?>