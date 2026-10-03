<?php
// Formulario "Editar móvil": reutiliza form.php con los datos actuales ($movil viene del controlador).
$modo = 'editar';
$mov  = $movil;
require __DIR__ . '/form.php';