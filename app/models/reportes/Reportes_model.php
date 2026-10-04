<?php
/**
 * Reportes_model (solo lectura).
 * reporte($f) devuelve una estructura genérica que usan la pantalla, la impresión y el Excel:
 *   ['tipo','titulo','periodo','saldo_label','saldo','kpi'=>[inicial,ing,egr,pre,saldo],
 *    'tablas'=>[ ['titulo','cols'=>[[label,tipo]],'filas'=>[[...]],'pie'=>[...]|null ] ]]
 * Tipos de columna: texto | entero | monto.
 *
 * Fuentes de ingresos: pasajes (detalles activos), encomiendas pagadas en origen, cobros COD en
 * destino (entregas_encomiendas) y movimientos de caja tipo ingreso. Egresos y préstamos salen de
 * movimientos_cajas (tipo egreso); es préstamo si el concepto contiene "prestamo/préstamo".
 *
 * Tipos "detalle" (diario/semanal/mensual): un solo periodo con desglose; el saldo "Vienen"
 * se acumula desde el 1 de enero ($f['acum_desde']).
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

    /** 'YYYY-MM' del primer registro económico de las sucursales dadas (o null si no hay). */
    public function primerMes(array $ids) {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) return null;
        $in = implode(',', $ids); // enteros ya saneados

        try {
            $fechas = [];
            $fechas[] = $this->pdo->query(
                "SELECT MIN(p.create_pasaje) FROM pasajes p
                 INNER JOIN usuarios u ON p.id_usuario = u.id_usuario WHERE u.id_sucursal IN ($in)")->fetchColumn();
            $fechas[] = $this->pdo->query(
                "SELECT MIN(e.create_encomienda) FROM encomiendas e WHERE e.id_sucursal_origen IN ($in)")->fetchColumn();
            $fechas[] = $this->pdo->query(
                "SELECT MIN(m.create_movimiento_caja) FROM movimientos_cajas m
                 INNER JOIN historiales_cajas h ON m.id_historial_caja = h.id_historial_caja
                 INNER JOIN cajas c ON h.id_caja = c.id_caja WHERE c.id_sucursal IN ($in)")->fetchColumn();

            $fechas = array_filter($fechas);
            return $fechas ? substr(min($fechas), 0, 7) : null;
        } catch (PDOException $e) {
            error_log('Reportes_model::primerMes: ' . $e->getMessage());
            return null;
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

    /* ---------------- Totales agrupados ---------------- */

    /**
     * Agrupa por mes ('Y-m') o por día ('Y-m-d') según $f['fmt'], dentro de [$f['ini'], $f['fin']].
     * @return array clave => ['ing'=>,'egr'=>,'pre'=>]
     */
    private function agrupado(array $f) {
        $fmt = $f['fmt'] === 'd' ? '%Y-%m-%d' : '%Y-%m';
        $out = [];
        $add = function ($rows, $campo) use (&$out) {
            foreach ($rows as $r) {
                if (!isset($out[$r['ym']])) $out[$r['ym']] = ['ing' => 0.0, 'egr' => 0.0, 'pre' => 0.0];
                $out[$r['ym']][$campo] += (float)$r['total'];
            }
        };

        $p = []; $w = $this->filtro('u.id_sucursal', 'p.create_pasaje', $f, $p);
        $add($this->q("SELECT DATE_FORMAT(p.create_pasaje, '$fmt') AS ym, SUM(dp.precio_detalle_pasaje) AS total
                       FROM detalles_pasajes dp
                       INNER JOIN pasajes p ON dp.id_pasaje = p.id_pasaje
                       INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                       WHERE (p.estado_pasaje = 1 OR p.estado_pasaje IS NULL)
                         AND (dp.estado_detalle_pasaje = 1 OR dp.estado_detalle_pasaje IS NULL) AND $w
                       GROUP BY ym", $p), 'ing');

        $p = []; $w = $this->filtro('e.id_sucursal_origen', 'e.create_encomienda', $f, $p);
        $add($this->q("SELECT DATE_FORMAT(e.create_encomienda, '$fmt') AS ym, SUM(e.monto_encomienda) AS total
                       FROM encomiendas e
                       LEFT JOIN entregas_encomiendas en ON en.id_encomienda = e.id_encomienda
                       WHERE (e.estado_encomienda = 1 OR e.estado_encomienda IS NULL)
                         AND CAST(e.estado_pago_encomienda AS UNSIGNED) = 1
                         AND IFNULL(en.monto_entrega_encomienda, 0) = 0 AND $w
                       GROUP BY ym", $p), 'ing');

        $p = []; $w = $this->filtro('e.id_sucursal_destino', 'en.create_entrega_encomienda', $f, $p);
        $add($this->q("SELECT DATE_FORMAT(en.create_entrega_encomienda, '$fmt') AS ym, SUM(en.monto_entrega_encomienda) AS total
                       FROM entregas_encomiendas en
                       INNER JOIN encomiendas e ON en.id_encomienda = e.id_encomienda
                       WHERE en.delete_entrega_encomienda IS NULL AND IFNULL(en.monto_entrega_encomienda, 0) > 0 AND $w
                       GROUP BY ym", $p), 'ing');

        $p = []; $w = $this->filtro('c.id_sucursal', 'm.create_movimiento_caja', $f, $p);
        $rows = $this->q("SELECT DATE_FORMAT(m.create_movimiento_caja, '$fmt') AS ym,
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

    /** Suma lo agrupado cuya fecha (mes => día 1) cae dentro de [desde, hasta]. */
    private function sumar(array $agr, $desde, $hasta) {
        $s = ['ing' => 0.0, 'egr' => 0.0, 'pre' => 0.0];
        foreach ($agr as $k => $v) {
            $d = strlen($k) === 7 ? $k . '-01' : $k;
            if ($d >= $desde && $d <= $hasta) {
                $s['ing'] += $v['ing']; $s['egr'] += $v['egr']; $s['pre'] += $v['pre'];
            }
        }
        return $s;
    }

    /* ---------------- Reporte ---------------- */

    public function reporte(array $f) {
        $agr    = $this->agrupado($f);
        $vienen = (float)$f['saldo'];

        // Tipos de detalle: el saldo "Vienen" acumula lo ocurrido desde el 1 de enero hasta el día anterior
        if ($f['acum_desde'] !== '' && $f['acum_desde'] < $f['buckets'][0]['desde']) {
            $ant = (new DateTime($f['buckets'][0]['desde']))->modify('-1 day')->format('Y-m-d');
            $pre = $this->sumar(
                $this->agrupado(array_merge($f, ['ini' => $f['acum_desde'], 'fin' => $ant, 'fmt' => 'm'])),
                '0000-01-01', '9999-12-31'
            );
            $vienen = round($vienen + $pre['ing'] - $pre['egr'] - $pre['pre'], 2);
        }
        $saldoIni = $vienen;

        $calc = [];
        foreach ($f['buckets'] as $i => $b) {
            $m     = $this->sumar($agr, $b['desde'], $b['hasta']);
            $total = round($m['ing'] + $vienen, 2);
            $saldo = round($total - $m['egr'] - $m['pre'], 2);
            $calc[$i] = ['ing' => round($m['ing'], 2), 'vienen' => $vienen, 'total' => $total,
                         'egr' => round($m['egr'], 2), 'pre' => round($m['pre'], 2), 'saldo' => $saldo];
            $vienen = $saldo;
        }

        $r = [
            'tipo'        => $f['tipo'],
            'saldo'       => $saldoIni,
            'saldo_label' => $this->etiquetaSaldo($f),
        ];

        $r += $f['detalle'] ? $this->armarDetalle($f, $calc[0]) : $this->armarTabla($f, $calc);
        return $r;
    }

    private function etiquetaSaldo(array $f) {
        $desde = $f['buckets'][0]['desde'];
        if ($f['fmt'] === 'm') {
            $ant = (new DateTime($desde))->modify('-1 month');
            return 'SALDO DE ' . mb_strtoupper(ValidarReportes::nombreMes($ant->format('n')), 'UTF-8');
        }
        return 'SALDO INICIAL AL ' . date('d/m/Y', strtotime($desde));
    }

    /** Tabla de 7 columnas: una fila por periodo (general, anual, rango). */
    private function armarTabla(array $f, array $calc) {
        $filas = []; $si = $se = $sp = 0.0;
        foreach ($f['buckets'] as $i => $b) {
            $c = $calc[$i];
            $filas[] = [$b['label'], $c['ing'], $c['vienen'], $c['total'], $c['egr'], $c['pre'], $c['saldo']];
            $si += $c['ing']; $se += $c['egr']; $sp += $c['pre'];
        }
        $ult = $calc[count($calc) - 1];
        $b0  = $f['buckets'][0];
        $bN  = $f['buckets'][count($f['buckets']) - 1];
        $up  = function ($s) { return mb_strtoupper($s, 'UTF-8'); };

        switch ($f['tipo']) {
            case 'consolidado':
                $titulo  = 'INFORME ECONÓMICO GENERAL · ' . $up($b0['label']) . ($b0['key'] === $bN['key'] ? '' : ' A ' . $up($bN['label']));
                $periodo = $b0['label'] . ($b0['key'] === $bN['key'] ? '' : ' a ' . $bN['label']);
                break;
            case 'anual':
                $titulo = 'INFORME ECONÓMICO ANUAL ' . $f['anio']; $periodo = 'Gestión ' . $f['anio'];
                break;
            default: // rango
                $a = date('d/m/Y', strtotime($b0['desde'])); $z = date('d/m/Y', strtotime($bN['hasta']));
                $titulo = "INFORME ECONÓMICO DEL $a AL $z"; $periodo = "$a al $z";
        }

        return [
            'titulo'  => $titulo,
            'periodo' => $periodo,
            'kpi'     => ['inicial' => (float)$f['saldo'], 'ing' => round($si, 2), 'egr' => round($se, 2),
                          'pre' => round($sp, 2), 'saldo' => $ult['saldo']],
            'tablas'  => [[
                'titulo' => null,
                'cols'   => [['Periodo / Fecha', 'texto'], ['Ingresos', 'monto'], ['Vienen', 'monto'], ['Total ingresos', 'monto'],
                             ['Egresos', 'monto'], ['Préstamos', 'monto'], ['Saldo final', 'monto']],
                'filas'  => $filas,
                'pie'    => ['TOTAL', round($si, 2), null, null, round($se, 2), round($sp, 2), $ult['saldo']],
            ]],
        ];
    }

    /** Detalle de UN periodo (día, semana o mes): por ruta, egresos, préstamos y resumen. */
    private function armarDetalle(array $f, array $c) {
        $b  = $f['buckets'][0];
        $g  = ['sucursales' => $f['sucursales'], 'ini' => $b['desde'], 'fin' => $b['hasta']];
        $ym = substr($b['desde'], 0, 7);
        $up = function ($s) { return mb_strtoupper($s, 'UTF-8'); };

        switch ($f['tipo']) {
            case 'diario':
                $d = date('d/m/Y', strtotime($b['desde']));
                $titulo = 'INFORME ECONÓMICO DEL DÍA ' . $d; $periodo = $d;
                break;
            case 'semanal':
                $titulo  = 'INFORME ECONÓMICO SEMANAL · ' . $up($b['label']) . ' · ' . $up(ValidarReportes::etiqueta($ym));
                $periodo = $b['label'] . ' · ' . ValidarReportes::etiqueta($ym);
                break;
            default: // mensual
                $titulo  = 'INFORME ECONÓMICO MES DE ' . $up(ValidarReportes::etiqueta($ym));
                $periodo = ValidarReportes::etiqueta($ym);
        }

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

        // Movimiento por día (solo semana y mes)
        if ($f['tipo'] !== 'diario') {
            $dias = $this->agrupado(array_merge($f, ['ini' => $b['desde'], 'fin' => $b['hasta'], 'fmt' => 'd']));
            ksort($dias);
            $filas = []; $ti = $te = $tp = 0.0;
            foreach ($dias as $k => $v) {
                $filas[] = [date('d/m/Y', strtotime($k)), round($v['ing'], 2), round($v['egr'], 2), round($v['pre'], 2)];
                $ti += $v['ing']; $te += $v['egr']; $tp += $v['pre'];
            }
            if ($filas) {
                $tablas[] = ['titulo' => 'MOVIMIENTO POR DÍA',
                             'cols'   => [['Día', 'texto'], ['Ingresos', 'monto'], ['Egresos', 'monto'], ['Préstamos', 'monto']],
                             'filas'  => $filas, 'pie' => ['TOTAL', round($ti, 2), round($te, 2), round($tp, 2)]];
            }
        }

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
        if ($filas) $tablas[] = $tabla('PASAJES (POR RUTA)', $filas, 'Ruta');

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
        if ($filas) $tablas[] = $tabla('ENCOMIENDAS PAGADAS EN ORIGEN (POR RUTA)', $filas, 'Ruta');

        $otros = [];
        $p = []; $w = $this->filtro('e.id_sucursal_destino', 'en.create_entrega_encomienda', $g, $p);
        $cod = $this->q("SELECT COUNT(*) AS cantidad, IFNULL(SUM(en.monto_entrega_encomienda), 0) AS monto
                         FROM entregas_encomiendas en INNER JOIN encomiendas e ON en.id_encomienda = e.id_encomienda
                         WHERE en.delete_entrega_encomienda IS NULL AND IFNULL(en.monto_entrega_encomienda, 0) > 0 AND $w", $p)[0];
        if ((int)$cod['cantidad'] > 0) $otros[] = $num(['Cobros de encomiendas COD en destino', $cod['cantidad'], $cod['monto']]);
        $otros = array_merge($otros, $mapa($this->movimientos($g, 'ing')));
        if ($otros) $tablas[] = $tabla('OTROS INGRESOS', $otros, 'Concepto');

        $filas = $mapa($this->movimientos($g, 'egr'));
        if ($filas) $tablas[] = $tabla('EGRESOS', $filas, 'Concepto');
        $filas = $mapa($this->movimientos($g, 'pre'));
        if ($filas) $tablas[] = $tabla('PRÉSTAMOS', $filas, 'Concepto');

        $tablas[] = [
            'titulo' => 'RESUMEN DEL PERIODO',
            'cols'   => [['Concepto', 'texto'], ['Monto', 'monto']],
            'filas'  => [
                ['VIENEN (SALDO ANTERIOR)', $c['vienen']],
                ['TOTAL INGRESOS', $c['ing']],
                ['TOTAL INGRESOS (CON VIENEN)', $c['total']],
                ['TOTAL EGRESOS', $c['egr']],
                ['TOTAL PRÉSTAMOS', $c['pre']],
            ],
            'pie'    => ['SALDO FINAL', $c['saldo']],
        ];

        return [
            'titulo'  => $titulo,
            'periodo' => $periodo,
            'kpi'     => ['inicial' => $c['vienen'], 'ing' => $c['ing'], 'egr' => $c['egr'], 'pre' => $c['pre'], 'saldo' => $c['saldo']],
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