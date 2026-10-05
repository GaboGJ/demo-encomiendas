<?php
require_once __DIR__ . '/../../models/reportes/Reportes_model.php';
require_once __DIR__ . '/../../helpers/reportes/ValidarReportes.php';

class Dashboard_controller {

    public function index() {
        $viewPath   = __DIR__ . '/../../views/dashboard/';
        $menuActivo = 'dashboard';

        $model = new Reportes_model();
        $admin = mb_strtolower((string)($_SESSION['nombre_rol'] ?? ''), 'UTF-8') === Permisos::PROTEGIDO;
        $suc   = $model->getSucursalesPermitidas((int)($_SESSION['id_sindicato'] ?? 0), $admin ? 0 : (int)($_SESSION['id_sucursal'] ?? 0));
        $dash  = $model->dashboard(array_column($suc, 'id_sucursal'), (int)($_SESSION['id_sindicato'] ?? 0));

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';
        require_once $viewPath . 'start/index.php';
        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }
}
?>