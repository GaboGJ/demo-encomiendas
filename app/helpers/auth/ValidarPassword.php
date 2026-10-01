<?php
/**
 * helpers/auth/ValidarPassword.php
 * Reglas de contraseña para el registro. Devuelve null si es válida o el mensaje de error.
 */
class ValidarPassword {

    const MIN = 6;
    const MAX_BYTES = 72; // bcrypt ignora todo lo que pase de 72 bytes

    public static function validar($password, $confirmacion) {
        if (mb_strlen($password, 'UTF-8') < self::MIN) {
            return 'La contraseña debe tener al menos ' . self::MIN . ' caracteres.';
        }
        if (strlen($password) > self::MAX_BYTES) {
            return 'La contraseña no puede superar ' . self::MAX_BYTES . ' caracteres.';
        }
        if ($password !== $confirmacion) {
            return 'Las contraseñas no coinciden.';
        }
        return null;
    }
}