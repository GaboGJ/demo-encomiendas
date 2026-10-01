<?php
/**
 * helpers/auth/ValidarLogin.php
 * Validación de los datos del formulario de inicio de sesión.
 * Devuelve null si es válido o el mensaje de error.
 */
require_once __DIR__ . '/ValidarPassword.php';

class ValidarLogin {

    public static function validar($ci, $password) {
        if ($ci === '' || $password === '') {
            return 'Ingrese su C.I. y su contraseña.';
        }
        // Mismo mensaje que "contraseña incorrecta": no se revela qué falló
        // (una contraseña de más de 72 bytes nunca pudo registrarse).
        if (!preg_match('/^[0-9A-Z\-]{5,20}$/', $ci) || strlen($password) > ValidarPassword::MAX_BYTES) {
            return 'C.I. o contraseña incorrectos.';
        }
        return null;
    }
}