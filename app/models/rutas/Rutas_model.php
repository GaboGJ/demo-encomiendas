<?php
/**
 * Rutas_model
 * Una ruta = (sucursal origen -> sucursal destino) de UN sindicato.
 * El listado muestra las rutas de TODOS los sindicatos.
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
                AND te.id_sindicato = pp.id_sindicato
                AND te.delete_tarifa_encomienda IS NULL";
        // OJO: termina con espacio para poder concatenar el WHERE
        return "SELECT
                    pp.id_precio_pasaje,
                    pp.id_sucursal_origen,
                    pp.id_sucursal_destino,
                    pp.id_sindicato,
                    pp.base_precio_pasaje,
                    CAST(IFNULL(pp.estado_precio_pasaje, 1) AS UNSIGNED) AS estado_ruta,
                    pp.create_precio_pasaje,
                    pp.update_precio_pasaje,
                    pp.delete_precio_pasaje,
                    so.ciudad_sucursal AS ciudad_origen,
                    so.nombre_sucursal AS nombre_origen,
                    sd.ciudad_sucursal AS ciudad_destino,
                    sd.nombre_sucursal AS nombre_destino,
                    sn.nombre_sindicato,
                    sn.sigla_sindicato,
                    (SELECT COUNT(*) FROM tarifas_encomiendas te WHERE $tar) AS total_tarifas,
                    (SELECT MIN(te.precio_tarifa_encomienda) FROM tarifas_encomiendas te WHERE $tar) AS tarifa_min,
                    (SELECT MAX(te.precio_tarifa_encomienda) FROM tarifas_encomiendas te WHERE $tar) AS tarifa_max
                FROM precios_pasajes pp
                INNER JOIN sucursales so ON pp.id_sucursal_origen = so.id_sucursal
                INNER JOIN sucursales sd ON pp.id_sucursal_destino = sd.id_sucursal
                INNER JOIN sindicatos sn ON pp.id_sindicato = sn.id_sindicato ";
    }

    /* ---------------- Lecturas (todas las rutas, de todos los sindicatos) ---------------- */

    public function getRutas() {
        try {
            return $this->pdo->query($this->baseSelect() .
                "WHERE pp.delete_precio_pasaje IS NULL
                 ORDER BY so.ciudad_sucursal ASC, sd.ciudad_sucursal ASC, sn.nombre_sindicato ASC")
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Rutas_model::getRutas: ' . $e->getMessage());
            return [];
        }
    }

    public function getEliminados() {
        try {
            return $this->pdo->query($this->baseSelect() .
                "WHERE pp.delete_precio_pasaje IS NOT NULL
                 ORDER BY pp.delete_precio_pasaje DESC")
                ->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Rutas_model::getEliminados: ' . $e->getMessage());
            return [];
        }
    }

    public function contarEliminados() {
        try {
            return (int)$this->pdo->query(
                "SELECT COUNT(*) FROM precios_pasajes WHERE delete_precio_pasaje IS NOT NULL"
            )->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function obtenerRuta($id, $incluirEliminados = false) {
        try {
            $sql = $this->baseSelect() . "WHERE pp.id_precio_pasaje = :id";
            if (!$incluirEliminados) $sql .= " AND pp.delete_precio_pasaje IS NULL";
            $st = $this->pdo->prepare($sql . " LIMIT 1");
            $st->execute([':id' => $id]);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Rutas_model::obtenerRuta: ' . $e->getMessage());
            return null;
        }
    }

    public function getTarifas($id_origen, $id_destino, $id_sindicato) {
        try {
            $st = $this->pdo->prepare(
                "SELECT te.id_tarifa_encomienda, te.id_encomienda_contenido, ec.nombre_encomienda_contenido,
                        te.peso_minimo, te.peso_maximo, te.precio_tarifa_encomienda
                 FROM tarifas_encomiendas te
                 INNER JOIN encomiendas_contenidos ec ON te.id_encomienda_contenido = ec.id_encomienda_contenido
                 WHERE te.id_sucursal_origen = :o AND te.id_sucursal_destino = :d AND te.id_sindicato = :s
                   AND te.delete_tarifa_encomienda IS NULL
                 ORDER BY ec.nombre_encomienda_contenido ASC, te.peso_minimo ASC, te.peso_maximo ASC"
            );
            $st->execute([':o' => $id_origen, ':d' => $id_destino, ':s' => $id_sindicato]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

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

    public function getSindicatos($soloId = 0) {
        try {
            $sql = "SELECT id_sindicato, nombre_sindicato, sigla_sindicato FROM sindicatos
                    WHERE delete_sindicato IS NULL AND (estado_sindicato = 1 OR estado_sindicato IS NULL)";
            if ((int)$soloId > 0) $sql .= " AND id_sindicato = " . (int)$soloId;
            return $this->pdo->query($sql . " ORDER BY nombre_sindicato ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function sindicatoValido($id) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM sindicatos
             WHERE id_sindicato = :id AND delete_sindicato IS NULL AND (estado_sindicato = 1 OR estado_sindicato IS NULL)"
        );
        $st->execute([':id' => $id]);
        return (int)$st->fetchColumn() > 0;
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

    public function contenidosValidos(array $ids) {
        $ids = array_values(array_unique(array_map('intval', $ids)));
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

    public function buscarDuplicado($id_origen, $id_destino, $id_sindicato, $excluirId = 0) {
        $st = $this->pdo->prepare(
            "SELECT delete_precio_pasaje FROM precios_pasajes
             WHERE id_sucursal_origen = :o AND id_sucursal_destino = :d AND id_sindicato = :s
               AND id_precio_pasaje <> :x LIMIT 1"
        );
        $st->execute([':o' => $id_origen, ':d' => $id_destino, ':s' => $id_sindicato, ':x' => (int)$excluirId]);
        $f = $st->fetch(PDO::FETCH_ASSOC);
        return $f ? ['eliminado' => $f['delete_precio_pasaje'] !== null] : null;
    }

    public function tieneTurnosAbiertos($id_origen, $id_destino, $id_sindicato) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM turnos t
             INNER JOIN estados_turnos et ON t.id_estado_turno = et.id_estado_turno
             INNER JOIN vehiculos_choferes vc ON t.id_vehiculo_chofer = vc.id_vehiculo_chofer
             INNER JOIN vehiculos v ON vc.id_vehiculo = v.id_vehiculo
             INNER JOIN socios so ON v.id_socio = so.id_socio
             WHERE t.id_sucursal_origen = :o AND t.id_sucursal_destino = :d AND so.id_sindicato = :s
               AND (t.estado_turno = 1 OR t.estado_turno IS NULL)
               AND (LOWER(et.nombre_estado_turno) LIKE '%turno%' OR LOWER(et.nombre_estado_turno) = 'pendiente')"
        );
        $st->execute([':o' => $id_origen, ':d' => $id_destino, ':s' => $id_sindicato]);
        return (int)$st->fetchColumn() > 0;
    }

    /* ---------------- Escrituras ---------------- */

    public function crear(array $d) {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                "INSERT INTO precios_pasajes
                    (id_sucursal_origen, id_sucursal_destino, id_sindicato, base_precio_pasaje, estado_precio_pasaje, create_precio_pasaje)
                 VALUES (:o, :d, :s, :p, 1, NOW())"
            )->execute([':o' => $d['id_origen'], ':d' => $d['id_destino'], ':s' => $d['id_sindicato'], ':p' => $d['precio']]);
            $id = (int)$this->pdo->lastInsertId();

            $this->sincronizarTarifas($d['id_origen'], $d['id_destino'], $d['id_sindicato'], $d['tarifas'], 1);

            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function actualizar($id, array $ruta, array $d) {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                "UPDATE precios_pasajes SET base_precio_pasaje = :p, update_precio_pasaje = NOW()
                 WHERE id_precio_pasaje = :id AND delete_precio_pasaje IS NULL"
            )->execute([':p' => $d['precio'], ':id' => $id]);

            $this->sincronizarTarifas(
                $ruta['id_sucursal_origen'], $ruta['id_sucursal_destino'], $ruta['id_sindicato'],
                $d['tarifas'], (int)$ruta['estado_ruta']
            );

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Reemplaza las tarifas ACTIVAS de la ruta por la lista recibida.
     * Se permiten varias tarifas por tipo de contenido (distintos rangos de peso).
     * Ninguna tabla referencia tarifas_encomiendas, por eso se puede borrar y reinsertar.
     * Las filas ya dadas de baja (papelera) no se tocan.
     */
    private function sincronizarTarifas($o, $d, $s, array $tarifas, $estado) {
        $this->pdo->prepare(
            "DELETE FROM tarifas_encomiendas
             WHERE id_sucursal_origen = :o AND id_sucursal_destino = :d AND id_sindicato = :s
               AND delete_tarifa_encomienda IS NULL"
        )->execute([':o' => $o, ':d' => $d, ':s' => $s]);

        $ins = $this->pdo->prepare(
            "INSERT INTO tarifas_encomiendas
                (id_sucursal_origen, id_sucursal_destino, id_sindicato, id_encomienda_contenido, peso_minimo, peso_maximo,
                 precio_tarifa_encomienda, estado_tarifa_encomienda, create_tarifa_encomienda)
             VALUES (:o, :d, :s, :c, :pmin, :pmax, :p, :e, NOW())"
        );
        foreach ($tarifas as $t) {
            $ins->execute([':o' => $o, ':d' => $d, ':s' => $s, ':c' => (int)$t['id_contenido'],
                           ':pmin' => $t['peso_min'], ':pmax' => $t['peso_max'], ':p' => $t['precio'],
                           ':e' => (int)$estado]);
        }
    }

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

            $this->pdo->prepare(
                "UPDATE tarifas_encomiendas SET estado_tarifa_encomienda = :e, update_tarifa_encomienda = NOW()
                 WHERE id_sucursal_origen = :o AND id_sucursal_destino = :d AND id_sindicato = :s AND delete_tarifa_encomienda IS NULL"
            )->execute([':e' => (int)$estado, ':o' => (int)$ruta['id_sucursal_origen'],
                        ':d' => (int)$ruta['id_sucursal_destino'], ':s' => (int)$ruta['id_sindicato']]);

            $this->pdo->commit();
            return $ok;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

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
                 WHERE id_sucursal_origen = :o AND id_sucursal_destino = :d AND id_sindicato = :s AND delete_tarifa_encomienda IS NULL"
            )->execute([':f' => $fyh, ':o' => $ruta['id_sucursal_origen'], ':d' => $ruta['id_sucursal_destino'], ':s' => $ruta['id_sindicato']]);

            $this->pdo->commit();
            return $ok;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function restaurar($id, array $ruta) {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                "UPDATE tarifas_encomiendas SET delete_tarifa_encomienda = NULL, estado_tarifa_encomienda = 1,
                        update_tarifa_encomienda = NOW()
                 WHERE id_sucursal_origen = :o AND id_sucursal_destino = :d AND id_sindicato = :s
                   AND delete_tarifa_encomienda = :f"
            )->execute([':o' => $ruta['id_sucursal_origen'], ':d' => $ruta['id_sucursal_destino'],
                        ':s' => $ruta['id_sindicato'], ':f' => $ruta['delete_precio_pasaje']]);

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