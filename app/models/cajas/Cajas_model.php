<?php
/**
 * Cajas_model
 * CRUD de cajas con borrado suave (delete_caja + estado_caja = 0) y turnos de caja
 * (historiales_cajas: apertura / arqueo / cierre).
 *
 * Notas de la BD:
 *  - UK_sucursal_caja (id_sucursal, nombre_caja) cuenta también las cajas eliminadas:
 *    los duplicados NO filtran delete_caja y se recupera desde la papelera.
 *  - cajas no tiene sindicato propio: se filtra por sucursales.id_sindicato.
 *  - Turno abierto = estado_historial_caja = 1 AND fecha_cierre IS NULL.
 *  - Monto del sistema = inicial + pasajes + encomiendas pagadas en origen + cobros COD
 *    (entregas) + ingresos manuales - egresos manuales, todo por id_historial_caja.
 * Las lecturas devuelven [] / null ante error; las escrituras dejan subir la excepción.
 */
class Cajas_model {
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
                    c.id_caja,
                    c.id_sucursal,
                    c.nombre_caja,
                    CAST(IFNULL(c.estado_caja, 1) AS UNSIGNED) AS estado_caja,
                    c.create_caja,
                    c.update_caja,
                    c.delete_caja,
                    s.nombre_sucursal,
                    s.ciudad_sucursal,
                    h.id_historial_caja AS id_historial_abierto,
                    h.id_usuario        AS id_usuario_abierto,
                    h.fecha_apertura    AS fecha_apertura_abierto,
                    h.monto_inicial     AS monto_inicial_abierto,
                    TRIM(CONCAT(IFNULL(pe.nombre_persona,''), ' ', IFNULL(pe.apellido_paterno_persona,''))) AS cajero_abierto,
                    (SELECT COUNT(*) FROM historiales_cajas hc
                      WHERE hc.id_caja = c.id_caja AND hc.delete_historial_caja IS NULL) AS total_turnos
                FROM cajas c
                INNER JOIN sucursales s ON c.id_sucursal = s.id_sucursal
                LEFT JOIN historiales_cajas h
                       ON h.id_caja = c.id_caja
                      AND h.estado_historial_caja = 1
                      AND h.fecha_cierre IS NULL
                      AND h.delete_historial_caja IS NULL
                LEFT JOIN usuarios u  ON h.id_usuario = u.id_usuario
                LEFT JOIN personas pe ON u.id_persona = pe.id_persona ";
    }

    /* ---------------- Lecturas ---------------- */

    public function getCajas($id_sindicato) {
        try {
            $st = $this->pdo->prepare($this->baseSelect() .
                "WHERE s.id_sindicato = :s AND c.delete_caja IS NULL
                 ORDER BY s.ciudad_sucursal ASC, c.nombre_caja ASC");
            $st->execute([':s' => $id_sindicato]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Cajas_model::getCajas: ' . $e->getMessage());
            return [];
        }
    }

    public function getEliminadas($id_sindicato) {
        try {
            $st = $this->pdo->prepare($this->baseSelect() .
                "WHERE s.id_sindicato = :s AND c.delete_caja IS NOT NULL
                 ORDER BY c.delete_caja DESC");
            $st->execute([':s' => $id_sindicato]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Cajas_model::getEliminadas: ' . $e->getMessage());
            return [];
        }
    }

    public function contarEliminadas($id_sindicato) {
        try {
            $st = $this->pdo->prepare(
                "SELECT COUNT(*) FROM cajas c
                 INNER JOIN sucursales s ON c.id_sucursal = s.id_sucursal
                 WHERE s.id_sindicato = :s AND c.delete_caja IS NOT NULL"
            );
            $st->execute([':s' => $id_sindicato]);
            return (int)$st->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function obtenerCaja($id, $id_sindicato, $incluirEliminadas = false) {
        try {
            $sql = $this->baseSelect() . "WHERE c.id_caja = :id AND s.id_sindicato = :s";
            if (!$incluirEliminadas) $sql .= " AND c.delete_caja IS NULL";
            $st = $this->pdo->prepare($sql . " LIMIT 1");
            $st->execute([':id' => $id, ':s' => $id_sindicato]);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log('Cajas_model::obtenerCaja: ' . $e->getMessage());
            return null;
        }
    }

    /** Sucursales vigentes del sindicato (para el select del formulario). */
    public function getSucursales($id_sindicato) {
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

    public function sucursalPerteneceASindicato($id_sucursal, $id_sindicato) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM sucursales
             WHERE id_sucursal = :id AND id_sindicato = :s AND delete_sucursal IS NULL
               AND (estado_sucursal = 1 OR estado_sucursal IS NULL)"
        );
        $st->execute([':id' => $id_sucursal, ':s' => $id_sindicato]);
        return (int)$st->fetchColumn() > 0;
    }

    /** ¿Ya existe una caja (vigente O eliminada) con ese nombre en la sucursal? */
    public function nombreDuplicado($id_sucursal, $nombre, $excluirId = 0) {
        $st = $this->pdo->prepare(
            "SELECT delete_caja FROM cajas
             WHERE id_sucursal = :s AND LOWER(nombre_caja) = :n AND id_caja <> :x LIMIT 1"
        );
        $st->execute([':s' => $id_sucursal, ':n' => mb_strtolower($nombre, 'UTF-8'), ':x' => (int)$excluirId]);
        $f = $st->fetch(PDO::FETCH_ASSOC);
        return $f ? ['eliminado' => $f['delete_caja'] !== null] : null;
    }

    public function tieneTurnoAbierto($id_caja) {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM historiales_cajas
             WHERE id_caja = :c AND estado_historial_caja = 1 AND fecha_cierre IS NULL AND delete_historial_caja IS NULL"
        );
        $st->execute([':c' => $id_caja]);
        return (int)$st->fetchColumn() > 0;
    }

    /** id_historial_caja del turno abierto del usuario (o 0). Útil para enlazar ventas a la caja. */
    public function idHistorialAbiertoDeUsuario($id_usuario) {
        try {
            $st = $this->pdo->prepare(
                "SELECT id_historial_caja FROM historiales_cajas
                 WHERE id_usuario = :u AND estado_historial_caja = 1 AND fecha_cierre IS NULL
                   AND delete_historial_caja IS NULL
                 ORDER BY id_historial_caja DESC LIMIT 1"
            );
            $st->execute([':u' => $id_usuario]);
            return (int)$st->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /** Últimos turnos de la caja (para el detalle). */
    public function getHistorial($id_caja, $limite = 10) {
        try {
            $st = $this->pdo->prepare(
                "SELECT h.id_historial_caja, h.monto_inicial, h.monto_final_sistema, h.monto_final_declarado,
                        h.diferencia_historial_caja, h.fecha_apertura, h.fecha_cierre,
                        CAST(IFNULL(h.estado_historial_caja, 0) AS UNSIGNED) AS abierta,
                        TRIM(CONCAT(IFNULL(pe.nombre_persona,''), ' ', IFNULL(pe.apellido_paterno_persona,''))) AS cajero
                 FROM historiales_cajas h
                 LEFT JOIN usuarios u  ON h.id_usuario = u.id_usuario
                 LEFT JOIN personas pe ON u.id_persona = pe.id_persona
                 WHERE h.id_caja = :c AND h.delete_historial_caja IS NULL
                 ORDER BY h.id_historial_caja DESC LIMIT " . (int)$limite
            );
            $st->execute([':c' => $id_caja]);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /** Un turno con su caja, sucursal y cajero; restringido al sindicato. */
    public function obtenerHistorial($id_historial, $id_sindicato) {
        try {
            $st = $this->pdo->prepare(
                "SELECT h.*, CAST(IFNULL(h.estado_historial_caja, 0) AS UNSIGNED) AS abierta,
                        c.nombre_caja, s.nombre_sucursal, s.ciudad_sucursal,
                        TRIM(CONCAT(IFNULL(pe.nombre_persona,''), ' ', IFNULL(pe.apellido_paterno_persona,''))) AS cajero
                 FROM historiales_cajas h
                 INNER JOIN cajas c ON h.id_caja = c.id_caja
                 INNER JOIN sucursales s ON c.id_sucursal = s.id_sucursal
                 LEFT JOIN usuarios u  ON h.id_usuario = u.id_usuario
                 LEFT JOIN personas pe ON u.id_persona = pe.id_persona
                 WHERE h.id_historial_caja = :h AND s.id_sindicato = :s AND h.delete_historial_caja IS NULL
                 LIMIT 1"
            );
            $st->execute([':h' => $id_historial, ':s' => $id_sindicato]);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /** Desglose de lo cobrado en un turno y total esperado en el sistema. */
    public function getResumen($id_historial, $monto_inicial) {
        $zero = ['inicial' => (float)$monto_inicial, 'pasajes' => 0, 'n_pasajes' => 0, 'encomiendas' => 0,
                 'n_encomiendas' => 0, 'entregas' => 0, 'ingresos_extra' => 0, 'egresos' => 0, 'total' => (float)$monto_inicial];
        try {
            $sql = "SELECT
                (SELECT IFNULL(SUM(p.total_pasaje),0) FROM pasajes p
                  WHERE p.id_historial_caja = :h1 AND (p.estado_pasaje = 1 OR p.estado_pasaje IS NULL)) AS pasajes,
                (SELECT COUNT(*) FROM pasajes p
                  WHERE p.id_historial_caja = :h2 AND (p.estado_pasaje = 1 OR p.estado_pasaje IS NULL)) AS n_pasajes,
                (SELECT IFNULL(SUM(e.monto_encomienda),0) FROM encomiendas e
                  WHERE e.id_historial_caja = :h3 AND e.estado_pago_encomienda = 1
                    AND (e.estado_encomienda = 1 OR e.estado_encomienda IS NULL)) AS encomiendas,
                (SELECT COUNT(*) FROM encomiendas e
                  WHERE e.id_historial_caja = :h4 AND (e.estado_encomienda = 1 OR e.estado_encomienda IS NULL)) AS n_encomiendas,
                (SELECT IFNULL(SUM(en.monto_entrega_encomienda),0) FROM entregas_encomiendas en
                  WHERE en.id_historial_caja = :h5 AND en.delete_entrega_encomienda IS NULL) AS entregas,
                (SELECT IFNULL(SUM(m.monto_movimiento_caja),0) FROM movimientos_cajas m
                  WHERE m.id_historial_caja = :h6 AND m.tipo_movimiento_caja = 1
                    AND m.delete_movimiento_caja IS NULL AND (m.estado_movimiento_caja = 1 OR m.estado_movimiento_caja IS NULL)) AS ingresos_extra,
                (SELECT IFNULL(SUM(m.monto_movimiento_caja),0) FROM movimientos_cajas m
                  WHERE m.id_historial_caja = :h7 AND m.tipo_movimiento_caja = 0
                    AND m.delete_movimiento_caja IS NULL AND (m.estado_movimiento_caja = 1 OR m.estado_movimiento_caja IS NULL)) AS egresos";
            $st = $this->pdo->prepare($sql);
            $p = [];
            for ($i = 1; $i <= 7; $i++) $p[":h$i"] = $id_historial;
            $st->execute($p);
            $f = $st->fetch(PDO::FETCH_ASSOC);
            if (!$f) return $zero;

            $r = [
                'inicial'        => (float)$monto_inicial,
                'pasajes'        => (float)$f['pasajes'],
                'n_pasajes'      => (int)$f['n_pasajes'],
                'encomiendas'    => (float)$f['encomiendas'],
                'n_encomiendas'  => (int)$f['n_encomiendas'],
                'entregas'       => (float)$f['entregas'],
                'ingresos_extra' => (float)$f['ingresos_extra'],
                'egresos'        => (float)$f['egresos'],
            ];
            $r['total'] = round($r['inicial'] + $r['pasajes'] + $r['encomiendas'] + $r['entregas']
                              + $r['ingresos_extra'] - $r['egresos'], 2);
            return $r;
        } catch (PDOException $e) {
            error_log('Cajas_model::getResumen: ' . $e->getMessage());
            return $zero;
        }
    }

    /* ---------------- Escrituras de la caja ---------------- */

    public function crear(array $d) {
        $st = $this->pdo->prepare(
            "INSERT INTO cajas (id_sucursal, nombre_caja, estado_caja, create_caja) VALUES (:s, :n, 1, NOW())"
        );
        $st->execute([':s' => $d['id_sucursal'], ':n' => $d['nombre']]);
        return (int)$this->pdo->lastInsertId();
    }

    /** La sucursal es parte de la identidad de la caja: solo se edita el nombre. */
    public function actualizar($id, $nombre) {
        $st = $this->pdo->prepare(
            "UPDATE cajas SET nombre_caja = :n, update_caja = NOW() WHERE id_caja = :id AND delete_caja IS NULL"
        );
        return $st->execute([':n' => $nombre, ':id' => $id]);
    }

    public function cambiarEstado($id, $estado) {
        $st = $this->pdo->prepare(
            "UPDATE cajas SET estado_caja = :e, update_caja = NOW() WHERE id_caja = :id AND delete_caja IS NULL"
        );
        $st->bindValue(':e', (int)$estado, PDO::PARAM_INT);
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    public function eliminar($id, $fyh) {
        $st = $this->pdo->prepare(
            "UPDATE cajas SET delete_caja = :f, estado_caja = 0 WHERE id_caja = :id AND delete_caja IS NULL"
        );
        $st->bindValue(':f', $fyh, PDO::PARAM_STR);
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    public function restaurar($id) {
        $st = $this->pdo->prepare(
            "UPDATE cajas SET delete_caja = NULL, estado_caja = 1, update_caja = NOW()
             WHERE id_caja = :id AND delete_caja IS NOT NULL"
        );
        $st->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $st->execute();
        return $st->rowCount() > 0;
    }

    /* ---------------- Turnos de caja ---------------- */

    /** Abre un turno. Una caja y un usuario solo pueden tener UN turno abierto. */
    public function abrir($id_caja, $id_usuario, $monto) {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                "SELECT CAST(IFNULL(estado_caja,1) AS UNSIGNED) AS estado, delete_caja
                 FROM cajas WHERE id_caja = :id FOR UPDATE"
            );
            $st->execute([':id' => $id_caja]);
            $c = $st->fetch(PDO::FETCH_ASSOC);
            if (!$c || $c['delete_caja'] !== null) throw new Exception('La caja no existe o fue eliminada.');
            if ((int)$c['estado'] !== 1) throw new Exception('La caja está inactiva. Actívela antes de aperturarla.');

            if ($this->tieneTurnoAbierto($id_caja)) {
                throw new Exception('Esta caja ya tiene un turno abierto.');
            }
            if ($this->idHistorialAbiertoDeUsuario($id_usuario) > 0) {
                throw new Exception('Usted ya tiene una caja abierta. Ciérrela antes de abrir otra.');
            }

            $this->pdo->prepare(
                "INSERT INTO historiales_cajas
                    (id_caja, id_usuario, monto_inicial, fecha_apertura, estado_historial_caja, create_historial_caja)
                 VALUES (:c, :u, :m, NOW(), 1, NOW())"
            )->execute([':c' => $id_caja, ':u' => $id_usuario, ':m' => $monto]);
            $id = (int)$this->pdo->lastInsertId();

            $this->pdo->commit();
            return $id;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Cierra el turno (arqueo): guarda monto del sistema, declarado y diferencia.
     * @return array ['sistema' => float, 'diferencia' => float]
     */
    public function cerrar($id_historial, $declarado) {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                "SELECT monto_inicial, estado_historial_caja, fecha_cierre
                 FROM historiales_cajas WHERE id_historial_caja = :h AND delete_historial_caja IS NULL FOR UPDATE"
            );
            $st->execute([':h' => $id_historial]);
            $h = $st->fetch(PDO::FETCH_ASSOC);
            if (!$h) throw new Exception('El turno de caja no existe.');
            if ($h['fecha_cierre'] !== null) throw new Exception('Este turno ya fue cerrado.');

            $resumen    = $this->getResumen($id_historial, $h['monto_inicial']);
            $sistema    = $resumen['total'];
            $diferencia = round($declarado - $sistema, 2);

            $this->pdo->prepare(
                "UPDATE historiales_cajas SET
                    monto_final_sistema = :s, monto_final_declarado = :d, diferencia_historial_caja = :df,
                    fecha_cierre = NOW(), estado_historial_caja = 0, update_historial_caja = NOW()
                 WHERE id_historial_caja = :h"
            )->execute([':s' => $sistema, ':d' => $declarado, ':df' => $diferencia, ':h' => $id_historial]);

            $this->pdo->commit();
            return ['sistema' => $sistema, 'diferencia' => $diferencia];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }
}
?>