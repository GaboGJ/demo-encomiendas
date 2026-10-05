<?php
require_once __DIR__ . '/../../models/encomiendas/Encomiendas_model.php';

class Rastreo_controller {
    public function index() {
        $v = __DIR__ . '/../../views/web/';
        require_once $v . 'layouts/header.php';
        require_once $v . 'layouts/navbar.php';
        require_once $v . 'rastreo/index.php';
        require_once $v . 'layouts/footer.php';
        require_once $v . 'layouts/script.php';
    }

    public function buscar() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        $g = trim((string)($_GET['guia'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9\-]{3,50}$/', $g)) {
            echo json_encode(['success' => false, 'message' => 'Ingrese un número de guía válido (Ej. ENC-000123).']); exit;
        }
        $r = (new Encomiendas_model())->rastrear($g);
        if (!$r) { echo json_encode(['success' => false, 'message' => 'No encontramos una guía con ese número.']); exit; }
        $r['despachado'] = !empty($r['hora_salida_turno']);
        echo json_encode(['success' => true, 'data' => $r], JSON_UNESCAPED_UNICODE); exit;
    }
}