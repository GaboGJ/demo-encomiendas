<?php
// Iniciar sesión de forma segura si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la Base de Datos
define('SERVIDOR', 'localhost');
define('USUARIO', 'root');
define('PASSWORD', '');
define('BD', 'demo_encomiendas'); // Ajusta el nombre de tu base de datos

$servidor = "mysql:dbname=" . BD . ";host=" . SERVIDOR;

try {
    $pdo = new PDO($servidor, USUARIO, PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
} catch (PDOException $e) {
    echo "Error al conectar a la base de datos: " . $e->getMessage();
}

// URL Base del Sistema (Apunta directamente a la carpeta public)
define('URL', 'http://localhost/demo_encomiendas');

// Zona horaria y fecha actual
date_default_timezone_set("America/La_Paz");
$fechaHora = date('Y-m-d H:i:s');
?>