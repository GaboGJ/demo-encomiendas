<?php
require_once __DIR__ . '/../../models/cajas/Cajas_model.php';

/**
 * CajaGuardada::requerir('pasajes');
 * Si el usuario NO tiene caja abierta, muestra la vista sin_caja y corta la ejecución.
 */
class CajaGuardada {
    public static function requerir($menuActivo, $mensaje = null) {
        $idUsuario = (int)($_SESSION['id_usuario'] ?? 0);
        if ((new Cajas_model())->idHistorialAbiertoDeUsuario($idUsuario) > 0) return;

        $mensaje  = $mensaje ?: 'Para realizar esta operación primero debe aperturar una caja a su nombre.';
        $viewPath = __DIR__ . '/../../views/dashboard/';

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';
        require $viewPath . 'cajas/sin_caja.php';
        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
        exit;
    }
}