<?php
// Formulario "Editar rol": reutiliza form.php con los datos actuales ($rol viene del controlador).
$modo = 'editar';
$rl   = $rol;
require __DIR__ . '/form.php';