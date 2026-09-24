<?php
require_once __DIR__ . '/../../models/auth/Auth_model.php';

class Auth_controller {

private $authModel;

    public function __construct() {
        $this->authModel = new Auth_model();
    }

    public function index() {
        // Rutas relativas desde la perspectiva de la estructura app/views/web/
        $viewsPath = __DIR__ . '/../../views/web/';

        // 1. Cargar el Header general de la web
        require_once $viewsPath . 'layouts/header.php';
        
        // 2. Cargar el Navbar de navegación superior
        require_once $viewsPath . 'layouts/navbar.php';
        
        // 3. Cargar el contenido específico del Auth (Login y Registro)
        require_once $viewsPath . 'auth/index.php';
        
        // 4. Cargar el Footer de la web
        require_once $viewsPath . 'layouts/footer.php';
        
        // 5. Cargar los scripts globales finales
        require_once $viewsPath . 'layouts/script.php'; // (Asegúrate de tener este archivo en tus layouts web si agrupas los scripts ahí)
    }
}
?>