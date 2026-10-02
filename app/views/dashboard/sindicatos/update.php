<?php
// Formulario "Editar sindicato": reutiliza _form.php con los datos actuales ($sindicato viene del controlador).
$modo = 'editar';
$sin  = $sindicato;
require __DIR__ . '/form.php';