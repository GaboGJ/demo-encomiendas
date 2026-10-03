<?php
/**
 * helpers/roles/ValidarRoles.php
 * Normalización y validación del formulario del módulo Roles.
 * validar() devuelve null si todo es válido o el mensaje de error.
 */
class ValidarRoles {

    const MAX_NOMBRE      = 50;
    const MAX_DESCRIPCION = 200;

    public static function normalizar(array $post) {
        $limpiar = function ($v) {
            return trim(preg_replace('/\s+/u', ' ', (string)$v));
        };

        return [
            'nombre'      => $limpiar($post['nombre_rol'] ?? ''),
            'descripcion' => $limpiar($post['descripcion_rol'] ?? ''),
            'copiar_de'   => intval($post['copiar_de'] ?? 0),
        ];
    }

    public static function validar(array $d) {
        if ($d['nombre'] === '') {
            return 'El nombre del rol es obligatorio.';
        }

        $largo = mb_strlen($d['nombre'], 'UTF-8');
        if ($largo < 3 || $largo > self::MAX_NOMBRE) {
            return 'El nombre del rol debe tener entre 3 y ' . self::MAX_NOMBRE . ' caracteres.';
        }
        if (!preg_match('/^[\p{L}\p{N}][\p{L}\p{N}\s\'.\-\/()&+]*$/u', $d['nombre'])) {
            return 'El nombre solo puede contener letras, números, espacios y los signos . - / ( ) & +';
        }

        if (mb_strlen($d['descripcion'], 'UTF-8') > self::MAX_DESCRIPCION) {
            return 'La descripción no puede superar ' . self::MAX_DESCRIPCION . ' caracteres.';
        }

        return null;
    }

    /** ids_json de la matriz de permisos: lista de id_operacion enteros positivos y únicos. */
    public static function normalizarOperaciones($json) {
        $ids = json_decode((string)$json, true);
        if (!is_array($ids)) return null;
        return array_values(array_unique(array_filter(array_map('intval', $ids), function ($v) { return $v > 0; })));
    }
}
?>