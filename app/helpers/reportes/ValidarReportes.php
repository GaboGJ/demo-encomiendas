<?php
/**
 * helpers/reportes/ValidarReportes.php
 * Filtros, validación y formato del módulo Reportes (plantillas económicas del sindicato).
 *  - resumen : una fila por mes (Ingresos, Vienen, Total, Egresos, Préstamos, Saldo)
 *  - detalle : informe de UN mes (ingresos por ruta, egresos, préstamos y resumen)
 */
class ValidarReportes {

    const MAX_MESES = 24;
    const MAX_NOMBRE = 60;

    const TIPOS = [
        'resumen' => ['titulo' => 'Resumen Económico Mensual', 'icono' => 'table_chart',
                      'desc' => 'Una fila por mes: ingresos, vienen, egresos, préstamos y saldo.'],
        'detalle' => ['titulo' => 'Informe Económico del Mes', 'icono' => 'receipt_long',
                      'desc' => 'Detalle de un mes: ingresos por ruta, egresos, préstamos y saldo.'],
    ];

    const MESES = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                   'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    public static function nombreMes($n) { return self::MESES[(int)$n] ?? ''; }

    /** '2026-03' => 'Marzo 2026' */
    public static function etiqueta($ym) {
        $d = DateTime::createFromFormat('!Y-m', $ym);
        return $d ? ucfirst(self::nombreMes($d->format('n'))) . ' ' . $d->format('Y') : $ym;
    }

    private static function ym($s) {
        $d = DateTime::createFromFormat('!Y-m', (string)$s);
        return ($d && $d->format('Y-m') === $s) ? $d : null;
    }

    /** @param int[] $idsPermitidos sucursales que el usuario puede consultar */
    public static function normalizar(array $get, array $idsPermitidos) {
        $tipo  = trim((string)($get['tipo'] ?? 'resumen'));
        $suc   = intval($get['sucursal'] ?? 0);
        $saldoTxt = str_replace(',', '.', trim((string)($get['saldo'] ?? '')));
        $f = [
            'tipo'       => $tipo,
            'sucursal'   => $suc,
            'sucursales' => $suc > 0 ? (in_array($suc, $idsPermitidos, true) ? [$suc] : []) : $idsPermitidos,
            'saldo'      => $saldoTxt === '' ? 0.0 : (is_numeric($saldoTxt) ? round((float)$saldoTxt, 2) : null),
            'finanzas'   => mb_substr(trim((string)($get['finanzas'] ?? '')), 0, self::MAX_NOMBRE, 'UTF-8'),
            'parada'     => mb_substr(trim((string)($get['parada'] ?? '')), 0, self::MAX_NOMBRE, 'UTF-8'),
            'meses' => [], 'ini' => '', 'fin' => '', 'mes_ini' => '', 'mes_fin' => '',
        ];

        $base = $ultimo = null;
        if ($tipo === 'detalle') {
            $m = self::ym(trim((string)($get['mes'] ?? '')));
            if ($m) {
                $base = new DateTime($m->format('Y-01-01'));   // la cuenta arranca en enero del año
                $ultimo = $m;
                $f['mes_ini'] = $m->format('Y-m-01');
                $f['mes_fin'] = $m->format('Y-m-t');
            }
        } else {
            $d = self::ym(trim((string)($get['desde'] ?? '')));
            $h = self::ym(trim((string)($get['hasta'] ?? '')));
            if ($d && $h && $d <= $h) { $base = $d; $ultimo = $h; }
        }

        if ($base && $ultimo) {
            $f['ini'] = $base->format('Y-m-01');
            $f['fin'] = $ultimo->format('Y-m-t');
            $cur = clone $base;
            for ($i = 0; $i <= self::MAX_MESES && $cur->format('Y-m') <= $ultimo->format('Y-m'); $i++) {
                $f['meses'][] = $cur->format('Y-m');
                $cur->modify('+1 month');
            }
        }
        return $f;
    }

    public static function validar(array $f) {
        if (!isset(self::TIPOS[$f['tipo']])) return 'El tipo de reporte no es válido.';
        if (!$f['meses']) {
            return $f['tipo'] === 'detalle' ? 'Seleccione el mes del informe.'
                                            : 'Indique un mes de inicio y uno de fin válidos (inicio no mayor al fin).';
        }
        if (count($f['meses']) > self::MAX_MESES) return 'El rango máximo es de ' . self::MAX_MESES . ' meses.';
        if ($f['saldo'] === null) return 'El saldo inicial no es un número válido.';
        if (!$f['sucursales']) return 'No tiene sucursales disponibles o la sucursal elegida no es válida.';
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