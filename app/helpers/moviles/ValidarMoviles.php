<?php
/**
 * helpers/moviles/ValidarMoviles.php
 * Normalización y validación del formulario del módulo Móviles (nuevo / editar).
 * validar() devuelve null si todo es válido o el mensaje de error.
 */
class ValidarMoviles {

    const MAX = 50;

    public static function normalizar(array $post) {
        $limpiar = function ($v) {
            return trim(preg_replace('/\s+/u', ' ', (string)$v));
        };

        return [
            'id_socio'  => intval($post['id_socio'] ?? 0),
            'id_modelo' => intval($post['id_modelo'] ?? 0),
            'numero'    => $limpiar($post['numero_interno'] ?? ''),
            // La placa se guarda en mayúsculas y sin espacios
            'placa'     => strtoupper(preg_replace('/\s+/', '', (string)($post['placa'] ?? ''))),
            'color'     => $limpiar($post['color'] ?? ''),
        ];
    }

    public static function validar(array $d) {
        if ($d['id_socio'] <= 0 || $d['id_modelo'] <= 0 || $d['numero'] === '') {
            return 'Seleccione el socio titular y el modelo, e indique el número de unidad.';
        }

        if (!preg_match('/^[0-9A-Za-z\-\/\. ]{1,' . self::MAX . '}$/', $d['numero'])) {
            return 'El número de unidad solo puede contener letras, números, espacio, guion, punto y barra (máximo ' . self::MAX . ' caracteres).';
        }

        if ($d['placa'] !== '' && !preg_match('/^[0-9A-Z\-]{3,15}$/', $d['placa'])) {
            return 'La placa solo puede contener letras, números y guion (3 a 15 caracteres).';
        }

        if ($d['color'] !== '') {
            if (mb_strlen($d['color'], 'UTF-8') > self::MAX || !preg_match('/^[\p{L}][\p{L}\s\'.\-\/]*$/u', $d['color'])) {
                return 'El color solo puede contener letras y espacios (máximo ' . self::MAX . ' caracteres).';
            }
        }

        return null;
    }
}
?>