<?php
/**
 * helpers/rutas/ValidarRutas.php
 * Se permiten varias tarifas por tipo de contenido siempre que sus rangos de peso no se solapen.
 */
class ValidarRutas {

    const MAX_TARIFAS = 50;
    const MAX_MONTO   = 99999999.99;
    const MAX_PESO    = 99999999.99;

    private static function numero($v) {
        $v = str_replace(',', '.', trim((string)$v));
        return ($v !== '' && is_numeric($v)) ? round((float)$v, 2) : null;
    }

    public static function normalizar(array $post) {
        $tarifas = json_decode((string)($post['tarifas_json'] ?? '[]'), true);
        if (!is_array($tarifas)) $tarifas = [];

        $norm = [];
        foreach ($tarifas as $t) {
            if (!is_array($t)) continue;
            $norm[] = [
                'id_contenido' => intval($t['id_contenido'] ?? 0),
                'peso_min'     => self::numero($t['peso_min'] ?? ''),
                'peso_max'     => self::numero($t['peso_max'] ?? ''),
                'precio'       => self::numero($t['precio'] ?? ''),
            ];
        }

        return [
            'id_origen'    => intval($post['id_origen'] ?? 0),
            'id_destino'   => intval($post['id_destino'] ?? 0),
            'id_sindicato' => intval($post['id_sindicato'] ?? 0),
            'precio'       => self::numero($post['precio_pasaje'] ?? ''),
            'tarifas'      => $norm,
        ];
    }

    public static function validar(array $d) {
        if ($d['id_origen'] <= 0 || $d['id_destino'] <= 0) {
            return 'Seleccione la sucursal de destino.';
        }
        if ($d['id_origen'] === $d['id_destino']) {
            return 'El origen y el destino no pueden ser la misma sucursal.';
        }
        if ($d['id_sindicato'] <= 0) {
            return 'Seleccione el sindicato al que pertenece la ruta.';
        }
        if ($d['precio'] === null || $d['precio'] <= 0 || $d['precio'] > self::MAX_MONTO) {
            return 'Indique un precio de pasaje válido (mayor a 0).';
        }
        if (count($d['tarifas']) > self::MAX_TARIFAS) {
            return 'Se admiten como máximo ' . self::MAX_TARIFAS . ' tarifas de encomienda por ruta.';
        }

        foreach ($d['tarifas'] as $i => $t) {
            $n = $i + 1;
            if ($t['id_contenido'] <= 0) {
                return "Tarifa #$n: seleccione el tipo de contenido.";
            }
            if ($t['precio'] === null || $t['precio'] <= 0 || $t['precio'] > self::MAX_MONTO) {
                return "Tarifa #$n: indique un precio válido (mayor a 0).";
            }
            if ($t['peso_max'] === null || $t['peso_max'] <= 0 || $t['peso_max'] > self::MAX_PESO) {
                return "Tarifa #$n: indique un peso máximo válido (mayor a 0).";
            }
            if ($t['peso_min'] !== null && ($t['peso_min'] < 0 || $t['peso_min'] > $t['peso_max'])) {
                return "Tarifa #$n: el peso mínimo no puede ser negativo ni mayor al máximo.";
            }
        }

        // Mismo tipo de contenido: los rangos de peso no pueden solaparse
        $n = count($d['tarifas']);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $a = $d['tarifas'][$i]; $b = $d['tarifas'][$j];
                if ($a['id_contenido'] !== $b['id_contenido']) continue;
                $aMin = $a['peso_min'] ?? 0; $bMin = $b['peso_min'] ?? 0;
                if ($aMin <= $b['peso_max'] && $bMin <= $a['peso_max']) {
                    return 'Las tarifas #' . ($i + 1) . ' y #' . ($j + 1) . ' son del mismo tipo de contenido y sus rangos de peso se solapan.';
                }
            }
        }
        return null;
    }
}
?>