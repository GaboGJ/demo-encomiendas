<?php
require_once __DIR__ . '/../choferes/ValidarChoferes.php';

class ValidarSocios {

    public static function normalizar(array $post) {
        $d = ValidarChoferes::normalizar($post);
        $d['codigo']           = strtoupper(preg_replace('/\s+/', '', (string)($post['codigo_socio'] ?? '')));
        $d['fecha_afiliacion'] = trim((string)($post['fecha_afiliacion'] ?? ''));
        return $d;
    }

    public static function validar(array $d) {
        $err = ValidarChoferes::validar($d); // persona + licencia (el socio también es chofer)
        if ($err !== null) return $err;

        if ($d['codigo'] !== '' && !preg_match('/^[0-9A-Z\-\/\.]{3,50}$/', $d['codigo'])) {
            return 'El código de socio solo puede contener letras, números, guion, punto y barra (3 a 50 caracteres).';
        }
        if ($d['fecha_afiliacion'] !== '') {
            $f = DateTime::createFromFormat('Y-m-d', $d['fecha_afiliacion']);
            if (!$f || $f->format('Y-m-d') !== $d['fecha_afiliacion']) {
                return 'La fecha de afiliación no es válida.';
            }
        }
        return null;
    }
}
?>