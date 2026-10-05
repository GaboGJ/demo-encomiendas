<?php
require_once __DIR__ . '/../../models/personas/Personas_model.php';
require_once __DIR__ . '/../../helpers/auth/ValidarPersona.php';

class Personas_controller {
    public function buscarPorCiJson() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['id_usuario'])) { echo json_encode(['success' => false]); exit; }

        $ci = trim((string)($_GET['ci'] ?? ''));
        $model = new Personas_model();
        $p = $ci !== '' ? ($model->buscarPorCi($ci) ?: $model->buscarPorCi(ValidarPersona::normalizarCi($ci))) : null;

        if (!$p) { echo json_encode(['success' => false]); exit; }

        echo json_encode(['success' => true, 'persona' => $p + [
            'nombres_persona' => $p['nombre_persona'],
            'paterno_persona' => $p['apellido_paterno_persona'],
            'materno_persona' => $p['apellido_materno_persona'],
            'celular_persona' => $p['telefono_persona'],
        ]], JSON_UNESCAPED_UNICODE);
        exit;
    }
}