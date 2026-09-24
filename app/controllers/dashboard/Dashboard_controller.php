<?php
class Dashboard_controller {

    public function index() {
        $viewPath    = __DIR__ . '/../../views/dashboard/';
        
        // Definimos qué menú estará activo
        $menuActivo = 'dashboard';

        require_once $viewPath . 'layouts/header.php';
        require_once $viewPath . 'layouts/sidebar.php'; // Aquí la vista del sidebar heredará la variable $menuActivo
        require_once $viewPath . 'layouts/navbar.php';
        
        if (file_exists($viewPath)) {
             require_once $viewPath . 'start/index.php';
        }

        require_once $viewPath . 'layouts/footer.php';
        require_once $viewPath . 'layouts/script.php';
    }
}
?>