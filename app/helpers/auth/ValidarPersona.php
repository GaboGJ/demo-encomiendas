<?php
/**
 * helpers/auth/ValidarPersona.php
 * Validaciones de datos personales (C.I., nombres, teléfono).
 * Todos los métodos devuelven null si el dato es válido, o un string con el mensaje de error.
 */
class ValidarPersona {

    const MAX = 50;

    /** C.I. sin espacios y en mayúsculas (así se guarda y así se busca en el login). */
    public static function normalizarCi($ci) {
        return strtoupper(preg_replace('/\s+/', '', (string)$ci));
    }

    public static function validar($ci, $nombres, $paterno, $materno, $telefono) {
        if ($ci === '' || $nombres === '' || $paterno === '' || $telefono === '') {
            return 'C.I., nombres, apellido paterno y celular son obligatorios.';
        }

        if (!preg_match('/^[0-9A-Z\-]{5,20}$/', $ci)) {
            return 'El C.I. solo puede contener números, letras y guion (entre 5 y 20 caracteres).';
        }

        $campos = [
            'Los nombres'         => $nombres,
            'El apellido paterno' => $paterno,
            'El apellido materno' => $materno,
        ];
        foreach ($campos as $etiqueta => $valor) {
            if ($valor === '') continue; // el materno es opcional
            if (mb_strlen($valor, 'UTF-8') > self::MAX) {
                return $etiqueta . ' no puede superar ' . self::MAX . ' caracteres.';
            }
            if (!preg_match('/^[\p{L}][\p{L}\s\'.\-]*$/u', $valor)) {
                return $etiqueta . ' solo puede contener letras y espacios.';
            }
        }

        return self::validarTelefono($telefono, 'El celular');
    }

    public static function validarTelefono($telefono, $etiqueta = 'El teléfono') {
        if (mb_strlen($telefono, 'UTF-8') > self::MAX || !preg_match('/^\+?[0-9\s\-]+$/', $telefono)) {
            return $etiqueta . ' solo puede contener números, espacios y guiones.';
        }
        $digitos = strlen(preg_replace('/\D/', '', $telefono));
        if ($digitos < 6 || $digitos > 15) {
            return $etiqueta . ' debe tener entre 6 y 15 dígitos.';
        }
        return null;
    }
}