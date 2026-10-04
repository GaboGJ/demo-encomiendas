<?php
/**
 * helpers/reportes/ValidarReportes.php
 * Filtros, validación y formato del módulo Reportes (plantillas económicas del sindicato).
 * Tipos: consolidado | diario | semanal | mensual | anual | rango | destino
 *
 *  - consolidado : un mes por fila.
 *  - diario      : UN día específico (param "dia").
 *  - semanal     : UNA semana de un mes (params "mes" + "semana" 1..5).
 *  - mensual     : UN mes específico (param "mes").
 *  - anual       : los 12 meses de un año (param "anio").
 *  - rango       : un día por fila entre "desde" y "hasta".
 *  - destino     : órdenes de ruta de un mes hacia un destino, por sindicato
 *                  (params "mes", "destino", "sind" = ids separados por coma,
 *                   "precios" = "id:monto,id:monto").
 *
 * normalizar() construye $f['buckets'] (periodos [key,label,desde,hasta]).
 * $f['detalle'] = true en diario/semanal/mensual (un solo periodo con desglose).
 * $f['acum_desde'] = fecha desde la que se acumula el saldo "Vienen" (1 de enero) en esos tipos.
 */
class ValidarReportes {

    const MAX_MESES  = 36;
    const MAX_DIAS   = 366;
    const MAX_NOMBRE = 60;
    const MAX_DESTINO = 50;
    const MAX_SINDICATOS = 50;
    const MAX_PRECIO = 99999999.99;

    const TIPOS = [
        'consolidado' => ['titulo' => 'General', 'icono' => 'summarize',
                          'desc' => 'Todos los meses registrados, uno por fila.'],
        'diario'      => ['titulo' => 'Diario', 'icono' => 'today',
                          'desc' => 'Detalle de un día específico.'],
        'semanal'     => ['titulo' => 'Semanal', 'icono' => 'date_range',
                          'desc' => 'Detalle de una semana de un mes.'],
        'mensual'     => ['titulo' => 'Mensual', 'icono' => 'receipt_long',
                          'desc' => 'Detalle de un mes: ingresos por ruta, egresos, préstamos y saldo.'],
        'anual'       => ['titulo' => 'Anual', 'icono' => 'calendar_month',
                          'desc' => 'Los 12 meses de la gestión elegida.'],
        'rango'       => ['titulo' => 'Rango de fechas', 'icono' => 'event_note',
                          'desc' => 'Un día por fila entre dos fechas.'],
        'destino'     => ['titulo' => 'Por destino', 'icono' => 'alt_route',
                          'desc' => 'Ingresos por órdenes de ruta de un destino, separados por sindicato.'],
    ];

    const MESES = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                   'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    public static function nombreMes($n) { return self::MESES[(int)$n] ?? ''; }

    /** '2026-03' => 'Marzo 2026' */
    public static function etiqueta($ym) {
        $d = DateTime::createFromFormat('!Y-m', $ym);
        return $d ? ucfirst(self::nombreMes($d->format('n'))) . ' ' . $d->format('Y') : $ym;
    }

    /** 'YYYY-MM' => DateTime (día 1) o null */
    private static function ym($s) {
        $d = DateTime::createFromFormat('!Y-m', (string)$s);
        return ($d && $d->format('Y-m') === $s) ? $d : null;
    }

    /** 'YYYY-MM-DD' => DateTime o null */
    private static function fecha($s) {
        $d = DateTime::createFromFormat('!Y-m-d', (string)$s);
        return ($d && $d->format('Y-m-d') === $s) ? $d : null;
    }

    /* ---------------- periodos ---------------- */

    private static function bucketsMeses(DateTime $a, DateTime $b) {
        $out = [];
        $cur = clone $a;
        for ($i = 0; $i <= self::MAX_MESES + 1 && $cur <= $b; $i++) {
            $out[] = [
                'key'   => $cur->format('Y-m'),
                'label' => ucfirst(self::nombreMes($cur->format('n'))) . ' ' . $cur->format('Y'),
                'desde' => $cur->format('Y-m-01'),
                'hasta' => $cur->format('Y-m-t'),
            ];
            $cur->modify('+1 month');
        }
        return $out;
    }

    private static function bucketsDias(DateTime $a, DateTime $b) {
        $out = [];
        $cur = clone $a;
        for ($i = 0; $i <= self::MAX_DIAS && $cur <= $b; $i++) {
            $out[] = [
                'key'   => $cur->format('Y-m-d'),
                'label' => $cur->format('d/m/Y'),
                'desde' => $cur->format('Y-m-d'),
                'hasta' => $cur->format('Y-m-d'),
            ];
            $cur->modify('+1 day');
        }
        return $out;
    }

    /** Bloques de 7 días: 1-7, 8-14, 15-21, 22-28, 29-fin. */
    private static function bucketsSemanas(DateTime $mes) {
        $out = [];
        $dim = (int)$mes->format('t');
        $ym  = $mes->format('Y-m');
        for ($s = 1, $n = 1; $s <= $dim; $s += 7, $n++) {
            $e = min($s + 6, $dim);
            $out[] = [
                'key'   => $ym . '-' . sprintf('%02d', $s),
                'label' => 'Semana ' . $n . ' (' . sprintf('%02d', $s) . '/' . $mes->format('m') . ' al ' . sprintf('%02d', $e) . '/' . $mes->format('m') . ')',
                'desde' => $ym . '-' . sprintf('%02d', $s),
                'hasta' => $ym . '-' . sprintf('%02d', $e),
            ];
        }
        return $out;
    }

    /** "id:monto,id:monto" => [id => float|null]  (null = monto inválido) */
    private static function precios($txt) {
        $out = [];
        foreach (explode(',', (string)$txt) as $par) {
            $par = trim($par);
            if ($par === '') continue;
            $x = explode(':', $par, 2);
            $id = intval($x[0]);
            if ($id <= 0) continue;
            $v = str_replace(',', '.', trim($x[1] ?? ''));
            $out[$id] = ($v !== '' && is_numeric($v) && (float)$v >= 0 && (float)$v <= self::MAX_PRECIO) ? round((float)$v, 2) : null;
        }
        return $out;
    }

    /**
     * @param int[]       $idsPermitidos sucursales que el usuario puede consultar
     * @param string|null $primerMes     'YYYY-MM' del primer registro (solo para consolidado)
     */
    public static function normalizar(array $get, array $idsPermitidos, $primerMes = null) {
        $tipo     = trim((string)($get['tipo'] ?? 'consolidado'));
        $suc      = intval($get['sucursal'] ?? 0);
        $saldoTxt = str_replace(',', '.', trim((string)($get['saldo'] ?? '')));

        $sind = array_values(array_unique(array_filter(array_map('intval', explode(',', (string)($get['sind'] ?? ''))))));

        $f = [
            'tipo'       => $tipo,
            'sucursal'   => $suc,
            'sucursales' => $suc > 0 ? (in_array($suc, $idsPermitidos, true) ? [$suc] : []) : $idsPermitidos,
            'saldo'      => $saldoTxt === '' ? 0.0 : (is_numeric($saldoTxt) ? round((float)$saldoTxt, 2) : null),
            'finanzas'   => mb_substr(trim((string)($get['finanzas'] ?? '')), 0, self::MAX_NOMBRE, 'UTF-8'),
            'parada'     => mb_substr(trim((string)($get['parada'] ?? '')), 0, self::MAX_NOMBRE, 'UTF-8'),
            'destino'    => mb_substr(trim((string)($get['destino'] ?? '')), 0, self::MAX_DESTINO, 'UTF-8'),
            'sindicatos' => array_slice($sind, 0, self::MAX_SINDICATOS),
            'precios'    => self::precios($get['precios'] ?? ''),
            'fmt'        => 'm',
            'buckets'    => [],
            'detalle'    => in_array($tipo, ['diario', 'semanal', 'mensual'], true),
            'acum_desde' => '',
            'ini' => '', 'fin' => '', 'anio' => 0,
        ];

        $mes = self::ym(trim((string)($get['mes'] ?? '')));

        switch ($tipo) {
            case 'consolidado':
                $fin  = new DateTime(date('Y-m-01'));
                $ini  = self::ym((string)$primerMes) ?: clone $fin;
                $tope = (clone $fin)->modify('-' . (self::MAX_MESES - 1) . ' months');
                if ($ini < $tope) $ini = $tope;
                $f['buckets'] = self::bucketsMeses($ini, $fin);
                break;

            case 'diario':
                $dia = self::fecha(trim((string)($get['dia'] ?? '')));
                if ($dia) {
                    $f['fmt'] = 'd';
                    $f['buckets'] = self::bucketsDias($dia, $dia);
                    $f['acum_desde'] = $dia->format('Y-01-01');
                }
                break;

            case 'semanal':
                if ($mes) {
                    $n = intval($get['semana'] ?? 0);
                    $semanas = self::bucketsSemanas($mes);
                    if ($n >= 1 && isset($semanas[$n - 1])) {
                        $f['fmt'] = 'd';
                        $f['buckets'] = [$semanas[$n - 1]];
                        $f['acum_desde'] = $mes->format('Y-01-01');
                    }
                }
                break;

            case 'mensual':
                if ($mes) {
                    $f['buckets'] = self::bucketsMeses($mes, $mes);
                    $f['acum_desde'] = $mes->format('Y-01-01');
                }
                break;

            case 'destino':
                if ($mes) {
                    $f['buckets'] = self::bucketsMeses($mes, $mes);
                }
                break;

            case 'anual':
                $anio = intval($get['anio'] ?? 0);
                if ($anio >= 2000 && $anio <= 2100) {
                    $f['anio'] = $anio;
                    $f['buckets'] = self::bucketsMeses(new DateTime("$anio-01-01"), new DateTime("$anio-12-01"));
                }
                break;

            case 'rango':
                $d = self::fecha(trim((string)($get['desde'] ?? '')));
                $h = self::fecha(trim((string)($get['hasta'] ?? '')));
                if ($d && $h && $d <= $h && ($d->diff($h)->days + 1) <= self::MAX_DIAS) {
                    $f['fmt'] = 'd';
                    $f['buckets'] = self::bucketsDias($d, $h);
                }
                break;
        }

        if ($f['buckets']) {
            $f['ini'] = $f['buckets'][0]['desde'];
            $f['fin'] = end($f['buckets'])['hasta'];
        }
        return $f;
    }

    public static function validar(array $f) {
        if (!isset(self::TIPOS[$f['tipo']])) return 'El tipo de reporte no es válido.';

        if (!$f['buckets']) {
            switch ($f['tipo']) {
                case 'diario':  return 'Seleccione el día del informe.';
                case 'semanal': return 'Seleccione el mes y la semana del informe.';
                case 'anual':   return 'Indique un año válido (entre 2000 y 2100).';
                case 'rango':   return 'Indique fecha de inicio y de fin válidas (inicio no mayor al fin, máximo ' . self::MAX_DIAS . ' días).';
                default:        return 'Seleccione el mes del informe.';
            }
        }
        if ($f['saldo'] === null) return 'El saldo inicial no es un número válido.';
        if (!$f['sucursales']) return 'No tiene sucursales disponibles o la sucursal elegida no es válida.';

        if ($f['tipo'] === 'destino') {
            if ($f['destino'] === '') return 'Seleccione el destino del informe.';
            if (!$f['sindicatos']) return 'Seleccione al menos un sindicato.';
            foreach ($f['precios'] as $p) {
                if ($p === null) return 'El monto por orden debe ser un número mayor o igual a 0.';
            }
        }
        return null;
    }

    /** Formato legible (impresión / Excel). */
    public static function formatear($v, $tipo) {
        if ($v === null || $v === '') return '';
        switch ($tipo) {
            case 'monto':  return number_format((float)$v, 2);
            case 'entero': return (string)(int)$v;
            default:       return (string)$v;
        }
    }
}
?>