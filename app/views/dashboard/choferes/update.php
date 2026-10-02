<?php
// Formulario "Editar chofer": reutiliza form.php con los datos actuales ($chofer viene del controlador).
$modo = 'editar';
$cho  = $chofer;
require __DIR__ . '/form.php';