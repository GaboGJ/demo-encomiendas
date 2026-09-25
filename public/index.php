<?php
// 1. Cargar la configuración global (conexión BD, sesiones, constantes)
require_once __DIR__ . '/../config/config.php';

// 2. Obtener y limpiar la URL solicitada
$url = isset($_GET['url']) ? $_GET['url'] : 'auth/auth_controller/index';

// ELIMINAR 'index.php' O 'index.php/' DEL INICIO DE LA URL SI EXISTE
$url = preg_replace('/^index\.php\/?/', '', $url);
$url = rtrim($url, '/');

// Si la URL quedó vacía tras limpiar index.php, asignamos la ruta por defecto
if (empty($url)) {
    $url = 'auth/auth_controller/index';
}

$url = explode('/', $url);

// --- REGLAS DE ENRUTAMIENTO INTELIGENTE ---
if (count($url) == 1 && !empty($url[0])) {
    // Si escribes 'dashboard', busca carpeta 'dashboard', controlador 'dashboard_controller' y acción 'index'
    $folder     = $url[0];
    $controller = $url[0] . '_controller'; 
    $action     = 'index';
} elseif (count($url) == 2) {
    // Caso A: 'encomiendas/new' -> carpeta 'encomiendas', controlador 'encomiendas_controller', acción 'new'
    // Caso B: 'dashboard/perfil' -> carpeta 'dashboard', controlador 'perfil_controller', acción 'index'
    $folder     = $url[0];
    
    // Si el segundo parámetro coincide con el nombre de un archivo controlador habitual (ej: encomiendas_controller)
    if (strpos($url[1], '_controller') !== false) {
        $controller = $url[1];
        $action     = 'index';
    } else {
        // Estructura corta: /carpeta/accion (ej: /encomiendas/new)
        $controller = $url[0] . '_controller';
        $action     = $url[1];
    }
} else {
    // Estructura completa clásica: /carpeta/controlador/accion
    $folder     = isset($url[0]) ? $url[0] : 'auth';                 
    $controller = isset($url[1]) ? $url[1] : 'auth_controller';      
    $action     = isset($url[2]) ? $url[2] : 'index';                
}

// Formatear el nombre de la clase
$controllerClassName = str_replace(' ', '', $controller);

// 3. Ruta física del archivo usando __DIR__ para el servidor 
$archivoControlador = __DIR__ . '/../app/controllers/' . $folder . '/' . $controllerClassName . '.php';

// 4. Verificar si el archivo del controlador existe
if (file_exists($archivoControlador)) {
    require_once $archivoControlador;
    
    if (class_exists($controllerClassName)) {
        $controllerInstance = new $controllerClassName();
        
        if (method_exists($controllerInstance, $action)) {
            $controllerInstance->$action();
        } else {
            http_response_code(404);
            echo "<h1>Error 404</h1><p>La acción '{$action}' solicitada no existe en el controlador '{$controllerClassName}'.</p>";
        }
    } else {
        http_response_code(404);
        echo "<h1>Error 404</h1><p>La clase del controlador '{$controllerClassName}' no está definida correctamente.</p>";
    }
} else {
    http_response_code(404);
    echo "<h1>Error 404</h1><p>El controlador '{$folder}/{$controllerClassName}.php' no fue encontrado en el sistema.</p>";
}
?>