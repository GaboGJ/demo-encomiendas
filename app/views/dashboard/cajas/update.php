<?php
// Formulario "Editar caja": reutiliza form.php con los datos actuales ($caja viene del controlador).
$modo = 'editar';
$caj  = $caja;
require __DIR__ . '/form.php';