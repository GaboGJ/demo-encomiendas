<?php
// Formulario "Editar móvil": reutiliza form.php con los datos actuales ($modelo viene del controlador).
$modo = 'editar';
$mod  = $modelo;
require __DIR__ . '/form.php';