<?php
/**
 * Reportes_model (solo lectura).
 * reporte($f) devuelve una estructura genérica que usan la pantalla, la impresión y el Excel:
 *   ['tipo','titulo','periodo','saldo_label','saldo','kpi'=>[ing,egr,pre,saldo],
 *    'tablas'=>[ ['titulo','cols'=>[[label,tipo]],'filas'=>[[...]],'pie'=>[...]|null ] ]]
 * Tipos de columna: texto | entero | monto.
 *
 * Fuentes de ingresos: pasajes (detalles activos), encomiendas pagadas en origen, cobros COD en
 * destino (entregas_encomiendas) y movimientos de caja tipo ingreso. Egresos y préstamos salen de
 * movimientos_cajas (tipo egreso); es préstamo si el concepto contiene "prestamo/préstamo".
 */
class Reportes_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
        if ($this->pdo) $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getSucursalesPermitidas($id_sindicato, $soloUna = 0) {
        try {
            $sql = "SELECT id_sucursal, ciudad_sucursal, nombre_sucursal FROM sucursales
                    WHERE id_sindicato = :s AND delete_sucursal IS NULL
                      AND (estado_sucursal = 1 OR estado_sucursal IS NULL)";
            $p = [':s' => $id_sindicato];
            if ($soloUna > 0) { $sql .= " AND id_sucursal = :u"; $p[':u'] = $soloUna; }
            $st = $this->pdo->prepare($sql . " ORDER BY ciudad_sucursal ASC, nombre_sucursal ASC");
            $st->execute($p);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Reportes_model::getSucursalesPermitidas: ' . $e->getMessage());
            return [];
        }
    }

    /* ---------------- utilidades ---------------- */

    /** WHERE de sucursal IN + rango [ini, fin] inclusivo. Un solo uso por consulta. */
    private function filtro($colSuc, $colFecha, array $g, array &$p) {
        $in = [];
        foreach (array_values($g['sucursales']) as $i => $id) { $in[] = ":fs$i"; $p[":fs$i"] = (int)$id; }
        $p[':fd'] = $g['ini'];
        $p[':fh'] = $g['fin'];
        return "$colSuc IN (" . implode(',', $in) . ") AND $colFecha >= :fd AND $colFecha < DATE_ADD(:fh, INTERVAL 1 DAY)";
    }

    private function q($sql, array $p) {
        $st = $this->pdo->prepare($sql);
        $st->execute($p);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    private function esPrestamo() {
        return "(LOWER(m.concepto_movimiento_caja) LIKE '%prestamo%' OR LOWER(m.concepto_movimiento_caja) LIKE '%préstamo%')";
    }

    private function movFrom() {
        return "FROM movimientos_cajas m
                INNER JOIN historiales_cajas h ON m.id_historial_caja = h.id_historial_caja
                INNER JOIN cajas c ON h.id_caja = c.id_caja
                WHERE m.delete_movimiento_caja IS NULL
                  AND (m.estado_movimiento_caja = 1 OR m.estado_movimiento_caja IS NULL)";
    }

    /* ---------------- Totales por mes ---------------- */

    /** @return array ym => ['ing'=>,'egr'=>,'pre'=>] */
    private function mensual(array $f) {
        $out = [];
        $add = function ($rows, $campo) use (&$out) {
            foreach ($rows as $r) {
                if (!isset($out[$r['ym']])) $out[$r['ym']] = ['ing' => 0.0, 'egr' => 0.0, 'pre' => 0.0];
                $out[$r['ym']][$campo] += (float)$r['total'];
            }
        };

        $p = []; $w = $this->filtro('u.id_sucursal', 'p.create_pasaje', $f, $p);
        $add($this->q("SELECT DATE_FORMAT(p.create_pasaje, '%Y-%m') AS ym, SUM(dp.precio_detalle_pasaje) AS total
                       FROM detalles_pasajes dp
                       INNER JOIN pasajes p ON dp.id_pasaje = p.id_pasaje
                       INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                       WHERE (p.estado_pasaje = 1 OR p.estado_pasaje IS NULL)
                         AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL) AND $w
                       GROUP BY ym", $p), 'ing');

        $p = []; $w = $this->filtro('e.id_sucursal_origen', 'e.create_encomienda', $f, $p);
        $add($this->q("SELECT DATE_FORMAT(e.create_encomienda, '%Y-%m') AS ym, SUM(e.monto_encomienda) AS total
                       FROM encomiendas e
                       LEFT JOIN entregas_encomiendas en ON en.id_encomienda = e.id_encomienda
                       WHERE (e.estado_encomienda = 1 OR e.estado_encomienda IS NULL)
                         AND CAST(e.estado_pago_encomienda AS UNSIGNED) = 1
                         AND IFNULL(en.monto_entrega_encomienda, 0) = 0 AND $w
                       GROUP BY ym", $p), 'ing');

        $p = []; $w = $this->filtro('e.id_sucursal_destino', 'en.create_entrega_encomienda', $f, $p);
        $add($this->q("SELECT DATE_FORMAT(en.create_entrega_encomienda, '%Y-%m') AS ym, SUM(en.monto_entrega_encomienda) AS total
                       FROM entregas_encomiendas en
                       INNER JOIN encomiendas e ON en.id_encomienda = e.id_encomienda
                       WHERE en.delete_entrega_encomienda IS NULL AND IFNULL(en.monto_entrega_encomienda, 0) > 0 AND $w
                       GROUP BY ym", $p), 'ing');

        $p = []; $w = $this->filtro('c.id_sucursal', 'm.create_movimiento_caja', $f, $p);
        $rows = $this->q("SELECT DATE_FORMAT(m.create_movimiento_caja, '%Y-%m') AS ym,
                                 CAST(m.tipo_movimiento_caja AS UNSIGNED) AS tipo,
                                 IF(" . $this->esPrestamo() . ", 1, 0) AS pre,
                                 SUM(m.monto_movimiento_caja) AS total
                          " . $this->movFrom() . " AND $w
                          GROUP BY ym, tipo, pre", $p);
        foreach ($rows as $r) {
            $campo = (int)$r['tipo'] === 1 ? 'ing' : ((int)$r['pre'] === 1 ? 'pre' : 'egr');
            $add([['ym' => $r['ym'], 'total' => $r['total']]], $campo);
        }
        return $out;
    }

    /* ---------------- Reporte ---------------- */

    public function reporte(array $f) {
        $mens = $this->mensual($f);
        $calc = []; $vienen = (float)$f['saldo'];
        foreach ($f['meses'] as $ym) {
            $m = $mens[$ym] ?? ['ing' => 0.0, 'egr' => 0.0, 'pre' => 0.0];
            $total = round($m['ing'] + $vienen, 2);
            $saldo = round($total - $m['egr'] - $m['pre'], 2);
            $calc[$ym] = ['ing' => round($m['ing'], 2), 'vienen' => $vienen, 'total' => $total,
                          'egr' => round($m['egr'], 2), 'pre' => round($m['pre'], 2), 'saldo' => $saldo];
            $vienen = $saldo;
        }

        $base = DateTime::createFromFormat('!Y-m', $f['meses'][0]);
        $ant  = (clone $base)->modify('-1 month');
        $r = [
            'tipo' => $f['tipo'], 'saldo' => (float)$f['saldo'],
            'saldo_label' => 'SALDO DE ' . mb_strtoupper(ValidarReportes::nombreMes($ant->format('n')), 'UTF-8'),
        ];

        if ($f['tipo'] === 'resumen') {
            $r += $this->armarResumen($f, $calc);
        } else {
            $r += $this->armarDetalle($f, $calc[end($f['meses'])], end($f['meses']));
        }
        return $r;
    }

    private function armarResumen(array $f, array $calc) {
        $filas = []; $si = $se = $sp = 0.0;
        foreach ($f['meses'] as $ym) {
            $c = $calc[$ym];
            $filas[] = [mb_strtoupper(ValidarReportes::etiqueta($ym), 'UTF-8'), $c['ing'], $c['vienen'], $c['total'], $c['egr'], $c['pre'], $c['saldo']];
            $si += $c['ing']; $se += $c['egr']; $sp += $c['pre'];
        }
        $ult = $calc[end($f['meses'])];
        $a = ValidarReportes::etiqueta($f['meses'][0]);
        $b = ValidarReportes::etiqueta(end($f['meses']));

        return [
            'titulo'  => 'RESUMEN INFORME ECONÓMICO ' . ($a === $b ? 'DE ' . mb_strtoupper($a, 'UTF-8') : 'DE ' . mb_strtoupper($a, 'UTF-8') . ' A ' . mb_strtoupper($b, 'UTF-8')),
            'periodo' => $a === $b ? $a : "$a a $b",
            'kpi'     => ['ing' => round($si, 2), 'egr' => round($se, 2), 'pre' => round($sp, 2), 'saldo' => $ult['saldo']],
            'tablas'  => [[
                'titulo' => null,
                'cols'   => [['Mes', 'texto'], ['Ingresos', 'monto'], ['Vienen', 'monto'], ['Total ingresos', 'monto'],
                             ['Egresos', 'monto'], ['Préstamos', 'monto'], ['Saldo', 'monto']],
                'filas'  => $filas,
                'pie'    => ['TOTAL', round($si, 2), null, null, round($se, 2), round($sp, 2), $ult['saldo']],
            ]],
        ];
    }

    private function armarDetalle(array $f, array $c, $ym) {
        $g = ['sucursales' => $f['sucursales'], 'ini' => $f['mes_ini'], 'fin' => $f['mes_fin']];
        $num = function ($x) { return [$x[0], (int)$x[1], round((float)$x[2], 2)]; };
        $mapa = function ($rows) use ($num) {
            return array_map(function ($r) use ($num) { return $num([$r['concepto'], $r['cantidad'], $r['monto']]); }, $rows);
        };
        $tabla = function ($titulo, $filas, $colConcepto) {
            $cant = array_sum(array_column($filas, 1));
            $mont = round(array_sum(array_column($filas, 2)), 2);
            return ['titulo' => $titulo, 'cols' => [[$colConcepto, 'texto'], ['Cantidad', 'entero'], ['Monto', 'monto']],
                    'filas' => $filas, 'pie' => ['TOTAL', $cant, $mont]];
        };

        $tablas = [];

        $p = []; $w = $this->filtro('u.id_sucursal', 'p.create_pasaje', $g, $p);
        $filas = $mapa($this->q("SELECT IF(t.id_turno IS NULL, 'Sin turno (en espera)', CONCAT(so.ciudad_sucursal, ' ➔ ', sd.ciudad_sucursal)) AS concepto,
                                        COUNT(*) AS cantidad, SUM(dp.precio_detalle_pasaje) AS monto
                                 FROM detalles_pasajes dp
                                 INNER JOIN pasajes p ON dp.id_pasaje = p.id_pasaje
                                 INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                                 LEFT JOIN turnos t ON dp.id_turno = t.id_turno
                                 LEFT JOIN sucursales so ON t.id_sucursal_origen = so.id_sucursal
                                 LEFT JOIN sucursales sd ON t.id_sucursal_destino = sd.id_sucursal
                                 WHERE (p.estado_pasaje = 1 OR p.estado_pasaje IS NULL)
                                   AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL) AND $w
                                 GROUP BY concepto ORDER BY monto DESC", $p));
        if ($filas) $tablas[] = $tabla('INGRESOS POR PASAJES (POR RUTA)', $filas, 'Ruta');

        $p = []; $w = $this->filtro('e.id_sucursal_origen', 'e.create_encomienda', $g, $p);
        $filas = $mapa($this->q("SELECT CONCAT(so.ciudad_sucursal, ' ➔ ', sd.ciudad_sucursal) AS concepto,
                                        COUNT(*) AS cantidad, SUM(e.monto_encomienda) AS monto
                                 FROM encomiendas e
                                 INNER JOIN sucursales so ON e.id_sucursal_origen = so.id_sucursal
                                 INNER JOIN sucursales sd ON e.id_sucursal_destino = sd.id_sucursal
                                 LEFT JOIN entregas_encomiendas en ON en.id_encomienda = e.id_encomienda
                                 WHERE (e.estado_encomienda = 1 OR e.estado_encomienda IS NULL)
                                   AND CAST(e.estado_pago_encomienda AS UNSIGNED) = 1
                                   AND IFNULL(en.monto_entrega_encomienda, 0) = 0 AND $w
                                 GROUP BY concepto ORDER BY monto DESC", $p));
        if ($filas) $tablas[] = $tabla('INGRESOS POR ENCOMIENDAS PAGADAS EN ORIGEN (POR RUTA)', $filas, 'Ruta');

        $otros = [];
        $p = []; $w = $this->filtro('e.id_sucursal_destino', 'en.create_entrega_encomienda', $g, $p);
        $cod = $this->q("SELECT COUNT(*) AS cantidad, IFNULL(SUM(en.monto_entrega_encomienda), 0) AS monto
                         FROM entregas_encomiendas en INNER JOIN encomiendas e ON en.id_encomienda = e.id_encomienda
                         WHERE en.delete_entrega_encomienda IS NULL AND IFNULL(en.monto_entrega_encomienda, 0) > 0 AND $w", $p)[0];
        if ((int)$cod['cantidad'] > 0) $otros[] = $num(['Cobros de encomiendas COD en destino', $cod['cantidad'], $cod['monto']]);
        $otros = array_merge($otros, $mapa($this->movimientos($g, 'ing')));
        if ($otros) $tablas[] = $tabla('OTROS INGRESOS', $otros, 'Concepto');

        $filas = $mapa($this->movimientos($g, 'egr'));
        if ($filas) $tablas[] = $tabla('EGRESOS DEL MES', $filas, 'Concepto');
        $filas = $mapa($this->movimientos($g, 'pre'));
        if ($filas) $tablas[] = $tabla('PRÉSTAMOS DEL MES', $filas, 'Concepto');

        $tablas[] = [
            'titulo' => 'RESUMEN DEL MES',
            'cols'   => [['Concepto', 'texto'], ['Monto', 'monto']],
            'filas'  => [
                [mb_strtoupper('Vienen (saldo anterior)', 'UTF-8'), $c['vienen']],
                ['TOTAL INGRESOS DEL MES', $c['ing']],
                ['TOTAL INGRESOS (CON VIENEN)', $c['total']],
                ['TOTAL EGRESOS', $c['egr']],
                ['TOTAL PRÉSTAMOS', $c['pre']],
            ],
            'pie'    => ['SALDO DEL MES', $c['saldo']],
        ];

        return [
            'titulo'  => 'INFORME ECONÓMICO MES DE ' . mb_strtoupper(ValidarReportes::etiqueta($ym), 'UTF-8'),
            'periodo' => ValidarReportes::etiqueta($ym),
            'kpi'     => ['ing' => $c['ing'], 'egr' => $c['egr'], 'pre' => $c['pre'], 'saldo' => $c['saldo']],
            'tablas'  => $tablas,
        ];
    }

    /** Movimientos de caja del rango agrupados por concepto. $que: ing | egr | pre */
    private function movimientos(array $g, $que) {
        $p = []; $w = $this->filtro('c.id_sucursal', 'm.create_movimiento_caja', $g, $p);
        $cond = $que === 'ing' ? "AND CAST(m.tipo_movimiento_caja AS UNSIGNED) = 1"
              : "AND CAST(m.tipo_movimiento_caja AS UNSIGNED) = 0 AND " . ($que === 'pre' ? '' : 'NOT ') . $this->esPrestamo();
        return $this->q("SELECT m.concepto_movimiento_caja AS concepto, COUNT(*) AS cantidad, SUM(m.monto_movimiento_caja) AS monto
                         " . $this->movFrom() . " $cond AND $w
                         GROUP BY m.concepto_movimiento_caja ORDER BY monto DESC", $p);
    }
}
?>