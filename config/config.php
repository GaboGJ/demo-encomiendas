<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Credenciales de Base de Datos
define('SERVIDOR', 'localhost');
define('USUARIO', 'root');
define('PASSWORD', '');
define('BD', 'demo_encomiendas');

$servidor = "mysql:dbname=" . BD . ";host=" . SERVIDOR;

try {
    $pdo = new PDO($servidor, USUARIO, PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
} catch (PDOException $e) {
    echo "Error al conectar a la base de datos";
}

// URLs y Rutas del Sistema
define('URL', 'http://localhost/demo_encomiendas/public'); // Apunta directo a la carpeta pública

// Configuración regional de fechas
date_default_timezone_set("America/La_Paz");
$fechaHora = date('Y-m-d H:i:s');
?>