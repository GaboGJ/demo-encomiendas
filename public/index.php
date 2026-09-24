<?php
// 1. Cargar la configuración global (conexión BD, sesiones, constantes)
require_once __DIR__ . '/../config/config.php';

// 2. Obtener y limpiar la URL solicitada
$url = isset($_GET['url']) ? $_GET['url'] : 'auth/auth_controller/index';
$url = rtrim($url, '/');
$url = explode('/', $url);

// --- REGLAS DE ENRUTAMIENTO INTELIGENTE PARA URLs CORTAS ---
if (count($url) == 1 && !empty($url[0])) {
    // Si escribes solo 'dashboard', asumimos carpeta 'dashboard', controlador 'dashboard_controller' y acción 'index'
    $folder     = $url[0];
    $controller = $url[0] . '_controller'; 
    $action     = 'index';
} elseif (count($url) == 2) {
    // Si escribes 'dashboard/perfil', asumimos carpeta 'dashboard', controlador 'perfil_controller' y acción 'index'
    $folder     = $url[0];
    $controller = $url[1] . '_controller';
    $action     = 'index';
} else {
    // Estructura completa clásica: /carpeta/controlador/accion
    $folder     = isset($url[0]) ? $url[0] : 'auth';                 
    $controller = isset($url[1]) ? $url[1] : 'auth_controller';      
    $action     = isset($url[2]) ? $url[2] : 'index';                
}

// Formatear el nombre de la clase (ej: dashboard_controller -> Dashboard_controller)
// Nota: Si usas guiones bajos, asegurate de capitalizar correctamente si tu clase lo requiere
$controllerClassName = str_replace(' ', '', $controller);

// 3. Ruta física del archivo usando __DIR__ para el servidor 
$archivoControlador = __DIR__ . '/../app/controllers/' . $folder . '/' . $controllerClassName . '.php';

// 4. Verificar si el archivo del controlador existe
if (file_exists($archivoControlador)) {
    require_once $archivoControlador;
    
    // Validar si la clase existe dentro del archivo
    if (class_exists($controllerClassName)) {
        $controllerInstance = new $controllerClassName();
        
        // Verificar si el método (acción) existe en la clase
        if (method_exists($controllerInstance, $action)) {
            $controllerInstance->$action();
        } else {
            echo "<h1>Error 404</h1><p>La acción '{$action}' solicitada no existe.</p>";
        }
    } else {
        echo "<h1>Error 404</h1><p>La clase del controlador '{$controllerClassName}' no está definida correctamente.</p>";
    }
} else {
    // Si no encuentra el controlador, muestra un mensaje de error limpio
    http_response_code(404);
    echo "<h1>Error 404</h1><p>El controlador '{$folder}/{$controller}.php' no fue encontrado en el sistema.</p>";
}
?>