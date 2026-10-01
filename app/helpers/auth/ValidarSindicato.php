<?php
/**
 * helpers/auth/ValidarSindicato.php
 * Validaciones de los datos de la institución (paso 1 del registro de sindicato).
 * Devuelve null si todo es válido o el mensaje de error.
 */
require_once __DIR__ . '/ValidarPersona.php';

class ValidarSindicato {

    const MAX = 50;
    const MAX_DIRECCION = 200;

    public static function validar($nombre, $nit, $telefono, $ciudad, $direccion) {
        if ($nombre === '' || $nit === '' || $telefono === '' || $ciudad === '' || $direccion === '') {
            return 'Complete los datos de la institución (nombre, NIT, teléfono, ciudad y dirección).';
        }

        $largo = mb_strlen($nombre, 'UTF-8');
        if ($largo < 3 || $largo > self::MAX) {
            return 'El nombre del sindicato debe tener entre 3 y ' . self::MAX . ' caracteres.';
        }

        if (!preg_match('/^[0-9A-Za-z\-\/\.]{5,50}$/', $nit)) {
            return 'El NIT / registro legal solo puede contener letras, números, guion, punto y barra (5 a 50 caracteres, sin espacios).';
        }

        $errTel = ValidarPersona::validarTelefono($telefono, 'El teléfono central');
        if ($errTel !== null) return $errTel;

        if (mb_strlen($ciudad, 'UTF-8') > self::MAX || !preg_match('/^[\p{L}][\p{L}\s\'.\-]*$/u', $ciudad)) {
            return 'La ciudad solo puede contener letras y espacios (máximo ' . self::MAX . ' caracteres).';
        }

        if (mb_strlen($direccion, 'UTF-8') > self::MAX_DIRECCION) {
            return 'La dirección no puede superar ' . self::MAX_DIRECCION . ' caracteres.';
        }

        return null;
    }
}