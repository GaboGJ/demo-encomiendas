<?php
/**
 * helpers/sindicatos/ValidarSindicatos.php
 * Normalización y validación del formulario del módulo Sindicatos (nuevo / editar).
 * Reutiliza las reglas del registro (helpers/auth): largo del nombre, formato del NIT,
 * teléfono y largo de la dirección. validar() devuelve null si todo es válido o el mensaje de error.
 */
require_once __DIR__ . '/../auth/ValidarSindicato.php'; // incluye también ValidarPersona

class ValidarSindicatos {

    const MAX_SIGLA = 20;

    /** Limpia espacios repetidos; la sigla se guarda en mayúsculas. */
    public static function normalizar(array $post) {
        $limpiar = function ($v) {
            return trim(preg_replace('/\s+/u', ' ', (string)$v));
        };

        return [
            'nombre'    => $limpiar($post['nombre_sindicato'] ?? ''),
            'sigla'     => mb_strtoupper($limpiar($post['sigla'] ?? ''), 'UTF-8'),
            'nit'       => trim((string)($post['nit'] ?? '')),
            'telefono'  => $limpiar($post['telefono_sindicato'] ?? ''),
            'direccion' => $limpiar($post['direccion'] ?? ''),
        ];
    }

    public static function validar(array $d) {
        if ($d['nombre'] === '' || $d['nit'] === '' || $d['telefono'] === '' || $d['direccion'] === '') {
            return 'Complete los campos obligatorios (nombre, NIT, teléfono y dirección).';
        }

        $largo = mb_strlen($d['nombre'], 'UTF-8');
        if ($largo < 3 || $largo > ValidarSindicato::MAX) {
            return 'El nombre del sindicato debe tener entre 3 y ' . ValidarSindicato::MAX . ' caracteres.';
        }

        if ($d['sigla'] !== '' && !preg_match('/^[\p{L}\p{N}.\-]{2,' . self::MAX_SIGLA . '}$/u', $d['sigla'])) {
            return 'La sigla solo puede contener letras, números, punto y guion (2 a ' . self::MAX_SIGLA . ' caracteres, sin espacios).';
        }

        if (!preg_match('/^[0-9A-Za-z\-\/\.]{5,50}$/', $d['nit'])) {
            return 'El NIT / registro legal solo puede contener letras, números, guion, punto y barra (5 a 50 caracteres, sin espacios).';
        }

        $errTel = ValidarPersona::validarTelefono($d['telefono'], 'El teléfono');
        if ($errTel !== null) return $errTel;

        if (mb_strlen($d['direccion'], 'UTF-8') > ValidarSindicato::MAX_DIRECCION) {
            return 'La dirección no puede superar ' . ValidarSindicato::MAX_DIRECCION . ' caracteres.';
        }

        return null;
    }
}
?>