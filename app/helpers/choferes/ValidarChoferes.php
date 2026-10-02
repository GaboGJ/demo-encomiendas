<?php
/**
 * helpers/choferes/ValidarChoferes.php
 * Normalización y validación del formulario del módulo Choferes (nuevo / editar).
 * Reutiliza ValidarPersona (C.I., nombres, celular). validar() devuelve null si todo es válido
 * o el mensaje de error.
 */
require_once __DIR__ . '/../auth/ValidarPersona.php';

class ValidarChoferes {

    const CATEGORIAS = ['A', 'B', 'C', 'P', 'M', 'T'];
    const MAX_DIRECCION = 200;

    public static function normalizar(array $post) {
        $limpiar = function ($v) {
            return trim(preg_replace('/\s+/u', ' ', (string)$v));
        };

        return [
            'ci'          => ValidarPersona::normalizarCi($post['ci'] ?? ''),
            'nombres'     => $limpiar($post['nombres'] ?? ''),
            'paterno'     => $limpiar($post['paterno'] ?? ''),
            'materno'     => $limpiar($post['materno'] ?? ''),
            'celular'     => $limpiar($post['celular'] ?? ''),
            'direccion'   => $limpiar($post['direccion'] ?? ''),
            'licencia'    => strtoupper(preg_replace('/\s+/', '', (string)($post['licencia'] ?? ''))),
            'categoria'   => strtoupper(trim((string)($post['categoria'] ?? ''))),
            'vencimiento' => trim((string)($post['vencimiento'] ?? '')),
        ];
    }

    public static function validar(array $d) {
        $err = ValidarPersona::validar($d['ci'], $d['nombres'], $d['paterno'], $d['materno'], $d['celular']);
        if ($err !== null) return $err;

        if (mb_strlen($d['direccion'], 'UTF-8') > self::MAX_DIRECCION) {
            return 'La dirección no puede superar ' . self::MAX_DIRECCION . ' caracteres.';
        }

        if ($d['licencia'] === '') {
            return 'El número de licencia es obligatorio.';
        }
        if (!preg_match('/^[0-9A-Z\-\/\.]{4,50}$/', $d['licencia'])) {
            return 'La licencia solo puede contener letras, números, guion, punto y barra (4 a 50 caracteres, sin espacios).';
        }

        if ($d['categoria'] !== '' && !in_array($d['categoria'], self::CATEGORIAS, true)) {
            return 'La categoría de licencia no es válida.';
        }

        if ($d['vencimiento'] !== '') {
            $f = DateTime::createFromFormat('Y-m-d', $d['vencimiento']);
            if (!$f || $f->format('Y-m-d') !== $d['vencimiento']) {
                return 'La fecha de vencimiento de la licencia no es válida.';
            }
        }

        return null;
    }
}
?>