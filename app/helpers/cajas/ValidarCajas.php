<?php
/**
 * helpers/cajas/ValidarCajas.php
 * Normalización y validación del módulo Cajas (formulario + montos de apertura/cierre).
 * validar() devuelve null si todo es válido o el mensaje de error.
 */
class ValidarCajas {

    const MAX_NOMBRE = 50;
    const MAX_MONTO  = 99999999.99;

    public static function normalizar(array $post) {
        return [
            'id_sucursal' => intval($post['id_sucursal'] ?? 0),
            'nombre'      => trim(preg_replace('/\s+/u', ' ', (string)($post['nombre_caja'] ?? ''))),
        ];
    }

    public static function validar(array $d) {
        if ($d['id_sucursal'] <= 0) {
            return 'Seleccione la sucursal de la caja.';
        }
        if ($d['nombre'] === '') {
            return 'El nombre de la caja es obligatorio.';
        }
        $largo = mb_strlen($d['nombre'], 'UTF-8');
        if ($largo < 2 || $largo > self::MAX_NOMBRE) {
            return 'El nombre de la caja debe tener entre 2 y ' . self::MAX_NOMBRE . ' caracteres.';
        }
        if (!preg_match('/^[\p{L}\p{N}][\p{L}\p{N}\s\'.\-\/()#+]*$/u', $d['nombre'])) {
            return 'El nombre solo puede contener letras, números, espacios y los signos . - / ( ) # +';
        }
        return null;
    }

    /** Monto >= 0 con 2 decimales, o null si no es válido. */
    public static function monto($v) {
        $v = str_replace(',', '.', trim((string)$v));
        if ($v === '' || !is_numeric($v)) return null;
        $n = round((float)$v, 2);
        return ($n >= 0 && $n <= self::MAX_MONTO) ? $n : null;
    }
}
?>
