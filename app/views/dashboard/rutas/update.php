<?php
// Formulario "Editar ruta": reutiliza form.php con los datos actuales ($ruta viene del controlador).
$modo = 'editar';
$ru   = $ruta;
require __DIR__ . '/form.php';