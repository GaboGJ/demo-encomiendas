<?php
/**
 * helpers/modelos/ValidarModelos.php
 * Normalización y validación del módulo Modelos de Vehículos:
 *  - formulario (nombre del modelo)
 *  - configuración del plano (pisos + elementos) que envía el editor de asientos.
 * Los validar*() devuelven null / ['error' => null, ...] si todo es válido.
 */
class ValidarModelos {

    const MAX_NOMBRE   = 50;
    const MAX_PISOS    = 3;
    const MAX_FILAS    = 10;
    const MAX_COLUMNAS = 20;
    const MAX_DATO     = 50;

    /* ---------------- Formulario ---------------- */

    public static function normalizar(array $post) {
        return [
            'nombre' => trim(preg_replace('/\s+/u', ' ', (string)($post['nombre_modelo'] ?? ''))),
        ];
    }

    public static function validar(array $d) {
        if ($d['nombre'] === '') {
            return 'El nombre del modelo es obligatorio.';
        }
        $largo = mb_strlen($d['nombre'], 'UTF-8');
        if ($largo < 2 || $largo > self::MAX_NOMBRE) {
            return 'El nombre del modelo debe tener entre 2 y ' . self::MAX_NOMBRE . ' caracteres.';
        }
        if (!preg_match('/^[\p{L}\p{N}][\p{L}\p{N}\s\'.\-\/()+]*$/u', $d['nombre'])) {
            return 'El nombre solo puede contener letras, números, espacios y los signos . - / ( ) +';
        }
        return null;
    }

    /* ---------------- Plano ---------------- */

    /** Misma regla que Elementos_model: "asiento" sí, "Asiento Chofer" no. */
    public static function esTipoAsiento($nombre) {
        $n = mb_strtolower(trim((string)$nombre), 'UTF-8');
        return strpos($n, 'asiento') !== false && strpos($n, 'chofer') === false;
    }

    /**
     * @param mixed $pisos  JSON decodificado del editor
     * @param array $tipos  [id_tipo_elemento => nombre_tipo_elemento] vigentes
     * @return array ['error' => string|null, 'pisos' => [...], 'total' => int]
     */
    public static function validarConfiguracion($pisos, array $tipos) {
        $fallo = function ($msg) { return ['error' => $msg, 'pisos' => [], 'total' => 0]; };

        if (!is_array($pisos) || !$pisos) {
            return $fallo('Debe configurar al menos un piso.');
        }
        if (count($pisos) > self::MAX_PISOS) {
            return $fallo('Un modelo admite como máximo ' . self::MAX_PISOS . ' pisos.');
        }

        $numeros = [];
        $etiquetas = [];
        $total = 0;
        $salida = [];

        foreach ($pisos as $p) {
            $num = intval($p['numero'] ?? 0);
            if ($num < 1 || $num > self::MAX_PISOS) return $fallo('Número de piso inválido.');
            if (isset($numeros[$num])) return $fallo('Hay dos pisos con el mismo número.');
            $numeros[$num] = true;

            $nombre = trim(preg_replace('/\s+/u', ' ', (string)($p['nombre_piso'] ?? '')));
            if (mb_strlen($nombre, 'UTF-8') > self::MAX_NOMBRE) {
                return $fallo('El nombre del piso no puede superar ' . self::MAX_NOMBRE . ' caracteres.');
            }

            $filas = intval($p['filas'] ?? 0);
            $cols  = intval($p['columnas'] ?? 0);
            if ($filas < 1 || $filas > self::MAX_FILAS || $cols < 1 || $cols > self::MAX_COLUMNAS) {
                return $fallo('Las dimensiones del piso ' . $num . ' deben ser de 1 a ' . self::MAX_FILAS . ' filas y de 1 a ' . self::MAX_COLUMNAS . ' columnas.');
            }

            // Base de coordenadas con la que el piso ya estaba guardado (0 o 1): se conserva
            $bf = max(0, min(100, intval($p['base_fila'] ?? 0)));
            $bc = max(0, min(100, intval($p['base_columna'] ?? 0)));

            $posiciones = [];
            $elementos  = [];
            foreach (($p['elementos'] ?? []) as $e) {
                $f   = intval($e['fila'] ?? -1);
                $c   = intval($e['columna'] ?? -1);
                $idt = intval($e['id_tipo'] ?? 0);
                $dato = trim((string)($e['dato'] ?? ''));

                if ($f < 0 || $f >= $filas || $c < 0 || $c >= $cols) {
                    return $fallo('Hay un elemento fuera de la grilla del piso ' . $num . '.');
                }
                if (!isset($tipos[$idt])) {
                    return $fallo('Hay un elemento con un tipo no válido.');
                }
                $k = $f . ':' . $c;
                if (isset($posiciones[$k])) {
                    return $fallo('Hay dos elementos en la misma celda del piso ' . $num . '.');
                }
                $posiciones[$k] = true;

                if ($dato === '' || mb_strlen($dato, 'UTF-8') > self::MAX_DATO) {
                    return $fallo('Todos los elementos necesitan una etiqueta (máximo ' . self::MAX_DATO . ' caracteres).');
                }

                if (self::esTipoAsiento($tipos[$idt])) {
                    $clave = mb_strtolower($dato, 'UTF-8');
                    if (isset($etiquetas[$clave])) {
                        return $fallo('El número de asiento "' . $dato . '" está repetido.');
                    }
                    $etiquetas[$clave] = true;
                    $total++;
                }

                $elementos[] = ['fila' => $f + $bf, 'columna' => $c + $bc, 'id_tipo' => $idt, 'dato' => $dato];
            }

            $salida[] = [
                'numero'    => $num,
                'nombre'    => $nombre !== '' ? $nombre : null,
                'filas'     => $filas,
                'columnas'  => $cols,
                'elementos' => $elementos,
            ];
        }

        if ($total < 1) {
            return $fallo('El modelo debe tener al menos un asiento.');
        }

        return ['error' => null, 'pisos' => $salida, 'total' => $total];
    }
}
?>