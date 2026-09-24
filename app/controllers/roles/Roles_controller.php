<?php
class Roles_controller {

    public function index() {
        $viewPath    = __DIR__ . '/../../views/dashboard/';
        
        // Definimos que aquí el activo es 'roles'
        $menuActivo = 'roles';

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php';
        require_once $viewPath . 'layouts/navbar.php';
        
        if (file_exists($viewPath)) {
             require_once $viewPath . 'roles/index.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }
}
?>